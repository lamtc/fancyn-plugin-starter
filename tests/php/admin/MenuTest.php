<?php
/**
 * Admin menu tests.
 *
 * @package Fancyn_Plugin_Starter
 */

namespace Fancyn\PluginStarter\Tests\Admin;

use Fancyn\PluginStarter\Admin\Menu;
use Fancyn\PluginStarter\Admin\Settings;
use Fancyn\PluginStarter\Support\View;
use Fancyn\PluginStarter\Tests\Support\WordPressTestCase;

/** Tests capability and development-only menu registration. */
final class MenuTest extends WordPressTestCase {
	/** Starts mocks. */
	protected function setUp(): void {
		parent::setUp();
		self::stubCallback( '__', static fn ( mixed $value ): mixed => $value ); }

	/** Registers dashboard and settings with manage_options. */
	public function test_register_menu_uses_capability_and_captures_hooks(): void {
		self::stub( 'add_menu_page', 'toplevel_page_fancyn-plugin-starter' );
		self::stub( 'add_submenu_page', 'fancyn-plugin-starter_page_fancyn-plugin-starter-settings' );
		$menu = $this->make_menu( false );
		$menu->register_menu();
		$this->assertContains( 'toplevel_page_fancyn-plugin-starter', $menu->hooks() );
		$this->assertSame( 'manage_options', self::calls( 'add_menu_page' )[0][2] );
		self::assertCalledTimes( 'add_submenu_page', 2 );
	}

	/** Registers component showcase under Tools only in development mode. */
	public function test_showcase_registration_is_development_only(): void {
		self::stub( 'add_menu_page', 'top' );
		self::stub( 'add_submenu_page', 'sub' );
		self::stub( 'add_management_page', 'tools_page_fancyn-plugin-starter-components' );
		$this->make_menu( false )->register_menu();
		$this->make_menu( true )->register_menu();
		self::assertCalledTimes( 'add_menu_page', 2 );
		self::assertCalledTimes( 'add_submenu_page', 4 );
		self::assertCalledTimes( 'add_management_page', 1 );
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
