<?php

if ( ! class_exists( 'WP_CLI' ) ) {
	return;
}

$wpcli_camaleaun_autoloader = __DIR__ . '/vendor/autoload.php';
if ( file_exists( $wpcli_camaleaun_autoloader ) ) {
	require_once $wpcli_camaleaun_autoloader;
}

WP_CLI::add_command( 'scaffold camaleaun-plugin', 'Camaleaun_Scaffold_Plugin_Command' );
