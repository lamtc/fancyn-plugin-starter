<?php
/**
 * Switch component tests.
 *
 * @package Fancyn_Plugin_Starter
 */

namespace Fancyn\PluginStarter\Tests\UI;

use Fancyn\PluginStarter\UI\SwitchField;
use Fancyn\PluginStarter\Tests\Support\WordPressTestCase;

/** Tests form-submittable switch markup. */
final class SwitchFieldTest extends WordPressTestCase {
	/** Starts mocks. */
	protected function setUp(): void {
		parent::setUp();
		self::stubCallback( 'esc_html', static fn ( mixed $value ): string => htmlspecialchars( (string) $value ) );
		self::stubCallback( 'esc_attr', static fn ( mixed $value ): string => htmlspecialchars( (string) $value ) ); }

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
