<?php
/**
 * Radio component.
 *
 * @package Fancyn_Plugin_Starter
 */

namespace Fancyn\PluginStarter\UI;

use Fancyn\PluginStarter\Support\Html;

/** Renders single radios and native radio groups. */
final class Radio {
	/**
	 * Returns radio markup.
	 *
	 * @param array<string, mixed> $args Radio arguments.
	 */
	public static function make( array $args ): string {
		$args = array_merge(
			array(
				'id'          => '',
				'name'        => '',
				'value'       => '',
				'label'       => '',
				'description' => '',
				'checked'     => false,
				'disabled'    => false,
				'required'    => false,
				'options'     => array(),
				'attributes'  => array(),
			),
			$args
		);
		if ( is_array( $args['options'] ) && $args['options'] ) {
			$html = '<fieldset class="fps-space-y-3"><legend class="fps-text-sm fps-font-semibold fps-text-ink">' . esc_html( (string) $args['label'] ) . '</legend>';
			foreach ( $args['options'] as $value => $label ) {
				$option            = $args;
				$option['options'] = array();
				$option['value']   = (string) $value;
				$option['label']   = (string) $label;
				$option['checked'] = (string) $args['value'] === (string) $value;
				$option['id']      = (string) $args['name'] . '-' . (string) $value;
				$html             .= self::single( $option );
			}
			return $html . '</fieldset>';
		}
		return self::single( $args );
	}
	/**
	 * Echoes radio markup.
	 *
	 * @param array<string, mixed> $args Arguments.
	 */
	public static function render( array $args ): void {
		echo self::make( $args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped by make().
	}

	/**
	 * Returns one radio.
	 *
	 * @param array<string, mixed> $args Arguments.
	 */
	private static function single( array $args ): string {
		$fallback_id         = (string) $args['name'] . '-' . (string) $args['value'];
		$id                  = '' !== (string) $args['id'] ? (string) $args['id'] : $fallback_id;
		$attributes          = array_merge(
			is_array( $args['attributes'] ) ? $args['attributes'] : array(),
			array(
				'id'       => $id,
				'name'     => (string) $args['name'],
				'type'     => 'radio',
				'value'    => (string) $args['value'],
				'checked'  => (bool) $args['checked'],
				'disabled' => (bool) $args['disabled'],
				'required' => (bool) $args['required'],
			)
		);
		$attributes['class'] = Html::class_names( array( 'fps-form-radio fps-mt-0.5 fps-h-4 fps-w-4 fps-border-line-strong fps-text-brand focus:fps-ring-brand-soft', $attributes['class'] ?? '' ) );
		$html                = '<label class="fps-flex fps-items-start fps-gap-3" for="' . esc_attr( $id ) . '"><input' . Html::attributes( $attributes ) . '><span><span class="fps-block fps-text-sm fps-font-medium fps-text-ink">' . esc_html( (string) $args['label'] ) . '</span>';
		if ( '' !== (string) $args['description'] ) {
			$html .= '<span class="fps-block fps-text-xs fps-text-ink-secondary">' . esc_html( (string) $args['description'] ) . '</span>';
		}
		return $html . '</span></label>';
	}
}
