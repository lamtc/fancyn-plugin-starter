<?php
/**
 * Admin asset loading service.
 *
 * @package Fancyn_Plugin_Starter
 */

namespace Fancyn\PluginStarter\Admin;

use Closure;
use Fancyn\PluginStarter\Core\ServiceProvider;

/**
 * Loads Vite or compiled assets only on plugin-owned screens.
 */
final class Assets implements ServiceProvider {
	/**
	 * Plugin URL.
	 *
	 * @var string
	 */
	private string $plugin_url;

	/**
	 * Plugin path.
	 *
	 * @var string
	 */
	private string $plugin_path;

	/**
	 * Plugin version.
	 *
	 * @var string
	 */
	private string $version;

	/**
	 * Vite server URL.
	 *
	 * @var string
	 */
	private string $dev_server_url;

	/**
	 * Allowed WordPress hook suffixes.
	 *
	 * @var array<int, string>
	 */
	private array $allowed_hooks;

	/**
	 * Current screen resolver.
	 *
	 * @var Closure(): string
	 */
	private Closure $screen_id;

	/**
	 * Development server health resolver.
	 *
	 * @var Closure(): bool
	 */
	private Closure $dev_server_available;

	/**
	 * Manifest resolver.
	 *
	 * @var Closure(): array<string, mixed>
	 */
	private Closure $manifest;

	/**
	 * Whether a usable production build was missing.
	 *
	 * @var bool
	 */
	private bool $build_error = false;

	/**
	 * Creates the asset service.
	 *
	 * @param array<string, mixed> $config Asset configuration and injectable resolvers.
	 */
	public function __construct( array $config ) {
		$this->plugin_url           = trailingslashit( (string) $config['plugin_url'] );
		$this->plugin_path          = trailingslashit( (string) $config['plugin_path'] );
		$this->version              = (string) $config['version'];
		$this->dev_server_url       = untrailingslashit( (string) $config['dev_server_url'] );
		$this->allowed_hooks        = (array) $config['allowed_hooks'];
		$this->screen_id            = Closure::fromCallable( $config['screen_id'] ?? array( $this, 'resolve_screen_id' ) );
		$this->dev_server_available = Closure::fromCallable( $config['dev_server_available'] ?? array( $this, 'check_dev_server' ) );
		$this->manifest             = Closure::fromCallable( $config['manifest'] ?? array( $this, 'read_manifest' ) );
	}

