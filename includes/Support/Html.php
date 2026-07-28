<?php
/**
 * HTML helpers.
 *
 * @package Fancyn_Plugin_Starter
 */

namespace Fancyn\PluginStarter\Support;

/**
 * Serializes safe HTML attributes and class lists.
 */
final class Html {
	/**
	 * Native attributes accepted by the component system.
	 *
	 * @var array<int, string>
	 */
	private const ATTRIBUTES = array(
		'accept',
		'action',
		'alt',
		'autocomplete',
		'checked',
		'class',
		'cols',
		'disabled',
		'for',
		'form',
		'href',
		'id',
		'inputmode',
		'label',
		'max',
		'maxlength',
		'method',
		'min',
		'minlength',
		'multiple',
		'name',
		'pattern',
		'placeholder',
		'readonly',
		'rel',
		'required',
		'role',
		'rows',
		'selected',
		'size',
		'spellcheck',
		'src',
		'step',
		'tabindex',
		'target',
		'title',
		'type',
		'value',
	);

	/**
	 * Attributes represented by presence rather than a value.
	 *
	 * @var array<int, string>
	 */
	private const BOOLEAN_ATTRIBUTES = array(
		'checked',
		'disabled',
		'multiple',
		'readonly',
		'required',
		'selected',
	);

	/**
	 * Converts a map into a leading-space-prefixed HTML attribute string.
	 *
	 * @param array<string, mixed> $attributes Attribute values.
	 */
	public static function attributes( array $attributes ): string {
		$output = array();

		foreach ( $attributes as $name => $value ) {
			$name = strtolower( (string) $name );
			if ( ! self::is_safe_attribute_name( $name ) || null === $value || false === $value ) {
				continue;
			}

			if ( in_array( $name, self::BOOLEAN_ATTRIBUTES, true ) ) {
				if ( true === $value ) {
					$output[] = $name;
				}
				continue;
			}

			if ( 'class' === $name ) {
				$value = self::class_names( is_array( $value ) ? $value : array( $value ) );
			}

			if ( '' === (string) $value ) {
				continue;
			}

			$output[] = sprintf( '%s="%s"', $name, esc_attr( true === $value ? '1' : $value ) );
		}

		return $output ? ' ' . implode( ' ', $output ) : '';
	}

	/**
	 * Flattens conditional class values into a deterministic list.
	 *
	 * @param array<int|string, mixed> $classes Class values and conditional keys.
	 */
	public static function class_names( array $classes ): string {
		$resolved = array();
		self::collect_classes( $classes, $resolved );

		return implode( ' ', array_keys( $resolved ) );
	}

	/**
	 * Determines whether an attribute name is allowlisted.
	 *
	 * @param string $name Attribute name.
	 */
	private static function is_safe_attribute_name( string $name ): bool {
		if ( str_starts_with( $name, 'on' ) ) {
			return false;
		}

		return in_array( $name, self::ATTRIBUTES, true )
			|| 1 === preg_match( '/^(?:aria|data)-[a-z0-9_-]+$/', $name );
	}

	/**
	 * Recursively collects unique class names.
	 *
	 * @param array<int|string, mixed> $classes  Class values.
	 * @param array<string, bool>      $resolved Collected class map.
	 */
	private static function collect_classes( array $classes, array &$resolved ): void {
		foreach ( $classes as $key => $value ) {
			if ( is_string( $key ) ) {
				if ( $value ) {
					$resolved[ $key ] = true;
				}
				continue;
			}

			if ( is_array( $value ) ) {
				self::collect_classes( $value, $resolved );
				continue;
			}

			if ( is_string( $value ) && '' !== trim( $value ) ) {
				$class_names = preg_split( '/\s+/', trim( $value ) );
				$class_names = false === $class_names ? array() : $class_names;
				foreach ( $class_names as $class_name ) {
					$resolved[ $class_name ] = true;
				}
			}
		}
	}
}
