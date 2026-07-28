<?php
/**
 * Plugin deactivation service.
 *
 * @package Fancyn_Plugin_Starter
 */

namespace Fancyn\PluginStarter\Core;

/**
 * Performs deactivation cleanup.
 */
final class Deactivator {
	/**
	 * Flushes rewrite rules after deactivation.
	 */
	public static function deactivate(): void {
		flush_rewrite_rules();
	}
}
