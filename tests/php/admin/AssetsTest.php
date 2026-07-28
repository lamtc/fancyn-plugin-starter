<?php
/**
 * Admin asset service tests.
 *
 * @package Fancyn_Plugin_Starter
 */

namespace Fancyn\PluginStarter\Tests\Admin;

use Fancyn\PluginStarter\Admin\Assets;
use Fancyn\PluginStarter\Tests\Support\WordPressTestCase;

/**
 * Tests scoped development and production asset loading.
 */
final class AssetsTest extends WordPressTestCase {
	/** Starts WordPress function mocks. */
	protected function setUp(): void {
		parent::setUp();
		self::stubCallback( 'esc_html__', static fn ( mixed $value ): mixed => $value );
		self::stubCallback( 'esc_html', static fn ( mixed $value ): mixed => $value );
		self::stubCallback(
			'trailingslashit',
			static fn ( string $value ): string => rtrim( $value, '/\\' ) . '/'
		);
		self::stubCallback(
			'untrailingslashit',
			static fn ( string $value ): string => rtrim( $value, '/\\' )
		);
	}

	/**
	 * Does not enqueue outside registered hooks.
	 */
	public function test_skips_unrelated_admin_hooks(): void {
		$this->make_assets()->enqueue( 'index.php' );
		self::assertCalledTimes( 'wp_enqueue_script', 0 );
		self::assertCalledTimes( 'wp_enqueue_style', 0 );
	}

	/**
	 * Does not enqueue when current screen is outside the plugin.
	 */
	public function test_skips_unrelated_current_screen(): void {
		$assets = $this->make_assets(
			array(
				'screen_id' => static fn (): string => 'dashboard',
			)
		);

		$assets->enqueue( 'toplevel_page_fancyn-plugin-starter' );
		self::assertCalledTimes( 'wp_enqueue_script', 0 );
	}

	/**
	 * Uses Vite source entries when the development server responds.
	 */
	public function test_enqueues_vite_development_entries(): void {
		$assets = $this->make_assets(
			array(
				'dev_server_available' => static fn (): bool => true,
			)
		);

		$assets->enqueue( 'toplevel_page_fancyn-plugin-starter' );
		self::assertCalledTimes( 'wp_enqueue_script', 2 );
		self::assertCalledTimes( 'wp_script_add_data', 2 );
		$this->assertSame( array( 'fancyn-plugin-starter-admin', 'http://127.0.0.1:5173/assets/src/css/admin.css', array(), null ), self::calls( 'wp_enqueue_style' )[0] );
		foreach ( self::calls( 'wp_script_add_data' ) as $call ) {
			$this->assertSame( array( 'type', 'module' ), array_slice( $call, 1 ) );
		}
	}

	/**
	 * Renders Vite entries as modules so their symbols cannot leak globally.
	 */
	public function test_marks_plugin_scripts_as_modules(): void {
		$assets = $this->make_assets();

		// phpcs:disable WordPress.WP.EnqueuedResources.NonEnqueuedScript -- Raw tags are fixtures for the script_loader_tag filter.
		$this->assertSame(
			'<script type="module" src="admin.js"></script>',
			$assets->mark_script_as_module(
				'<script src="admin.js"></script>',
				'fancyn-plugin-starter-admin',
				'admin.js'
			)
		);
		$this->assertSame(
			'<script src="underscore.js"></script>',
			$assets->mark_script_as_module(
				'<script src="underscore.js"></script>',
				'underscore',
				'underscore.js'
			)
		);
		// phpcs:enable WordPress.WP.EnqueuedResources.NonEnqueuedScript
	}

	/**
	 * Resolves hashed files and page-specific WordPress dependencies.
	 */
	public function test_enqueues_manifest_assets_and_settings_dependencies(): void {
		$assets = $this->make_assets(
			array(
				'manifest' => static fn (): array => array(
					'assets/src/js/admin.js'   => array( 'file' => 'assets/admin-abc.js' ),
					'assets/src/css/admin.css' => array( 'file' => 'assets/styles-def.css' ),
				),
			)
		);

		$assets->enqueue( 'fancyn-plugin-starter_page_fancyn-plugin-starter-settings' );
		$this->assertSame(
			array( 'fancyn-plugin-starter-admin', 'https://example.test/plugin/assets/dist/assets/admin-abc.js', array( 'wp-color-picker', 'wp-editor', 'media-editor', 'wp-api-fetch' ), '1.0.0', true ),
			self::calls( 'wp_enqueue_script' )[0]
		);
		$this->assertSame(
			array( 'fancyn-plugin-starter-admin', 'https://example.test/plugin/assets/dist/assets/styles-def.css', array( 'wp-color-picker' ), '1.0.0' ),
			self::calls( 'wp_enqueue_style' )[0]
		);
		self::assertCalledTimes( 'wp_script_add_data', 1 );
		self::assertCalledTimes( 'wp_enqueue_media', 1 );
		self::assertCalledTimes( 'wp_enqueue_editor', 1 );
	}

	/**
	 * Accepts the settings page when WordPress derives a different parent prefix.
	 */
	public function test_accepts_settings_hook_with_wordpress_parent_prefix(): void {
		$assets = $this->make_assets(
			array(
				'manifest' => static fn (): array => array(
					'assets/src/js/admin.js'   => array( 'file' => 'assets/admin-abc.js' ),
					'assets/src/css/admin.css' => array( 'file' => 'assets/styles-def.css' ),
				),
			)
		);

		$assets->enqueue( 'plugin-starter_page_fancyn-plugin-starter-settings' );
		self::assertCalledTimes( 'wp_enqueue_script', 1 );
		self::assertCalledTimes( 'wp_script_add_data', 1 );
		self::assertCalledTimes( 'wp_enqueue_media', 1 );
		self::assertCalledTimes( 'wp_enqueue_editor', 1 );
		self::assertCalledTimes( 'wp_enqueue_style', 1 );
	}

	/**
	 * Exposes a notice when no usable production build exists.
	 */
	public function test_marks_missing_manifest_for_admin_notice(): void {
		$assets = $this->make_assets();

		$assets->enqueue( 'toplevel_page_fancyn-plugin-starter' );

		$this->assertTrue( $assets->has_build_error() );
	}

	/**
	 * Creates an asset service with deterministic test defaults.
	 *
	 * @param array<string, mixed> $overrides Configuration overrides.
	 */
	private function make_assets( array $overrides = array() ): Assets {
		return new Assets(
			array_merge(
				array(
					'plugin_url'           => 'https://example.test/plugin/',
					'plugin_path'          => 'C:/plugin/',
					'version'              => '1.0.0',
					'dev_server_url'       => 'http://127.0.0.1:5173',
					'allowed_hooks'        => array(
						'toplevel_page_fancyn-plugin-starter',
						'fancyn-plugin-starter_page_fancyn-plugin-starter-settings',
					),
					'screen_id'            => static fn (): string => 'fancyn-plugin-starter',
					'dev_server_available' => static fn (): bool => false,
					'manifest'             => static fn (): array => array(),
				),
				$overrides
			)
		);
	}
}
