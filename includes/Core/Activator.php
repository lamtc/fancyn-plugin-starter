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
	 * @param string|null $php_version       PHP version override for tests.
	 * @param string|null $wordpress_version WordPress version override for tests.
	 */
	public static function activate( ?string $php_version = null, ?string $wordpress_version = null ): void {
		global $wp_version;

		$php_version       = $php_version ?? PHP_VERSION;
		$wordpress_version = $wordpress_version ?? (string) $wp_version;

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
