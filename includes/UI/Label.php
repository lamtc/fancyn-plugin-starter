<?php
/**
 * Label component.
 *
 * @package Fancyn_Plugin_Starter
 */

namespace Fancyn\PluginStarter\UI;

use Fancyn\PluginStarter\Support\Html;

/**
 * Renders a semantic form label.
 */
final class Label {
	/**
	 * Returns label markup.
	 *
	 * @param array<string, mixed> $args Label arguments.
	 */
	public static function make( array $args ): string {
		$args = array_merge(
			array(
				'for'        => '',
				'text'       => '',
				'required'   => false,
				'optional'   => '',
				'attributes' => array(),
			),
			$args
		);

		$attributes          = is_array( $args['attributes'] ) ? $args['attributes'] : array();
		$attributes['for']   = (string) $args['for'];
		$attributes['class'] = Html::class_names(
			array(
				'fps-flex fps-items-baseline fps-gap-2 fps-text-sm fps-font-semibold fps-leading-5 fps-text-ink',
				$attributes['class'] ?? '',
			)
		);

		$html  = '<label' . Html::attributes( $attributes ) . '>';
		$html .= '<span>' . esc_html( (string) $args['text'] ) . '</span>';

		if ( $args['required'] ) {
			$html .= '<span class="fps-text-danger" aria-hidden="true">*</span>';
			$html .= '<span class="screen-reader-text"> ' . esc_html__( 'required', 'fancyn-plugin-starter' ) . '</span>';
		}

		if ( '' !== (string) $args['optional'] ) {
			$html .= '<span class="fps-text-xs fps-font-normal fps-text-ink-muted">' . esc_html( (string) $args['optional'] ) . '</span>';
		}

		return $html . '</label>';
	}

	/**
	 * Echoes label markup.
	 *
	 * @param array<string, mixed> $args Label arguments.
	 */
	public static function render( array $args ): void {
		echo self::make( $args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped by make().
	}
}
