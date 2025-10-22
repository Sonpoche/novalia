<?php
/**
 * Gestion de la désactivation du plugin
 *
 * @package NovaliaDevis
 * @subpackage Includes
 */

if (!defined('ABSPATH')) {
    exit;
}

class ND_Deactivator {
    
    /**
     * Actions lors de la désactivation du plugin
     */
    public static function deactivate() {
        
        // Flush des règles de réécriture
        flush_rewrite_rules();
        
        // Nettoyage des tâches planifiées (cron)
        self::clear_scheduled_hooks();
        
        // Suppression des transients temporaires
        self::delete_transients();
        
        // Log de désactivation
        self::log_deactivation();
    }
    
    /**
     * Suppression des tâches cron planifiées
     */
    private static function clear_scheduled_hooks() {
        
        // Liste des hooks cron du plugin
        $hooks = [
            'nd_daily_cleanup',
            'nd_weekly_report',
        ];
        
        foreach ($hooks as $hook) {
            $timestamp = wp_next_scheduled($hook);
            if ($timestamp) {
                wp_unschedule_event($timestamp, $hook);
            }
        }
    }
    
    /**
     * Suppression des transients temporaires
     */
    private static function delete_transients() {
        global $wpdb;
        
        // Suppression de tous les transients du plugin
        $wpdb->query(
            "DELETE FROM {$wpdb->options} 
            WHERE option_name LIKE '_transient_nd_%' 
            OR option_name LIKE '_transient_timeout_nd_%'"
        );
    }
    
    /**
     * Log de la désactivation
     */
    private static function log_deactivation() {
        update_option('nd_deactivation_date', current_time('mysql'));
    }
    
    /**
     * Note : Les données ne sont PAS supprimées lors de la désactivation
     * Pour supprimer complètement les données, utiliser uninstall.php
     */
}