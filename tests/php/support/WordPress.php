<?php
/**
 * Minimal global WordPress functions for isolated PHPUnit tests.
 *
 * @package Fancyn_Plugin_Starter
 */

use Fancyn\PluginStarter\Tests\Support\JsonResponseException;
use Fancyn\PluginStarter\Tests\Support\WordPressFunctionRegistry as Registry;
use Fancyn\PluginStarter\Tests\Support\WpDieException;

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound -- These test-only functions intentionally mirror WordPress globals.
// phpcs:disable Squiz.Commenting.FunctionComment.MissingParamTag, Squiz.Commenting.FunctionCommentThrowTag.Missing -- Signatures intentionally mirror WordPress globals.

if ( ! function_exists( 'current_user_can' ) ) {
	/** Test double for current_user_can(). */
	function current_user_can( string $capability, mixed ...$arguments ): bool {
		return (bool) Registry::invoke( __FUNCTION__, array_merge( array( $capability ), $arguments ), false );
	}
}

if ( ! function_exists( 'sanitize_text_field' ) ) {
	/** Test double for sanitize_text_field(). */
	function sanitize_text_field( mixed $value ): string {
		return (string) Registry::invoke( __FUNCTION__, array( $value ), static fn( mixed $input ): string => trim( wp_strip_all_tags( (string) $input ) ) );
	}
}

if ( ! function_exists( 'wp_strip_all_tags' ) ) {
	/** Test double for wp_strip_all_tags(). */
	function wp_strip_all_tags( string $value ): string {
		// phpcs:ignore WordPress.WP.AlternativeFunctions.strip_tags_strip_tags -- This is the test implementation of wp_strip_all_tags().
		return (string) Registry::invoke( __FUNCTION__, array( $value ), static fn( string $input ): string => strip_tags( $input ) );
	}
}

if ( ! function_exists( 'wp_send_json_success' ) ) {
	/** Test double for wp_send_json_success(). */
	function wp_send_json_success( mixed $data = null, ?int $status_code = null ): never {
		Registry::invoke( __FUNCTION__, array( $data, $status_code ) );
		// phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- Payload is captured, not rendered.
		throw new JsonResponseException( true, $data, $status_code ?? 200 );
	}
}

if ( ! function_exists( 'wp_send_json_error' ) ) {
	/** Test double for wp_send_json_error(). */
	function wp_send_json_error( mixed $data = null, ?int $status_code = null ): never {
		Registry::invoke( __FUNCTION__, array( $data, $status_code ) );
		// phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- Payload is captured, not rendered.
		throw new JsonResponseException( false, $data, $status_code ?? 200 );
	}
}

if ( ! function_exists( 'wp_die' ) ) {
	/** Test double for wp_die(). */
	function wp_die( mixed $message = '', mixed $title = '', array $arguments = array() ): never {
		Registry::invoke( __FUNCTION__, array( $message, $title, $arguments ) );
		// phpcs:ignore WordPress.Security.EscapeOutput.ExceptionNotEscaped -- Values are captured, not rendered.
		throw new WpDieException( $message, $title, $arguments );
	}
}

/** Invokes the shim registry for generic WordPress functions. */
function fancyn_plugin_starter_test_invoke( string $function_name, array $arguments = array(), mixed $fallback = null ): mixed {
	return Registry::invoke( $function_name, $arguments, $fallback );
}

if ( ! function_exists( '__' ) ) {
	/** Test translation function. */
	function __( string $text, string $domain = 'default' ): string {
		return (string) Registry::invoke( __FUNCTION__, array( $text, $domain ), $text );
	}
}

if ( ! function_exists( 'esc_html__' ) ) {
	/** Test escaped translation function. */
	function esc_html__( string $text, string $domain = 'default' ): string {
		return (string) Registry::invoke( __FUNCTION__, array( $text, $domain ), static fn( string $value ): string => esc_html( $value ) );
	}
}

if ( ! function_exists( 'esc_html' ) ) {
	/** Test HTML escaping function. */
	function esc_html( mixed $value ): string {
		return (string) Registry::invoke( __FUNCTION__, array( $value ), static fn( mixed $input ): string => htmlspecialchars( (string) $input, ENT_QUOTES, 'UTF-8' ) );
	}
}

