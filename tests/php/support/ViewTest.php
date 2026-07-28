<?php
/**
 * View renderer tests.
 *
 * @package Fancyn_Plugin_Starter
 */

namespace Fancyn\PluginStarter\Tests\Support;

use Brain\Monkey;
use Brain\Monkey\Functions;
use Fancyn\PluginStarter\Support\View;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * Tests constrained PHP template rendering.
 */
final class ViewTest extends TestCase {
	/** Starts WordPress function mocks. */
	protected function setUp(): void {
		parent::setUp();
		Monkey\setUp();
		Functions\when( 'esc_html' )->returnArg( 1 );
	}

	/** Stops WordPress function mocks. */
	protected function tearDown(): void {
		Monkey\tearDown();
		parent::tearDown();
	}

	/**
	 * Renders prepared variables and returns the buffered result.
	 */
	public function test_make_returns_rendered_template(): void {
		$view = new View( dirname( __DIR__ ) . '/fixtures/views' );

		$this->assertSame( '<p>Hello Codex</p>', $view->make( 'greeting', array( 'name' => 'Codex' ) ) );
	}

	/**
	 * Echoes the same content returned by make.
	 */
	public function test_render_echoes_template(): void {
		$view = new View( dirname( __DIR__ ) . '/fixtures/views' );

		$this->expectOutputString( '<p>Hello Codex</p>' );
		$view->render( 'greeting', array( 'name' => 'Codex' ) );
	}

	/**
	 * Rejects paths that escape the configured template root.
	 */
	public function test_rejects_path_traversal(): void {
		$view = new View( dirname( __DIR__ ) . '/fixtures/views' );

		$this->expectException( InvalidArgumentException::class );
		$view->make( '../../bootstrap' );
	}

	/**
	 * Rejects missing templates with an actionable exception.
	 */
	public function test_rejects_missing_template(): void {
		$view = new View( dirname( __DIR__ ) . '/fixtures/views' );

		$this->expectException( InvalidArgumentException::class );
		$view->make( 'missing' );
	}
}
