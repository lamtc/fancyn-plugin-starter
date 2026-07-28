<?php
/**
 * Validation message component.
 *
 * @package Fancyn_Plugin_Starter
 */

namespace Fancyn\PluginStarter\UI;

use Fancyn\PluginStarter\Support\Html;

/**
 * Renders text-first semantic feedback.
 */
final class ValidationMessage {
	/**
	 * Fixed classes for supported variants.
	 *
	 * @var array<string, string>
	 */
	private const VARIANTS = array(
		'error'   => 'fps-text-danger',
		'warning' => 'fps-text-warning',
		'success' => 'fps-text-success',
		'info'    => 'fps-text-info',
	);

	/**
	 * Returns feedback markup.
	 *
	 * @param array<string, mixed> $args Message arguments.
	 */
	public static function make( array $args ): string {
		$args = array_merge(
			array(
				'id'           => '',
				'variant'      => 'error',
				'message'      => '',
				'icon'         => '',
				'submit_error' => false,
				'attributes'   => array(),
			),
			$args
		);

		$variant             = isset( self::VARIANTS[ $args['variant'] ] ) ? (string) $args['variant'] : 'error';
		$attributes          = is_array( $args['attributes'] ) ? $args['attributes'] : array();
		$attributes['id']    = (string) $args['id'];
		$attributes['class'] = Html::class_names(
			array(
				'fps-flex fps-items-start fps-gap-2 fps-text-xs fps-leading-5',
				self::VARIANTS[ $variant ],
				$attributes['class'] ?? '',
			)
		);

		if ( 'error' === $variant && $args['submit_error'] ) {
			$attributes['role'] = 'alert';
		}

		$html = '<p' . Html::attributes( $attributes ) . '>';
		if ( '' !== (string) $args['icon'] ) {
			$html .= '<span class="fps-shrink-0">' . Html::svg( (string) $args['icon'] ) . '</span>';
		}

		$html .= '<span>' . esc_html( (string) $args['message'] ) . '</span></p>';
		return $html;
	}

	/**
	 * Echoes feedback markup.
	 *
	 * @param array<string, mixed> $args Message arguments.
	 */
	public static function render( array $args ): void {
		echo self::make( $args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped by make().
	}
}
