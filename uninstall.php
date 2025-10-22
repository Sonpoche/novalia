<?php
/**
 * Suppression complète du plugin lors de la désinstallation
 *
 * @package NovaliaDevis
 */

// Si la désinstallation n'est pas appelée depuis WordPress, on quitte
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

/**
 * Suppression des tables de la base de données
 */
function nd_delete_plugin_tables() {
    global $wpdb;
    
    $tables = [
        $wpdb->prefix . 'nd_items',
        $wpdb->prefix . 'nd_quotes',
        $wpdb->prefix . 'nd_quote_items',
    ];
    
    foreach ($tables as $table) {
        $wpdb->query("DROP TABLE IF EXISTS $table");
    }
}

/**
 * Suppression de toutes les options du plugin
 */
function nd_delete_plugin_options() {
    global $wpdb;
    
    // Liste des options du plugin
    $options = [
        'nd_version',
        'nd_db_version',
        'nd_activation_date',
        'nd_deactivation_date',
        'nd_pricing',
        'nd_company',
        'nd_email',
        'nd_pdf',
    ];
    
    foreach ($options as $option) {
        delete_option($option);
    }
    
    // Suppression de toutes les options commençant par 'nd_'
    $wpdb->query(
        "DELETE FROM {$wpdb->options} 
        WHERE option_name LIKE 'nd_%'"
    );
}

/**
 * Suppression des transients
 */
function nd_delete_plugin_transients() {
    global $wpdb;
    
    $wpdb->query(
        "DELETE FROM {$wpdb->options} 
        WHERE option_name LIKE '_transient_nd_%' 
        OR option_name LIKE '_transient_timeout_nd_%'"
    );
}

/**
 * Suppression des métadonnées utilisateurs
 */
function nd_delete_user_meta() {
    global $wpdb;
    
    $wpdb->query(
        "DELETE FROM {$wpdb->usermeta} 
        WHERE meta_key LIKE 'nd_%'"
    );
}

/**
 * Suppression des capacités ajoutées
 */
function nd_remove_capabilities() {
    $role = get_role('administrator');
    
    if ($role) {
        $role->remove_cap('manage_novalia_devis');
        $role->remove_cap('view_novalia_quotes');
        $role->remove_cap('edit_novalia_items');
        $role->remove_cap('edit_novalia_pricing');
    }
}

/**
 * Suppression des fichiers uploadés (optionnel)
 */
function nd_delete_uploaded_files() {
    $upload_dir = wp_upload_dir();
    $plugin_upload_dir = $upload_dir['basedir'] . '/novalia-devis';
    
    if (file_exists($plugin_upload_dir)) {
        nd_recursive_delete($plugin_upload_dir);
    }
}

/**
 * Fonction récursive pour supprimer un dossier
 */
function nd_recursive_delete($dir) {
    if (!file_exists($dir)) {
        return;
    }
    
    $files = array_diff(scandir($dir), ['.', '..']);
    
    foreach ($files as $file) {
        $path = $dir . '/' . $file;
        is_dir($path) ? nd_recursive_delete($path) : unlink($path);
    }
    
    rmdir($dir);
}

// Exécution de la désinstallation
nd_delete_plugin_tables();
nd_delete_plugin_options();
nd_delete_plugin_transients();
nd_delete_user_meta();
nd_remove_capabilities();
nd_delete_uploaded_files();

// Flush des règles de réécriture
flush_rewrite_rules();