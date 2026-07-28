<?php
/**
 * Form field component.
 *
 * @package Fancyn_Plugin_Starter
 */

namespace Fancyn\PluginStarter\UI;

use Fancyn\PluginStarter\Support\Html;

/**
 * Composes the accessible relationship around a native control.
 */
final class FormField {
	/**
	 * Returns field markup.
	 *
	 * The control callback receives the final ID and ARIA attributes.
	 *
	 * @param array<string, mixed> $args Field arguments.
	 */
	public static function make( array $args ): string {
		$args = array_merge(
			array(
				'id'                 => '',
				'name'               => '',
				'label'              => '',
				'optional'           => '',
				'required'           => false,
				'description'        => '',
				'error'              => '',
				'submit_error'       => false,
				'control'            => null,
				'control_attributes' => array(),
				'attributes'         => array(),
			),
			$args
		);

		$id = self::resolve_id( (string) $args['id'], (string) $args['name'], (string) $args['label'] );

		$description_id = '' !== (string) $args['description'] ? $id . '-description' : '';
		$error_id       = '' !== (string) $args['error'] ? $id . '-error' : '';
		$described_by   = array_filter(
			array(
				$args['control_attributes']['aria-describedby'] ?? '',
				$description_id,
				$error_id,
			)
		);

		$control_attributes                     = is_array( $args['control_attributes'] ) ? $args['control_attributes'] : array();
		$control_attributes['id']               = $id;
		$control_attributes['aria-describedby'] = implode( ' ', array_unique( $described_by ) );
		$control_attributes['aria-invalid']     = $error_id ? 'true' : null;

		$wrapper_attributes          = is_array( $args['attributes'] ) ? $args['attributes'] : array();
		$wrapper_attributes['class'] = Html::class_names(
			array(
				'fps-space-y-2',
				$wrapper_attributes['class'] ?? '',
			)
		);

		$html  = '<div' . Html::attributes( $wrapper_attributes ) . '>';
		$html .= Label::make(
			array(
				'for'      => $id,
				'text'     => (string) $args['label'],
				'required' => (bool) $args['required'],
				'optional' => (string) $args['optional'],
			)
		);

		if ( is_callable( $args['control'] ) ) {
			$html .= (string) call_user_func( $args['control'], $control_attributes );
		}

		if ( $description_id ) {
			$html .= '<p id="' . esc_attr( $description_id ) . '" class="fps-text-xs fps-leading-5 fps-text-ink-secondary">';
			$html .= esc_html( (string) $args['description'] ) . '</p>';
		}

		if ( $error_id ) {
			$html .= ValidationMessage::make(
				array(
					'id'           => $error_id,
					'variant'      => 'error',
					'message'      => (string) $args['error'],
					'submit_error' => (bool) $args['submit_error'],
				)
			);
		}

		return $html . '</div>';
	}

	/**
	 * Echoes field markup.
	 *
	 * @param array<string, mixed> $args Field arguments.
	 */
	public static function render( array $args ): void {
		echo self::make( $args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped by make().
	}

	/**
	 * Resolves a stable field ID.
	 *
	 * @param string $id    Explicit ID.
	 * @param string $name  Control name.
	 * @param string $label Display label.
	 */
	private static function resolve_id( string $id, string $name, string $label ): string {
		$candidate = '' !== $id ? $id : $name;
		$candidate = '' !== $candidate ? $candidate : $label;
		$candidate = sanitize_key( str_replace( array( ' ', '[', ']' ), '-', $candidate ) );

		return '' !== $candidate ? $candidate : 'fancyn-field';
	}
}
