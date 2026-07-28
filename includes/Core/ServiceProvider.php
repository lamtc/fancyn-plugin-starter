<?php
/**
 * Service provider contract.
 *
 * @package Fancyn_Plugin_Starter
 */

namespace Fancyn\PluginStarter\Core;

/**
 * Defines the plugin service lifecycle.
 */
interface ServiceProvider {
	/**
	 * Registers hooks and dependencies.
	 */
	public function register(): void;

	/**
	 * Boots the registered service.
	 */
	public function boot(): void;
}
