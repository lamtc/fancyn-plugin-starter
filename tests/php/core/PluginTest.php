<?php
/**
 * Plugin application tests.
 *
 * @package Fancyn_Plugin_Starter
 */

namespace Fancyn\PluginStarter\Tests\Core;

use Fancyn\PluginStarter\Core\Plugin;
use Fancyn\PluginStarter\Core\ServiceProvider;
use PHPUnit\Framework\TestCase;

/**
 * Tests the application service lifecycle.
 */
final class PluginTest extends TestCase {
	/**
	 * Verifies that every service is registered before any service boots.
	 */
	public function test_registers_all_services_before_booting_them(): void {
		$events = array();
		$first  = new Recording_Service_Provider( 'first', $events );
		$second = new Recording_Service_Provider( 'second', $events );

		$plugin = new Plugin( array( $first, $second ) );
		$plugin->run();

		$this->assertSame(
			array( 'register:first', 'register:second', 'boot:first', 'boot:second' ),
			$events
		);
	}

	/**
	 * Verifies that repeated startup does not register services twice.
	 */
	public function test_run_is_idempotent(): void {
		$events   = array();
		$provider = new Recording_Service_Provider( 'only', $events );
		$plugin   = new Plugin( array( $provider ) );

		$plugin->run();
		$plugin->run();

		$this->assertSame( array( 'register:only', 'boot:only' ), $events );
	}
}

// phpcs:disable Generic.Files.OneObjectStructurePerFile.MultipleFound -- Focused local test double.
/**
 * Records lifecycle calls for assertions.
 */
final class Recording_Service_Provider implements ServiceProvider {
	/**
	 * Provider name.
	 *
	 * @var string
	 */
	private string $name;

	/**
	 * Shared lifecycle events.
	 *
	 * @var array<int, string>
	 */
	private array $events;

	/**
	 * Creates the recording provider.
	 *
	 * @param string             $name Provider name.
	 * @param array<int, string> $events Shared event list.
	 */
	public function __construct( string $name, array &$events ) {
		$this->name   = $name;
		$this->events = &$events;
	}

	/** Registers the provider. */
	public function register(): void {
		$this->events[] = 'register:' . $this->name;
	}

	/** Boots the provider. */
	public function boot(): void {
		$this->events[] = 'boot:' . $this->name;
	}
}
// phpcs:enable Generic.Files.OneObjectStructurePerFile.MultipleFound
