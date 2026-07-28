<?php
/**
 * Admin menu tests.
 *
 * @package Fancyn_Plugin_Starter
 */

namespace Fancyn\PluginStarter\Tests\Admin;

use Brain\Monkey;
use Brain\Monkey\Functions;
use Fancyn\PluginStarter\Admin\Menu;
use Fancyn\PluginStarter\Admin\Settings;
use Fancyn\PluginStarter\Support\View;
use PHPUnit\Framework\TestCase;

/** Tests capability and development-only menu registration. */
final class MenuTest extends TestCase {
	/** Starts mocks. */
	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();
		Functions\when( '__' )->returnArg( 1 ); }
	/** Stops mocks. */
	protected function tearDown(): void {
		Monkey\tearDown();
		parent::tearDown(); }

	/** Registers dashboard and settings with manage_options. */
	public function test_register_menu_uses_capability_and_captures_hooks(): void {
		Functions\expect( 'add_menu_page' )->once()->with( \Mockery::any(), \Mockery::any(), 'manage_options', 'fancyn-plugin-starter', \Mockery::type( 'callable' ), \Mockery::any(), \Mockery::any() )->andReturn( 'toplevel_page_fancyn-plugin-starter' );
		Functions\expect( 'add_submenu_page' )->twice()->andReturn( 'fancyn-plugin-starter_page_fancyn-plugin-starter-settings' );
		$menu = $this->make_menu( false );
		$menu->register_menu();
		$this->assertContains( 'toplevel_page_fancyn-plugin-starter', $menu->hooks() );
	}

	/** Registers component showcase under Tools only in development mode. */
	public function test_showcase_registration_is_development_only(): void {
		Functions\expect( 'add_menu_page' )->twice()->andReturn( 'top' );
		Functions\expect( 'add_submenu_page' )->times( 4 )->andReturn( 'sub' );
		Functions\expect( 'add_management_page' )->once()->andReturn( 'tools_page_fancyn-plugin-starter-components' );
		$this->make_menu( false )->register_menu();
		$this->make_menu( true )->register_menu();
		$this->addToAssertionCount( 1 );
	}

	/**
	 * Creates a menu service.
	 *
	 * @param bool $development Development mode.
	 */
	private function make_menu( bool $development ): Menu {
		$view = new View( dirname( __DIR__, 3 ) . '/templates' );
		return new Menu( $view, new Settings( $view ), $development );
	}
}
