<?php
/**
 * Authenticated AJAX example.
 *
 * @package Fancyn_Plugin_Starter
 */

namespace Fancyn\PluginStarter\Http;

use Fancyn\PluginStarter\Core\ServiceProvider;

/**
 * Validates a sample setting through authenticated admin AJAX.
 */
final class Ajax implements ServiceProvider {
	/** Registers only the authenticated action. */
	public function register(): void {
		add_action( 'wp_ajax_fancyn_plugin_starter_validate_setting', array( $this, 'handle' ) );
	}

	/** Boots the service. */
	public function boot(): void {}

	/** Verifies nonce and capability before processing input. */
	public function handle(): void {
		if ( false === check_ajax_referer( 'fancyn_plugin_starter_validate_setting', 'nonce', false ) ) {
			wp_send_json_error(
				array( 'code' => 'fancyn_plugin_starter_invalid_nonce' ),
				403
			);
			return;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error(
				array( 'code' => 'fancyn_plugin_starter_forbidden' ),
				403
			);
			return;
		}

		$value = isset( $_POST['value'] ) ? sanitize_text_field( wp_unslash( $_POST['value'] ) ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotValidated -- Explicitly sanitized.
		if ( '' === $value ) {
			wp_send_json_error(
				array( 'code' => 'fancyn_plugin_starter_invalid_value' ),
				422
			);
			return;
		}

		wp_send_json_success(
			array(
				'code'  => 'fancyn_plugin_starter_valid',
				'value' => $value,
			)
		);
	}
}
