<?php
/**
 * Removes data owned by Fancyn Plugin Starter.
 *
 * @package Fancyn_Plugin_Starter
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

delete_option( 'fancyn_plugin_starter_settings' );
