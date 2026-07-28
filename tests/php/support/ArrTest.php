<?php
/**
 * Array helper tests.
 *
 * @package Fancyn_Plugin_Starter
 */

namespace Fancyn\PluginStarter\Tests\Support;

use Fancyn\PluginStarter\Support\Arr;
use PHPUnit\Framework\TestCase;

/**
 * Tests safe nested array reads.
 */
final class ArrTest extends TestCase {
	/**
	 * Reads nested values using dot notation.
	 */
	public function test_get_reads_nested_values(): void {
		$data = array( 'company' => array( 'address' => array( 'city' => 'Da Nang' ) ) );

		$this->assertSame( 'Da Nang', Arr::get( $data, 'company.address.city' ) );
	}

	/**
	 * Returns the supplied default for missing paths.
	 */
	public function test_get_returns_default_for_missing_path(): void {
		$this->assertSame( 'fallback', Arr::get( array(), 'missing.value', 'fallback' ) );
	}
}
