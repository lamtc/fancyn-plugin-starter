<?php
/**
 * Input component.
 *
 * @package Fancyn_Plugin_Starter
 */

namespace Fancyn\PluginStarter\UI;

use Fancyn\PluginStarter\Support\Html;

/**
 * Renders native text-like inputs with optional field composition.
 */
final class Input {
	/**
	 * Supported native input types.
	 *
	 * @var array<int, string>
	 */
	private const TYPES = array( 'text', 'email', 'number', 'password', 'url', 'search', 'hidden' );

	/**
	 * Returns input or composed field markup.
	 *
	 * @param array<string, mixed> $args Input arguments.
	 */
	public static function make( array $args ): string {
		$args = array_merge(
			array(
				'id'          => '',
				'name'        => '',
				'type'        => 'text',
				'value'       => '',
				'label'       => '',
				'optional'    => '',
				'description' => '',
				'error'       => '',
				'required'    => false,
				'disabled'    => false,
				'readonly'    => false,
				'attributes'  => array(),
			),
			$args
		);

		$args['type'] = in_array( $args['type'], self::TYPES, true ) ? $args['type'] : 'text';
		if ( 'hidden' === $args['type'] ) {
			return self::control( $args, array() );
		}

		return FormField::make(
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
	}

	/**
	 * Echoes input markup.
	 *
	 * @param array<string, mixed> $args Input arguments.
	 */
	public static function render( array $args ): void {
		echo self::make( $args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped by make().
	}

	/**
	 * Returns the native input element.
	 *
	 * @param array<string, mixed> $args             Input arguments.
	 * @param array<string, mixed> $field_attributes ARIA attributes from FormField.
	 */
	private static function control( array $args, array $field_attributes ): string {
		$attributes = array_merge(
			is_array( $args['attributes'] ) ? $args['attributes'] : array(),
			$field_attributes,
			array(
				'name'     => (string) $args['name'],
				'type'     => (string) $args['type'],
				'value'    => (string) $args['value'],
				'required' => (bool) $args['required'],
				'disabled' => (bool) $args['disabled'],
				'readonly' => (bool) $args['readonly'],
			)
		);

		if ( 'hidden' !== $args['type'] ) {
			$attributes['class'] = Html::class_names(
				array(
					'fps-block fps-h-11 fps-w-full fps-rounded-control fps-border fps-bg-surface fps-px-3 fps-text-sm fps-text-ink fps-transition-colors fps-duration-150 placeholder:fps-text-ink-muted focus:fps-border-brand focus:fps-outline-none focus:fps-ring-2 focus:fps-ring-brand-soft disabled:fps-cursor-not-allowed disabled:fps-bg-surface-muted disabled:fps-opacity-70 read-only:fps-bg-surface-muted',
					$args['error'] ? 'fps-border-danger focus:fps-border-danger focus:fps-ring-danger-soft' : 'fps-border-line',
					$attributes['class'] ?? '',
				)
			);
		}

		return '<input' . Html::attributes( $attributes ) . '>';
	}
}
