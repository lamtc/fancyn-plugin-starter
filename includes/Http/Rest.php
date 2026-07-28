<?php
/**
 * REST API example.
 *
 * @package Fancyn_Plugin_Starter
 */

namespace Fancyn\PluginStarter\Http;

use Fancyn\PluginStarter\Core\ServiceProvider;

/**
 * Registers a capability-protected sample route.
 */
final class Rest implements ServiceProvider {
	/** Registers REST initialization. */
	public function register(): void {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	/** Boots the service. */
	public function boot(): void {}

	/** Registers the route, schema, and permission callback. */
	public function register_routes(): void {
		register_rest_route(
			'fancyn-plugin-starter/v1',
			'/validate-setting',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'validate_setting' ),
				'permission_callback' => array( $this, 'can_manage' ),
				'args'                => array(
					'value' => array(
						'type'              => 'string',
						'required'          => true,
						'sanitize_callback' => 'sanitize_text_field',
						'validate_callback' => static fn ( mixed $value ): bool => is_string( $value ) && '' !== trim( $value ),
					),
				),
			)
		);
	}

	/** Checks route capability. */
	public function can_manage(): bool {
		return current_user_can( 'manage_options' );
	}

	/**
	 * Returns a structured validation response.
	 *
	 * @param object $request REST request exposing get_param().
	 * @return mixed WordPress REST response.
	 */
	public function validate_setting( object $request ): mixed {
		$value = sanitize_text_field( (string) $request->get_param( 'value' ) );

		return rest_ensure_response(
			array(
				'code'  => 'fancyn_plugin_starter_valid',
				'value' => $value,
			)
		);
	}
}
