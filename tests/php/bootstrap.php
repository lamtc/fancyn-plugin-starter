<?php
/**
 * PHPUnit bootstrap file.
 *
 * @package Fancyn_Plugin_Starter
 */

$fancyn_plugin_starter_autoload = dirname( __DIR__, 2 ) . '/vendor/autoload.php';

if ( ! is_readable( $fancyn_plugin_starter_autoload ) ) {
	throw new RuntimeException( 'Composer dependencies are missing. Run composer install.' );
}

require_once $fancyn_plugin_starter_autoload;

if ( ! function_exists( 'Brain\\Monkey\\setUp' ) ) {
	throw new RuntimeException( 'Brain Monkey is unavailable. Run composer install.' );
}
