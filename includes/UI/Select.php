<?php
/**
 * Select component.
 *
 * @package Fancyn_Plugin_Starter
 */

namespace Fancyn\PluginStarter\UI;

use Fancyn\PluginStarter\Support\Html;

/** Renders native selects with opt-in enhancement data. */
final class Select {
	/**
	 * Returns composed select markup.
	 *
	 * @param array<string, mixed> $args Select arguments.
	 */
	public static function make( array $args ): string {
		$args = array_merge(
			array(
				'id'          => '',
				'name'        => '',
				'label'       => '',
				'optional'    => '',
				'description' => '',
				'error'       => '',
				'value'       => '',
				'placeholder' => '',
				'options'     => array(),
				'multiple'    => false,
				'required'    => false,
				'disabled'    => false,
				'enhanced'    => false,
				'searchable'  => false,
				'clearable'   => false,
				'create'      => false,
				'attributes'  => array(),
			),
			$args
		);

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
	 * Echoes select markup.
	 *
	 * @param array<string, mixed> $args Select arguments.
	 */
	public static function render( array $args ): void {
		echo self::make( $args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped by make().
	}

	/**
	 * Returns the native element.
	 *
	 * @param array<string, mixed> $args             Arguments.
	 * @param array<string, mixed> $field_attributes ARIA attributes.
	 */
	private static function control( array $args, array $field_attributes ): string {
		$name = (string) $args['name'];
		if ( $args['multiple'] && ! str_ends_with( $name, '[]' ) ) {
			$name .= '[]';
		}
		$attributes          = array_merge(
			is_array( $args['attributes'] ) ? $args['attributes'] : array(),
			$field_attributes,
			array(
				'name'                 => $name,
				'multiple'             => (bool) $args['multiple'],
				'required'             => (bool) $args['required'],
				'disabled'             => (bool) $args['disabled'],
				'data-enhanced-select' => $args['enhanced'] ? '1' : null,
				'data-searchable'      => $args['searchable'] ? '1' : null,
				'data-clearable'       => $args['clearable'] ? '1' : null,
				'data-create'          => $args['create'] ? '1' : null,
				'data-placeholder'     => (string) $args['placeholder'],
			)
		);
		$attributes['class'] = Html::class_names(
			array(
				'fps-block fps-h-11 fps-w-full fps-rounded-control fps-border fps-bg-surface fps-px-3 fps-text-sm fps-text-ink focus:fps-border-brand focus:fps-outline-none focus:fps-ring-2 focus:fps-ring-brand-soft disabled:fps-bg-surface-muted',
				$args['error'] ? 'fps-border-danger' : 'fps-border-line',
				$attributes['class'] ?? '',
			)
		);
		$selected            = is_array( $args['value'] ) ? array_map( 'strval', $args['value'] ) : array( (string) $args['value'] );
		$html                = '<select' . Html::attributes( $attributes ) . '>';
		if ( '' !== (string) $args['placeholder'] ) {
			$html .= '<option value=""' . ( in_array( '', $selected, true ) ? ' selected' : '' ) . '>' . esc_html( (string) $args['placeholder'] ) . '</option>';
		}
		$html .= self::options( is_array( $args['options'] ) ? $args['options'] : array(), $selected );
		return $html . '</select>';
	}

	/**
	 * Renders options recursively.
	 *
	 * @param array<string|int, mixed> $options  Options.
	 * @param array<int, string>       $selected Selected values.
	 */
	private static function options( array $options, array $selected ): string {
		$html = '';
		foreach ( $options as $value => $label ) {
			if ( is_array( $label ) ) {
				$html .= '<optgroup label="' . esc_attr( (string) $value ) . '">' . self::options( $label, $selected ) . '</optgroup>';
				continue;
			}
			$html .= '<option value="' . esc_attr( (string) $value ) . '"' . ( in_array( (string) $value, $selected, true ) ? ' selected' : '' ) . '>' . esc_html( (string) $label ) . '</option>';
		}
		return $html;
	}
}
