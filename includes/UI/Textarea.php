<?php
/**
 * Textarea component.
 *
 * @package Fancyn_Plugin_Starter
 */

namespace Fancyn\PluginStarter\UI;

use Fancyn\PluginStarter\Support\Html;

/**
 * Renders a native textarea with optional progressive enhancement.
 */
final class Textarea {
	/**
	 * Returns composed textarea markup.
	 *
	 * @param array<string, mixed> $args Textarea arguments.
	 */
	public static function make( array $args ): string {
		$args = array_merge(
			array(
				'id'                => '',
				'name'              => '',
				'value'             => '',
				'label'             => '',
				'optional'          => '',
				'description'       => '',
				'error'             => '',
				'rows'              => 4,
				'maxlength'         => null,
				'character_counter' => false,
				'auto_resize'       => false,
				'required'          => false,
				'disabled'          => false,
				'readonly'          => false,
				'attributes'        => array(),
			),
			$args
		);

		$field = FormField::make(
			array(
				'id'          => (string) $args['id'],
				'name'        => (string) $args['name'],
				'label'       => (string) $args['label'],
				'optional'    => (string) $args['optional'],
				'description' => (string) $args['description'],
				'error'       => (string) $args['error'],
				'required'    => (bool) $args['required'],
				'control'     => static fn ( array $field_attributes ): string => self::control( $args, $field_attributes ),
			)
		);

		if ( $args['character_counter'] ) {
			$maximum = is_numeric( $args['maxlength'] ) ? (int) $args['maxlength'] : 0;
			$current = strlen( (string) $args['value'] );
			$field  .= '<p class="fps-mt-2 fps-text-right fps-text-xs fps-text-ink-muted" data-character-count-output>';
			$field  .= esc_html( $current . ( $maximum ? ' / ' . $maximum : '' ) ) . '</p>';
		}

		return $field;
	}

	/**
	 * Echoes textarea markup.
	 *
	 * @param array<string, mixed> $args Textarea arguments.
	 */
	public static function render( array $args ): void {
		echo self::make( $args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped by make().
	}

	/**
	 * Returns the native textarea element.
	 *
	 * @param array<string, mixed> $args             Textarea arguments.
	 * @param array<string, mixed> $field_attributes ARIA attributes from FormField.
	 */
	private static function control( array $args, array $field_attributes ): string {
		$attributes = array_merge(
			is_array( $args['attributes'] ) ? $args['attributes'] : array(),
			$field_attributes,
			array(
				'name'                   => (string) $args['name'],
				'rows'                   => max( 1, (int) $args['rows'] ),
				'maxlength'              => is_numeric( $args['maxlength'] ) ? (int) $args['maxlength'] : null,
				'required'               => (bool) $args['required'],
				'disabled'               => (bool) $args['disabled'],
				'readonly'               => (bool) $args['readonly'],
				'data-character-counter' => $args['character_counter'] ? '1' : null,
				'data-auto-resize'       => $args['auto_resize'] ? '1' : null,
			)
		);

		$attributes['class'] = Html::class_names(
			array(
				'fps-block fps-w-full fps-resize-y fps-rounded-control fps-border fps-bg-surface fps-px-3 fps-py-2.5 fps-text-sm fps-leading-6 fps-text-ink fps-transition-colors fps-duration-150 placeholder:fps-text-ink-muted focus:fps-border-brand focus:fps-outline-none focus:fps-ring-2 focus:fps-ring-brand-soft disabled:fps-cursor-not-allowed disabled:fps-bg-surface-muted disabled:fps-opacity-70 read-only:fps-bg-surface-muted',
				$args['error'] ? 'fps-border-danger focus:fps-border-danger focus:fps-ring-danger-soft' : 'fps-border-line',
				$attributes['class'] ?? '',
			)
		);

		return '<textarea' . Html::attributes( $attributes ) . '>' . esc_html( (string) $args['value'] ) . '</textarea>';
	}
}
