<?php
/**
 * Shared UI component contract tests.
 *
 * @package Fancyn_Plugin_Starter
 */

namespace Fancyn\PluginStarter\Tests\UI;

use Fancyn\PluginStarter\UI\Button;
use Fancyn\PluginStarter\UI\Checkbox;
use Fancyn\PluginStarter\UI\FormField;
use Fancyn\PluginStarter\UI\FormSection;
use Fancyn\PluginStarter\UI\Input;
use Fancyn\PluginStarter\UI\Label;
use Fancyn\PluginStarter\UI\Radio;
use Fancyn\PluginStarter\UI\Select;
use Fancyn\PluginStarter\UI\SwitchField;
use Fancyn\PluginStarter\UI\Textarea;
use Fancyn\PluginStarter\UI\ValidationMessage;
use Fancyn\PluginStarter\Tests\Support\WordPressTestCase;

/**
 * Enforces a consistent stateless renderer contract.
 */
final class ComponentContractTest extends WordPressTestCase {
	/** Starts WordPress function mocks. */
	protected function setUp(): void {
		parent::setUp();
		self::stubCallback( 'esc_html', static fn ( mixed $value ): string => htmlspecialchars( (string) $value ) );
		self::stubCallback( 'esc_html__', static fn ( mixed $value ): mixed => $value );
		self::stubCallback( 'esc_attr', static fn ( mixed $value ): string => htmlspecialchars( (string) $value ) );
		self::stubCallback( 'esc_url', static fn ( mixed $value ): mixed => $value );
		self::stubCallback( 'wp_kses', static fn ( mixed $value ): mixed => $value );
		self::stubCallback( 'sanitize_key', static fn ( string $value ): string => strtolower( preg_replace( '/[^a-zA-Z0-9_-]/', '-', $value ) ?? '' ) );
	}

	/**
	 * Requires render to echo exactly what make returns.
	 *
	 * @dataProvider components
	 * @param class-string         $component Component class.
	 * @param array<string, mixed> $args Component arguments.
	 */
	public function test_render_matches_make( string $component, array $args ): void {
		$expected = $component::make( $args );

		$this->expectOutputString( $expected );
		$component::render( $args );
	}

	/**
	 * Requires every authored class token to use the plugin prefix.
	 *
	 * @dataProvider components
	 * @param class-string         $component Component class.
	 * @param array<string, mixed> $args Component arguments.
	 */
	public function test_all_authored_classes_are_prefixed( string $component, array $args ): void {
		$html = $component::make( $args );
		preg_match_all( '/class="([^"]+)"/', $html, $matches );

		foreach ( $matches[1] as $class_list ) {
			$class_names = preg_split( '/\s+/', $class_list );
			$class_names = false === $class_names ? array() : $class_names;
			foreach ( $class_names as $class_name ) {
				$is_prefixed = str_starts_with( $class_name, 'fps-' ) || str_contains( $class_name, ':fps-' ) || 'screen-reader-text' === $class_name;
				$this->assertTrue( $is_prefixed, 'Unprefixed class: ' . $class_name );
			}
		}
	}

	/**
	 * Component fixtures.
	 *
	 * @return array<string, array{0: class-string, 1: array<string, mixed>}>
	 */
	public static function components(): array {
		return array(
			'button'     => array( Button::class, array( 'text' => 'Save' ) ),
			'input'      => array(
				Input::class,
				array(
					'name'  => 'name',
					'label' => 'Name',
				),
			),
			'textarea'   => array(
				Textarea::class,
				array(
					'name'  => 'notes',
					'label' => 'Notes',
				),
			),
			'select'     => array(
				Select::class,
				array(
					'name'    => 'type',
					'label'   => 'Type',
					'options' => array( 'a' => 'A' ),
				),
			),
			'checkbox'   => array(
				Checkbox::class,
				array(
					'name'  => 'enabled',
					'label' => 'Enabled',
				),
			),
			'radio'      => array(
				Radio::class,
				array(
					'name'  => 'mode',
					'label' => 'Mode',
					'value' => 'a',
				),
			),
			'switch'     => array(
				SwitchField::class,
				array(
					'name'  => 'active',
					'label' => 'Active',
				),
			),
			'label'      => array(
				Label::class,
				array(
					'for'  => 'field',
					'text' => 'Field',
				),
			),
			'form-field' => array(
				FormField::class,
				array(
					'name'    => 'field',
					'label'   => 'Field',
					'control' => static fn (): string => '<input>',
				),
			),
			'section'    => array(
				FormSection::class,
				array(
					'title'   => 'Section',
					'content' => '<input>',
				),
			),
			'message'    => array( ValidationMessage::class, array( 'message' => 'Error' ) ),
		);
	}
}