if ( ! function_exists( 'esc_attr' ) ) {
	/** Test attribute escaping function. */
	function esc_attr( mixed $value ): string {
		return (string) Registry::invoke( __FUNCTION__, array( $value ), static fn( mixed $input ): string => htmlspecialchars( (string) $input, ENT_QUOTES, 'UTF-8' ) );
	}
}

if ( ! function_exists( 'esc_url' ) ) {
	/** Test URL escaping function. */
	function esc_url( mixed $value ): string {
		return (string) Registry::invoke( __FUNCTION__, array( $value ), (string) $value );
	}
}

if ( ! function_exists( 'esc_url_raw' ) ) {
	/** Test raw URL escaping function. */
	function esc_url_raw( mixed $value ): string {
		return (string) Registry::invoke( __FUNCTION__, array( $value ), filter_var( $value, FILTER_VALIDATE_URL ) ? (string) $value : '' );
	}
}

if ( ! function_exists( 'sanitize_email' ) ) {
	/** Test email sanitizer. */
	function sanitize_email( mixed $value ): string {
		return (string) Registry::invoke( __FUNCTION__, array( $value ), filter_var( $value, FILTER_VALIDATE_EMAIL ) ? (string) $value : '' );
	}
}

if ( ! function_exists( 'sanitize_hex_color' ) ) {
	/** Test color sanitizer. */
	function sanitize_hex_color( mixed $value ): string {
		return (string) Registry::invoke( __FUNCTION__, array( $value ), preg_match( '/^#[0-9a-fA-F]{6}$/', (string) $value ) ? (string) $value : '' );
	}
}

if ( ! function_exists( 'sanitize_key' ) ) {
	/** Test key sanitizer. */
	function sanitize_key( string $value ): string {
		return (string) Registry::invoke( __FUNCTION__, array( $value ), strtolower( preg_replace( '/[^a-zA-Z0-9_-]/', '-', $value ) ?? '' ) );
	}
}

if ( ! function_exists( 'absint' ) ) {
	/** Test absolute integer conversion. */
	function absint( mixed $value ): int {
		return (int) Registry::invoke( __FUNCTION__, array( $value ), abs( (int) $value ) );
	}
}

if ( ! function_exists( 'wp_kses' ) ) {
	/** Test KSES passthrough. */
	function wp_kses( string $value, array $allowed_html = array(), array $allowed_protocols = array() ): string {
		return (string) Registry::invoke( __FUNCTION__, array( $value, $allowed_html, $allowed_protocols ), $value );
	}
}

if ( ! function_exists( 'wp_kses_post' ) ) {
	/** Test post KSES passthrough. */
	function wp_kses_post( string $value ): string {
		return (string) Registry::invoke( __FUNCTION__, array( $value ), $value );
	}
}

if ( ! function_exists( 'wp_unslash' ) ) {
	/** Test unslash passthrough. */
	function wp_unslash( mixed $value ): mixed {
		return Registry::invoke( __FUNCTION__, array( $value ), $value );
	}
}

if ( ! function_exists( 'trailingslashit' ) ) {
	/** Test trailing slash helper. */
	function trailingslashit( string $value ): string {
		return (string) Registry::invoke( __FUNCTION__, array( $value ), rtrim( $value, '/\\' ) . '/' );
	}
}

if ( ! function_exists( 'untrailingslashit' ) ) {
	/** Test trailing slash removal helper. */
	function untrailingslashit( string $value ): string {
		return (string) Registry::invoke( __FUNCTION__, array( $value ), rtrim( $value, '/\\' ) );
	}
}

