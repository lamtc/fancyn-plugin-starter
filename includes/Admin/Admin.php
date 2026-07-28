<?php
/**
 * Admin aggregate provider.
 *
 * @package Fancyn_Plugin_Starter
 */

namespace Fancyn\PluginStarter\Admin;

use Fancyn\PluginStarter\Core\ServiceProvider;

/**
 * Coordinates focused admin service providers.
 */
final class Admin implements ServiceProvider {
	/**
	 * Admin providers.
	 *
	 * @var array<int, ServiceProvider>
	 */
	private array $providers;

	/**
	 * Creates the aggregate provider.
	 *
	 * @param array<int, ServiceProvider> $providers Admin providers.
	 */
	public function __construct( array $providers ) {
		$this->providers = $providers;
	}

	/** Registers every admin provider. */
	public function register(): void {
		foreach ( $this->providers as $provider ) {
			$provider->register();
		}
	}

	/** Boots every admin provider. */
	public function boot(): void {
		foreach ( $this->providers as $provider ) {
			$provider->boot();
		}
	}
}
