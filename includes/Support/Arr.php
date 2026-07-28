<?php
/**
 * Array helpers.
 *
 * @package Fancyn_Plugin_Starter
 */

namespace Fancyn\PluginStarter\Support;

/**
 * Provides small, side-effect-free array operations.
 */
final class Arr {
	/**
	 * Reads a nested array value using dot notation.
	 *
	 * @param array<string, mixed> $items   Source array.
	 * @param string               $key     Dot-delimited key.
	 * @param mixed                $fallback Default returned when the path is absent.
	 * @return mixed
	 */
	public static function get( array $items, string $key, mixed $fallback = null ): mixed {
		if ( '' === $key ) {
			return $items;
		}

		$value = $items;
		foreach ( explode( '.', $key ) as $segment ) {
			if ( ! is_array( $value ) || ! array_key_exists( $segment, $value ) ) {
				return $fallback;
			}

			$value = $value[ $segment ];
		}

		return $value;
	}
}
