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
