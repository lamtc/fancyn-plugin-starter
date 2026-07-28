<?php
/**
 * Plugin application.
 *
 * @package Fancyn_Plugin_Starter
 */

namespace Fancyn\PluginStarter\Core;

/**
 * Coordinates service registration and booting.
 */
final class Plugin {
	/**
	 * Registered service providers.
	 *
	 * @var array<int, ServiceProvider>
	 */
	private array $providers;

	/**
	 * Whether the application has started.
	 *
	 * @var bool
	 */
	private bool $started = false;

	/**
	 * Creates the plugin application.
	 *
	 * @param array<int, ServiceProvider> $providers Plugin service providers.
	 */
	public function __construct( array $providers ) {
		$this->providers = $providers;
	}

	/**
	 * Starts the plugin once.
	 */
	public function run(): void {
		if ( $this->started ) {
			return;
		}

		$this->started = true;

		foreach ( $this->providers as $provider ) {
			$provider->register();
		}

		foreach ( $this->providers as $provider ) {
			$provider->boot();
		}
	}
}
