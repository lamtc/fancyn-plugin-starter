<?php
/**
 * Shared UI component contract tests.
 *
 * @package Fancyn_Plugin_Starter
 */

namespace Fancyn\PluginStarter\Tests\UI;

use Brain\Monkey;
use Brain\Monkey\Functions;
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
use PHPUnit\Framework\TestCase;

/**
 * Enforces a consistent stateless renderer contract.
 */
final class ComponentContractTest extends TestCase {
	/** Starts WordPress function mocks. */
	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();
		Functions\when( 'esc_html' )->alias( 'htmlspecialchars' );
		Functions\when( 'esc_html__' )->returnArg( 1 );
		Functions\when( 'esc_attr' )->alias( 'htmlspecialchars' );
		Functions\when( 'esc_url' )->returnArg( 1 );
		Functions\when( 'wp_kses' )->returnArg( 1 );
		Functions\when( 'sanitize_key' )->alias( static fn ( string $value ): string => strtolower( preg_replace( '/[^a-zA-Z0-9_-]/', '-', $value ) ?? '' ) );
	}

	/** Stops WordPress function mocks. */
	protected function tearDown(): void {
		Monkey\tearDown();
		parent::tearDown();
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
