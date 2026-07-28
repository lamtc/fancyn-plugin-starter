<?php
/**
 * Switch component tests.
 *
 * @package Fancyn_Plugin_Starter
 */

namespace Fancyn\PluginStarter\Tests\UI;

use Brain\Monkey;
use Brain\Monkey\Functions;
use Fancyn\PluginStarter\UI\SwitchField;
use PHPUnit\Framework\TestCase;

/** Tests form-submittable switch markup. */
final class SwitchFieldTest extends TestCase {
	/** Starts mocks. */
	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();
		Functions\when( 'esc_html' )->alias( 'htmlspecialchars' );
		Functions\when( 'esc_attr' )->alias( 'htmlspecialchars' ); }
	/** Stops mocks. */
	protected function tearDown(): void {
		Monkey\tearDown();
		parent::tearDown(); }

	/** Uses a real checkbox with accessible label and native states. */
	public function test_make_uses_native_checkbox(): void {
		$html = SwitchField::make(
			array(
				'name'        => 'notifications',
				'value'       => '1',
				'label'       => 'Notifications',
				'description' => 'Send updates.',
				'checked'     => true,
				'disabled'    => true,
			)
		);
		$this->assertStringContainsString( 'type="checkbox"', $html );
		$this->assertStringContainsString( 'role="switch"', $html );
		$this->assertStringContainsString( ' checked', $html );
		$this->assertStringContainsString( ' disabled', $html );
		$this->assertStringContainsString( 'Notifications', $html );
		$this->assertStringNotContainsString( 'x-data', $html );
	}
}
