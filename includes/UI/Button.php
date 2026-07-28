<?php
/**
 * Button component.
 *
 * @package Fancyn_Plugin_Starter
 */

namespace Fancyn\PluginStarter\UI;

use Fancyn\PluginStarter\Support\Html;

/**
 * Renders native buttons and action links.
 */
final class Button {
	/**
	 * Fixed variant classes.
	 *
	 * @var array<string, string>
	 */
	private const VARIANTS = array(
		'primary'   => 'fps-bg-brand fps-text-ink-inverse hover:fps-bg-brand-hover focus-visible:fps-ring-brand-soft',
		'secondary' => 'fps-bg-ink fps-text-ink-inverse hover:fps-bg-ink-secondary focus-visible:fps-ring-surface-muted',
		'outline'   => 'fps-border fps-border-line-strong fps-bg-surface fps-text-ink hover:fps-bg-surface-muted focus-visible:fps-ring-brand-soft',
		'ghost'     => 'fps-bg-transparent fps-text-ink hover:fps-bg-surface-muted focus-visible:fps-ring-brand-soft',
		'danger'    => 'fps-bg-danger fps-text-ink-inverse hover:fps-opacity-90 focus-visible:fps-ring-danger-soft',
		'link'      => 'fps-bg-transparent fps-px-0 fps-text-brand hover:fps-text-brand-hover hover:fps-underline focus-visible:fps-ring-brand-soft',
	);

	/**
	 * Fixed size classes.
	 *
	 * @var array<string, string>
	 */
	private const SIZES = array(
		'sm'   => 'fps-h-9 fps-px-3 fps-text-xs',
		'md'   => 'fps-h-10 fps-px-4 fps-text-sm',
		'lg'   => 'fps-h-11 fps-px-5 fps-text-sm',
		'icon' => 'fps-h-10 fps-w-10 fps-p-0',
	);

	/**
	 * Returns action markup.
	 *
	 * @param array<string, mixed> $args Button arguments.
	 */
	public static function make( array $args ): string {
		$args = array_merge(
			array(
				'type'       => 'button',
				'variant'    => 'primary',
				'size'       => 'md',
				'loading'    => false,
				'disabled'   => false,
				'icon'       => '',
				'text'       => '',
				'href'       => '',
				'aria-label' => '',
				'attributes' => array(),
			),
			$args
		);

		$variant  = isset( self::VARIANTS[ $args['variant'] ] ) ? (string) $args['variant'] : 'primary';
		$size     = isset( self::SIZES[ $args['size'] ] ) ? (string) $args['size'] : 'md';
		$loading  = (bool) $args['loading'];
		$disabled = (bool) $args['disabled'] || $loading;
		$is_link  = '' !== (string) $args['href'];

		$attributes                 = is_array( $args['attributes'] ) ? $args['attributes'] : array();
		$attributes['class']        = Html::class_names(
			array(
				'fps-relative fps-inline-flex fps-items-center fps-justify-center fps-gap-2 fps-rounded-control fps-font-semibold fps-transition-colors fps-duration-150 focus-visible:fps-outline-none focus-visible:fps-ring-2 disabled:fps-cursor-not-allowed disabled:fps-opacity-50',
				self::VARIANTS[ $variant ],
				self::SIZES[ $size ],
				$attributes['class'] ?? '',
			)
		);
		$attributes['data-variant'] = $variant;
		$attributes['data-size']    = $size;
		$attributes['aria-label']   = (string) $args['aria-label'];
		$attributes['aria-busy']    = $loading ? 'true' : null;

		if ( $is_link ) {
			$tag                = 'a';
			$attributes['href'] = esc_url( (string) $args['href'] );
			if ( $disabled ) {
				$attributes['aria-disabled'] = 'true';
				$attributes['tabindex']      = '-1';
			}
		} else {
			$tag                    = 'button';
			$attributes['type']     = in_array( $args['type'], array( 'button', 'submit', 'reset' ), true ) ? $args['type'] : 'button';
			$attributes['disabled'] = $disabled;
		}

		$content = '';
		if ( '' !== (string) $args['icon'] ) {
			$content .= '<span class="fps-shrink-0" aria-hidden="true">' . Html::svg( (string) $args['icon'] ) . '</span>';
		}

		$text_class = $loading ? 'fps-opacity-0' : '';
		$content   .= '<span class="' . esc_attr( $text_class ) . '">' . esc_html( (string) $args['text'] ) . '</span>';
		if ( $loading ) {
			$content .= '<span class="fps-absolute fps-inset-0 fps-flex fps-items-center fps-justify-center" aria-hidden="true">…</span>';
		}

		return '<' . $tag . Html::attributes( $attributes ) . '>' . $content . '</' . $tag . '>';
	}

	/**
	 * Echoes button markup.
	 *
	 * @param array<string, mixed> $args Button arguments.
	 */
	public static function render( array $args ): void {
		echo self::make( $args ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped by make().
	}
}
