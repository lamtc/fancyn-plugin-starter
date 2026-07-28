<?php
/**
 * Admin menu service.
 *
 * @package Fancyn_Plugin_Starter
 */

namespace Fancyn\PluginStarter\Admin;

use Fancyn\PluginStarter\Core\ServiceProvider;
use Fancyn\PluginStarter\Support\View;

/**
 * Registers plugin-owned admin screens.
 */
final class Menu implements ServiceProvider {
	/**
	 * View renderer.
	 *
	 * @var View
	 */
	private View $view;

	/**
	 * Settings page controller.
	 *
	 * @var Settings
	 */
	private Settings $settings;

	/**
	 * Whether development-only pages are enabled.
	 *
	 * @var bool
	 */
	private bool $development;

	/**
	 * Registered hook suffixes.
	 *
	 * @var array<int, string>
	 */
	private array $hooks = array();

	/**
	 * Creates the menu service.
	 *
	 * @param View     $view        View renderer.
	 * @param Settings $settings    Settings controller.
	 * @param bool     $development Development mode.
	 */
	public function __construct( View $view, Settings $settings, bool $development ) {
		$this->view        = $view;
		$this->settings    = $settings;
		$this->development = $development;
	}

	/** Registers the admin-menu hook. */
	public function register(): void {
		add_action( 'admin_menu', array( $this, 'register_menu' ) );
	}

	/** Boots the service. */
	public function boot(): void {}

	/** Registers dashboard, settings, and optional showcase pages. */
	public function register_menu(): void {
		$this->hooks[] = (string) add_menu_page(
			__( 'Fancyn Plugin Starter', 'fancyn-plugin-starter' ),
			__( 'Plugin Starter', 'fancyn-plugin-starter' ),
			'manage_options',
			'fancyn-plugin-starter',
			array( $this, 'render_dashboard' ),
			'dashicons-admin-generic',
			58
		);

		$this->hooks[] = (string) add_submenu_page(
			'fancyn-plugin-starter',
			__( 'Dashboard', 'fancyn-plugin-starter' ),
			__( 'Dashboard', 'fancyn-plugin-starter' ),
			'manage_options',
			'fancyn-plugin-starter',
			array( $this, 'render_dashboard' )
		);

		$this->hooks[] = (string) add_submenu_page(
			'fancyn-plugin-starter',
			__( 'Settings', 'fancyn-plugin-starter' ),
			__( 'Settings', 'fancyn-plugin-starter' ),
			'manage_options',
			'fancyn-plugin-starter-settings',
			array( $this->settings, 'render_page' )
		);

		if ( $this->development ) {
			$this->hooks[] = (string) add_management_page(
				__( 'Plugin UI Kit', 'fancyn-plugin-starter' ),
				__( 'Plugin UI Kit', 'fancyn-plugin-starter' ),
				'manage_options',
				'fancyn-plugin-starter-components',
				array( $this, 'render_components' )
			);
		}
	}

	/**
	 * Returns registered hook suffixes.
	 *
	 * @return array<int, string> Registered hook suffixes.
	 */
	public function hooks(): array {
		return array_values( array_filter( $this->hooks ) );
	}

	/** Renders the dashboard page. */
	public function render_dashboard(): void {
		$this->view->render( 'admin/dashboard' );
	}

	/** Renders the development component showcase. */
	public function render_components(): void {
		$this->view->render( 'admin/components-demo' );
	}
}
