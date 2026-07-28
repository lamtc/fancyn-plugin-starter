<?php
/**
 * Switch component.
 *
 * @package Fancyn_Plugin_Starter
 */

namespace Fancyn\PluginStarter\UI;

use Fancyn\PluginStarter\Support\Html;

/** Renders a checkbox styled as an accessible switch. */
final class SwitchField {
	/**
	 * Returns switch markup.
	 *
	 * @param array<string, mixed> $args Arguments.
	 */
	public static function make( array $args ): string {
		$args       = array_merge(
			array(
				'id'          => '',
				'name'        => '',
				'value'       => '1',
				'label'       => '',
				'description' => '',
				'checked'     => false,
				'disabled'    => false,
				'attributes'  => array(),
			),
			$args
		);
		$id         = '' !== (string) $args['id'] ? (string) $args['id'] : (string) $args['name'];
		$attributes = array_merge(
			is_array( $args['attributes'] ) ? $args['attributes'] : array(),
			array(
				'id'       => $id,
				'name'     => (string) $args['name'],
				'type'     => 'checkbox',
				'role'     => 'switch',
				'value'    => (string) $args['value'],
				'checked'  => (bool) $args['checked'],
				'disabled' => (bool) $args['disabled'],
				'class'    => 'fps-peer fps-sr-only',
			)
		);
		$html       = '<label class="fps-flex fps-items-start fps-justify-between fps-gap-4" for="' . esc_attr( $id ) . '"><span class="fps-space-y-1"><span class="fps-block fps-text-sm fps-font-semibold fps-text-ink">' . esc_html( (string) $args['label'] ) . '</span>';
		if ( '' !== (string) $args['description'] ) {
			$html .= '<span class="fps-block fps-text-xs fps-leading-5 fps-text-ink-secondary">' . esc_html( (string) $args['description'] ) . '</span>';
		}
		$html .= '</span><span class="fps-relative fps-inline-flex fps-shrink-0"><input' . Html::attributes( $attributes ) . '><span aria-hidden="true" class="fps-h-6 fps-w-11 fps-rounded-full fps-bg-line-strong fps-transition-colors fps-duration-150 peer-checked:fps-bg-brand peer-focus-visible:fps-ring-2 peer-focus-visible:fps-ring-brand-soft peer-disabled:fps-opacity-50"><span class="fps-absolute fps-left-1 fps-top-1 fps-h-4 fps-w-4 fps-rounded-full fps-bg-surface fps-transition-transform fps-duration-150 peer-checked:fps-translate-x-5"></span></span></span></label>';
		return $html;
	}
	/**
	 * Echoes switch markup.
	 *
	 * @param array<string, mixed> $args Arguments.
	 */
	public static function render( array $args ): void {
		echo self::make( $args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped by make().
	}
}
