<?php
/**
 * Tests for the project-owned autoloader.
 *
 * @package Fancyn_Plugin_Starter
 */

namespace Fancyn\PluginStarter\Tests;

use Fancyn\PluginStarter\Autoloader;
use PHPUnit\Framework\TestCase;

/**
 * Verifies safe project namespace resolution.
 */
final class AutoloaderTest extends TestCase {
	/**
	 * Temporary fixture directories.
	 *
	 * @var array<int, string>
	 */
	private array $temporary_directories = array();

	/**
	 * Remove fixture directories.
	 */
	protected function tearDown(): void {
		foreach ( $this->temporary_directories as $directory ) {
			$this->remove_directory( $directory );
		}

		parent::tearDown();
	}

	/**
	 * Resolves nested classes only within the configured namespace.
	 */
	public function test_resolves_only_project_namespace_inside_includes(): void {
		$namespace = 'Example\\Starter' . str_replace( '.', '', uniqid( '', true ) );
		$root      = $this->fixture_with_class( $namespace . '\\Core\\Demo', 'Core/Demo.php' );
		$loader    = new Autoloader( $namespace, $root );

		$this->assertTrue( $loader->load( $namespace . '\\Core\\Demo' ) );
		$this->assertTrue( class_exists( $namespace . '\\Core\\Demo', false ) );
		$this->assertFalse( $loader->load( 'Other\\Core\\Demo' ) );
		$this->assertFalse( $loader->load( $namespace . '\\..\\Secrets' ) );
	}

	/**
	 * Rejects malformed relative names and missing files.
	 */
	public function test_rejects_empty_segments_and_missing_files(): void {
		$root   = $this->fixture_directory();
		$loader = new Autoloader( 'Example\\Starter', $root );

		$this->assertFalse( $loader->load( 'Example\\Starter\\Core\\' ) );
		$this->assertFalse( $loader->load( 'Example\\Starter\\Core\\\\Demo' ) );
		$this->assertFalse( $loader->load( 'Example\\Starter\\Missing' ) );
	}

	/**
	 * Creates a class fixture.
	 *
	 * @param string $class_name    Fully-qualified class name.
	 * @param string $relative_path Relative file path.
	 * @return string
	 */
	private function fixture_with_class( string $class_name, string $relative_path ): string {
		$root             = $this->fixture_directory();
		$separator        = strrpos( $class_name, '\\' );
		$namespace        = substr( $class_name, 0, $separator );
		$short_class_name = substr( $class_name, $separator + 1 );
		$file             = $root . '/' . $relative_path;

		// phpcs:disable WordPress.WP.AlternativeFunctions.file_system_operations_mkdir, WordPress.WP.AlternativeFunctions.file_system_operations_file_put_contents -- Native temporary test fixtures do not load WordPress.
		mkdir( dirname( $file ), 0777, true );
		file_put_contents( $file, "<?php\nnamespace {$namespace};\nfinal class {$short_class_name} {}\n" );
		// phpcs:enable

		return $root;
	}

	/**
	 * Creates a fixture directory.
	 *
	 * @return string
	 */
	private function fixture_directory(): string {
		$directory = sys_get_temp_dir() . '/fps-autoloader-' . uniqid( '', true );
		// phpcs:disable WordPress.WP.AlternativeFunctions.file_system_operations_mkdir -- Native temporary test fixtures do not load WordPress.
		mkdir( $directory, 0777, true );
		// phpcs:enable
		$this->temporary_directories[] = $directory;

		return $directory;
	}

	/**
	 * Recursively removes a fixture directory.
	 *
	 * @param string $directory Directory path.
	 */
	private function remove_directory( string $directory ): void {
		if ( ! is_dir( $directory ) ) {
			return;
		}

		$scanned = scandir( $directory );
		$items   = array_diff( false === $scanned ? array() : $scanned, array( '.', '..' ) );
		foreach ( $items as $item ) {
			$path = $directory . '/' . $item;
			if ( is_dir( $path ) ) {
				$this->remove_directory( $path );
			} else {
				// phpcs:ignore WordPress.WP.AlternativeFunctions.unlink_unlink -- Native temporary test fixtures do not load WordPress.
				unlink( $path );
			}
		}
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_rmdir -- Native temporary test fixtures do not load WordPress.
		rmdir( $directory );
	}
}
