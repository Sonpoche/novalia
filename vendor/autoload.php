<?php
/**
 * Autoloader pour TCPDF
 * Fichier créé manuellement pour charger TCPDF sans Composer
 */

// Chemin vers TCPDF
$tcpdf_path = __DIR__ . '/tecnickcom/tcpdf/tcpdf.php';

if (file_exists($tcpdf_path)) {
    require_once $tcpdf_path;
} else {
    // Si TCPDF n'est pas dans tecnickcom/tcpdf, chercher directement
    $alt_path = __DIR__ . '/tcpdf/tcpdf.php';
    if (file_exists($alt_path)) {
        require_once $alt_path;
    }
}

// Retourner true pour indiquer que l'autoload a fonctionné
return true;