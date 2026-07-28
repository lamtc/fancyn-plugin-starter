<?php
/**
 * Plugin activation service.
 *
 * @package Fancyn_Plugin_Starter
 */

namespace Fancyn\PluginStarter\Core;

/**
 * Validates runtime requirements during activation.
 */
final class Activator {
	/**
	 * Activates the plugin when requirements are satisfied.
	 *
	 * @param bool $network_wide Whether activation was requested network-wide.
	 */
	public static function activate( bool $network_wide = false ): void {
		global $wp_version;

		unset( $network_wide );
		self::validate_environment( PHP_VERSION, (string) $wp_version );
	}

	/**
	 * Validates explicit environment versions.
	 *
	 * @param string $php_version       PHP version.
	 * @param string $wordpress_version WordPress version.
	 */
	public static function validate_environment( string $php_version, string $wordpress_version ): void {

		if ( version_compare( $php_version, '8.1', '<' ) ) {
			self::reject( __( 'Fancyn Plugin Starter requires PHP 8.1 or newer.', 'fancyn-plugin-starter' ) );
		}

		if ( version_compare( $wordpress_version, '6.5', '<' ) ) {
			self::reject( __( 'Fancyn Plugin Starter requires WordPress 6.5 or newer.', 'fancyn-plugin-starter' ) );
		}

		flush_rewrite_rules();
	}

	/**
	 * Deactivates the plugin and stops activation.
	 *
	 * @param string $message Failure message.
	 */
	private static function reject( string $message ): void {
		$plugin_file = defined( 'FANCYN_PLUGIN_STARTER_FILE' ) ? FANCYN_PLUGIN_STARTER_FILE : '';
		deactivate_plugins( plugin_basename( $plugin_file ) );
		wp_die( esc_html( $message ) );
	}
}
