<?php
/**
 * Validation helpers.
 *
 * @package Fancyn_Plugin_Starter
 */

namespace Fancyn\PluginStarter\Support;

/**
 * Applies field-specific WordPress sanitization.
 */
final class Validation {
	/**
	 * Sanitizes a value by its declared type.
	 *
	 * @param string $type     Declared field type.
	 * @param mixed  $value    Untrusted value.
	 * @param mixed  $fallback Previous or default value.
	 * @return mixed
	 */
	public static function by_type( string $type, mixed $value, mixed $fallback = null ): mixed {
		return match ( $type ) {
			'email'         => self::strict_string( sanitize_email( (string) $value ), $fallback ),
			'url'           => self::strict_string( esc_url_raw( (string) $value ), $fallback ),
			'integer'       => absint( $value ),
			'attachment_id' => absint( $value ),
			'boolean'       => in_array( $value, array( true, 1, '1', 'true', 'on', 'yes' ), true ),
			'color'         => self::strict_string( sanitize_hex_color( (string) $value ), $fallback ),
			'rich_text'     => wp_kses_post( (string) $value ),
			default         => sanitize_text_field( (string) $value ),
		};
	}

	/**
	 * Returns a non-empty sanitized value or its safe fallback.
	 *
	 * @param string $value    Sanitized value.
	 * @param mixed  $fallback Previous or default value.
	 */
	private static function strict_string( string $value, mixed $fallback ): string {
		return '' !== $value ? $value : (string) $fallback;
	}
}