	/**
	 * Registers WordPress hooks.
	 */
	public function register(): void {
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ) );
		add_action( 'admin_notices', array( $this, 'render_build_notice' ) );
	}

	/**
	 * Boots the asset service.
	 */
	public function boot(): void {}

	/**
	 * Enqueues assets for an allowed plugin screen.
	 *
	 * @param string $hook_suffix Current admin page hook suffix.
	 */
	public function enqueue( string $hook_suffix ): void {
		if ( ! in_array( $hook_suffix, $this->allowed_hooks, true ) ) {
			return;
		}

		$screen_id = ( $this->screen_id )();
		if ( '' === $screen_id || ! str_contains( $screen_id, 'fancyn-plugin-starter' ) ) {
			return;
		}
		if ( str_ends_with( $hook_suffix, '-settings' ) ) {
			wp_enqueue_media();
			wp_enqueue_editor();
		}

		if ( ( $this->dev_server_available )() ) {
			$this->enqueue_development_assets();
			return;
		}

		$this->enqueue_production_assets( $hook_suffix );
	}

	/**
	 * Reports whether asset resolution failed.
	 */
	public function has_build_error(): bool {
		return $this->build_error;
	}

	/**
	 * Renders a development-facing missing build notice.
	 */
	public function render_build_notice(): void {
		if ( ! $this->build_error || ! current_user_can( 'manage_options' ) ) {
			return;
		}

		echo '<div class="notice notice-error"><p>';
		echo esc_html__( 'Fancyn Plugin Starter assets are unavailable. Start Vite or run npm run build.', 'fancyn-plugin-starter' );
		echo '</p></div>';
	}

	/**
	 * Enqueues Vite source entries.
	 */
	private function enqueue_development_assets(): void {
		$client_url = $this->dev_server_url . '/@vite/client';
		$admin_url  = $this->dev_server_url . '/assets/src/js/admin.js';
		$style_url  = $this->dev_server_url . '/assets/src/css/admin.css';

		// phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion -- Vite development assets are never cached.
		wp_enqueue_script( 'fancyn-plugin-starter-vite-client', $client_url, array(), null, true );
		wp_script_add_data( 'fancyn-plugin-starter-vite-client', 'type', 'module' );
		// phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion -- Vite development assets are never cached.
		wp_enqueue_script( 'fancyn-plugin-starter-admin', $admin_url, array(), null, true );
		wp_script_add_data( 'fancyn-plugin-starter-admin', 'type', 'module' );
		// phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion -- Vite development assets are never cached.
		wp_enqueue_style( 'fancyn-plugin-starter-admin', $style_url, array(), null );
	}

	/**
	 * Enqueues compiled manifest entries.
	 *
	 * @param string $hook_suffix Current admin page hook suffix.
	 */
	private function enqueue_production_assets( string $hook_suffix ): void {
		$manifest = ( $this->manifest )();
		$script   = $manifest['assets/src/js/admin.js']['file'] ?? '';
		$style    = $manifest['assets/src/css/admin.css']['file'] ?? '';

		if ( ! is_string( $script ) || '' === $script || ! is_string( $style ) || '' === $style ) {
			$this->build_error = true;
			return;
		}

		$dependencies = $this->dependencies_for( $hook_suffix );
		$base_url     = $this->plugin_url . 'assets/dist/';

		wp_enqueue_script(
			'fancyn-plugin-starter-admin',
			$base_url . ltrim( $script, '/' ),
			$dependencies['scripts'],
			$this->version,
			true
		);
		wp_script_add_data( 'fancyn-plugin-starter-admin', 'type', 'module' );
		wp_enqueue_style(
			'fancyn-plugin-starter-admin',
			$base_url . ltrim( $style, '/' ),
			$dependencies['styles'],
			$this->version
		);
	}

	/**
	 * Returns page-specific WordPress asset dependencies.
	 *
	 * @param string $hook_suffix Current admin page hook suffix.
	 * @return array{scripts: array<int, string>, styles: array<int, string>}
	 */
	private function dependencies_for( string $hook_suffix ): array {
		if ( str_ends_with( $hook_suffix, '-settings' ) ) {
			return array(
				'scripts' => array( 'wp-color-picker', 'wp-editor', 'media-editor', 'wp-api-fetch' ),
				'styles'  => array( 'wp-color-picker' ),
			);
		}

		return array(
			'scripts' => array(),
			'styles'  => array(),
		);
	}

	/**
	 * Resolves the current WordPress screen ID.
	 */
	private function resolve_screen_id(): string {
		$screen = get_current_screen();
		return $screen ? (string) $screen->id : '';
	}

	/**
	 * Checks whether the configured Vite server is reachable.
	 */
	private function check_dev_server(): bool {
		$response = wp_remote_get(
			$this->dev_server_url . '/@vite/client',
			array(
				'timeout'  => 0.5,
				'blocking' => true,
			)
		);

		return ! is_wp_error( $response ) && 200 === wp_remote_retrieve_response_code( $response );
	}

	/**
	 * Reads the production Vite manifest.
	 *
	 * @return array<string, mixed>
	 */
	private function read_manifest(): array {
		$path = $this->plugin_path . 'assets/dist/.vite/manifest.json';
		if ( ! is_readable( $path ) ) {
			return array();
		}

		$decoded = wp_json_file_decode( $path, array( 'associative' => true ) );

		return is_array( $decoded ) ? $decoded : array();
	}
}