if ( ! function_exists( 'add_action' ) ) {
	/** Test action registration. */
	function add_action( mixed ...$arguments ): mixed {
		return Registry::invoke( __FUNCTION__, $arguments ); }
}
if ( ! function_exists( 'add_filter' ) ) {
	/** Test filter registration. */
	function add_filter( mixed ...$arguments ): mixed {
		return Registry::invoke( __FUNCTION__, $arguments ); }
}
if ( ! function_exists( 'register_activation_hook' ) ) {
	/** Test activation registration. */
	function register_activation_hook( mixed ...$arguments ): mixed {
		return Registry::invoke( __FUNCTION__, $arguments ); }
}
if ( ! function_exists( 'register_deactivation_hook' ) ) {
	/** Test deactivation registration. */
	function register_deactivation_hook( mixed ...$arguments ): mixed {
		return Registry::invoke( __FUNCTION__, $arguments ); }
}
if ( ! function_exists( 'register_setting' ) ) {
	/** Test setting registration. */
	function register_setting( mixed ...$arguments ): mixed {
		return Registry::invoke( __FUNCTION__, $arguments ); }
}
if ( ! function_exists( 'add_settings_section' ) ) {
	/** Test settings section registration. */
	function add_settings_section( mixed ...$arguments ): mixed {
		return Registry::invoke( __FUNCTION__, $arguments ); }
}
if ( ! function_exists( 'wp_enqueue_script' ) ) {
	/** Test script enqueue. */
	function wp_enqueue_script( mixed ...$arguments ): mixed {
		return Registry::invoke( __FUNCTION__, $arguments ); }
}
if ( ! function_exists( 'wp_enqueue_style' ) ) {
	/** Test style enqueue. */
	function wp_enqueue_style( mixed ...$arguments ): mixed {
		return Registry::invoke( __FUNCTION__, $arguments ); }
}
if ( ! function_exists( 'wp_script_add_data' ) ) {
	/** Test script metadata. */
	function wp_script_add_data( mixed ...$arguments ): mixed {
		return Registry::invoke( __FUNCTION__, $arguments ); }
}
if ( ! function_exists( 'wp_enqueue_media' ) ) {
	/** Test media enqueue. */
	function wp_enqueue_media( mixed ...$arguments ): mixed {
		return Registry::invoke( __FUNCTION__, $arguments ); }
}
if ( ! function_exists( 'wp_enqueue_editor' ) ) {
	/** Test editor enqueue. */
	function wp_enqueue_editor( mixed ...$arguments ): mixed {
		return Registry::invoke( __FUNCTION__, $arguments ); }
}
if ( ! function_exists( 'register_rest_route' ) ) {
	/** Test REST route registration. */
	function register_rest_route( mixed ...$arguments ): mixed {
		return Registry::invoke( __FUNCTION__, $arguments ); }
}
if ( ! function_exists( 'deactivate_plugins' ) ) {
	/** Test plugin deactivation. */
	function deactivate_plugins( mixed ...$arguments ): mixed {
		return Registry::invoke( __FUNCTION__, $arguments ); }
}
if ( ! function_exists( 'flush_rewrite_rules' ) ) {
	/** Test rewrite flushing. */
	function flush_rewrite_rules( mixed ...$arguments ): mixed {
		return Registry::invoke( __FUNCTION__, $arguments ); }
}
if ( ! function_exists( 'check_ajax_referer' ) ) {
	/** Test AJAX nonce verification. */
	function check_ajax_referer( mixed ...$arguments ): mixed {
		return Registry::invoke( __FUNCTION__, $arguments, true ); }
}

if ( ! function_exists( 'get_option' ) ) {
	/** Test option getter. */
	function get_option( string $option, mixed $default_value = false ): mixed {
		return Registry::invoke( __FUNCTION__, array( $option, $default_value ), $default_value );
	}
}

if ( ! function_exists( 'rest_ensure_response' ) ) {
	/** Test REST response passthrough. */
	function rest_ensure_response( mixed $response ): mixed {
		return Registry::invoke( __FUNCTION__, array( $response ), $response );
	}
}

if ( ! function_exists( 'plugin_basename' ) ) {
	/** Test plugin basename helper. */
	function plugin_basename( string $file ): string {
		return (string) Registry::invoke( __FUNCTION__, array( $file ), $file );
	}
}

if ( ! function_exists( 'add_menu_page' ) ) {
	/** Test menu registration. */
	function add_menu_page( mixed ...$arguments ): mixed {
		return Registry::invoke( __FUNCTION__, $arguments, 'toplevel_page_test' );
	}
}

if ( ! function_exists( 'add_submenu_page' ) ) {
	/** Test submenu registration. */
	function add_submenu_page( mixed ...$arguments ): mixed {
		return Registry::invoke( __FUNCTION__, $arguments, 'test_page_submenu' );
	}
}

if ( ! function_exists( 'add_management_page' ) ) {
	/** Test Tools menu registration. */
	function add_management_page( mixed ...$arguments ): mixed {
		return Registry::invoke( __FUNCTION__, $arguments, 'tools_page_test' );
	}
}
