<?php
/**
 * Checkbox component.
 *
 * @package Fancyn_Plugin_Starter
 */

namespace Fancyn\PluginStarter\UI;

use Fancyn\PluginStarter\Support\Html;

/** Renders a native checkbox with text-first context. */
final class Checkbox {
	/**
	 * Returns checkbox markup.
	 *
	 * @param array<string, mixed> $args Checkbox arguments.
	 */
	public static function make( array $args ): string {
		$args                = array_merge(
			array(
				'id'            => '',
				'name'          => '',
				'value'         => '1',
				'label'         => '',
				'description'   => '',
				'checked'       => false,
				'disabled'      => false,
				'required'      => false,
				'indeterminate' => false,
				'attributes'    => array(),
			),
			$args
		);
		$id                  = '' !== (string) $args['id'] ? (string) $args['id'] : (string) $args['name'];
		$attributes          = array_merge(
			is_array( $args['attributes'] ) ? $args['attributes'] : array(),
			array(
				'id'                 => $id,
				'name'               => (string) $args['name'],
				'type'               => 'checkbox',
				'value'              => (string) $args['value'],
				'checked'            => (bool) $args['checked'],
				'disabled'           => (bool) $args['disabled'],
				'required'           => (bool) $args['required'],
				'data-indeterminate' => $args['indeterminate'] ? '1' : null,
			)
		);
		$attributes['class'] = Html::class_names( array( 'fps-form-checkbox fps-mt-0.5 fps-h-4 fps-w-4 fps-rounded fps-border-line-strong fps-text-brand focus:fps-ring-brand-soft', $attributes['class'] ?? '' ) );
		$html                = '<label class="fps-flex fps-items-start fps-gap-3" for="' . esc_attr( $id ) . '"><input' . Html::attributes( $attributes ) . '><span class="fps-space-y-1"><span class="fps-block fps-text-sm fps-font-semibold fps-text-ink">' . esc_html( (string) $args['label'] ) . '</span>';
		if ( '' !== (string) $args['description'] ) {
			$html .= '<span class="fps-block fps-text-xs fps-leading-5 fps-text-ink-secondary">' . esc_html( (string) $args['description'] ) . '</span>';
		}
		return $html . '</span></label>';
	}
	/**
	 * Echoes checkbox markup.
	 *
	 * @param array<string, mixed> $args Arguments.
	 */
	public static function render( array $args ): void {
		echo self::make( $args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped by make().
	}
}
