<?php
/**
 * Form section component.
 *
 * @package Fancyn_Plugin_Starter
 */

namespace Fancyn\PluginStarter\UI;

use Fancyn\PluginStarter\Support\Html;

/**
 * Groups related fields under one semantic heading.
 */
final class FormSection {
	/**
	 * Fixed section variant classes.
	 *
	 * @var array<string, string>
	 */
	private const VARIANTS = array(
		'card'    => 'fps-rounded-card fps-bg-surface fps-p-6 fps-shadow-card',
		'flat'    => 'fps-bg-transparent fps-py-5',
		'divided' => 'fps-border-y fps-border-line fps-py-6',
		'compact' => 'fps-rounded-card fps-bg-surface fps-p-5 fps-shadow-card',
	);

	/**
	 * Returns section markup.
	 *
	 * Content must be markup produced by trusted starter components.
	 *
	 * @param array<string, mixed> $args Section arguments.
	 */
	public static function make( array $args ): string {
		$args = array_merge(
			array(
				'variant'       => 'card',
				'title'         => '',
				'description'   => '',
				'content'       => '',
				'actions'       => '',
				'heading_level' => 2,
				'attributes'    => array(),
			),
			$args
		);

		$variant = isset( self::VARIANTS[ $args['variant'] ] ) ? (string) $args['variant'] : 'card';
		$level   = min( 6, max( 2, (int) $args['heading_level'] ) );

		$attributes                 = is_array( $args['attributes'] ) ? $args['attributes'] : array();
		$attributes['data-variant'] = $variant;
		$attributes['class']        = Html::class_names(
			array(
				self::VARIANTS[ $variant ],
				$attributes['class'] ?? '',
			)
		);

		$html  = '<section' . Html::attributes( $attributes ) . '>';
		$html .= '<div class="fps-flex fps-flex-col fps-gap-4 sm:fps-flex-row sm:fps-items-start sm:fps-justify-between">';
		$html .= '<div class="fps-space-y-1">';
		$html .= '<h' . $level . ' class="fps-text-lg fps-font-bold fps-leading-tight fps-text-ink">' . esc_html( (string) $args['title'] ) . '</h' . $level . '>';

		if ( '' !== (string) $args['description'] ) {
			$html .= '<p class="fps-max-w-3xl fps-text-sm fps-leading-6 fps-text-ink-secondary">' . esc_html( (string) $args['description'] ) . '</p>';
		}

		$html .= '</div>';
		if ( '' !== (string) $args['actions'] ) {
			$html .= '<div class="fps-flex fps-flex-wrap fps-items-center fps-gap-2">' . self::filter_actions( (string) $args['actions'] ) . '</div>';
		}
		$html .= '</div>';
		$html .= '<div class="fps-mt-5 fps-space-y-6">';
		$html .= (string) $args['content']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Trusted component markup slot.
		$html .= '</div></section>';

		return $html;
	}

	/**
	 * Echoes section markup.
	 *
	 * @param array<string, mixed> $args Section arguments.
	 */
	public static function render( array $args ): void {
		echo self::make( $args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped by make().
	}

	/**
	 * Filters the optional action slot.
	 *
	 * @param string $actions Action markup.
	 */
	private static function filter_actions( string $actions ): string {
		$common = array(
			'aria-label'    => true,
			'aria-disabled' => true,
			'class'         => true,
			'data-variant'  => true,
			'data-size'     => true,
			'id'            => true,
			'role'          => true,
			'title'         => true,
		);

		return wp_kses(
			$actions,
			array(
				'a'      => array_merge(
					$common,
					array(
						'href'   => true,
						'rel'    => true,
						'target' => true,
					)
				),
				'button' => array_merge(
					$common,
					array(
						'disabled' => true,
						'name'     => true,
						'type'     => true,
						'value'    => true,
					)
				),
				'span'   => $common,
				'svg'    => array(
					'aria-hidden' => true,
					'class'       => true,
					'fill'        => true,
					'viewbox'     => true,
				),
				'path'   => array(
					'd'            => true,
					'fill'         => true,
					'stroke'       => true,
					'stroke-width' => true,
				),
			)
		);
	}
}
