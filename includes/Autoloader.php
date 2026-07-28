<?php
/**
 * Project-owned namespace autoloader.
 *
 * @package Fancyn_Plugin_Starter
 */

namespace Fancyn\PluginStarter;

/**
 * Loads project classes from the includes directory.
 */
final class Autoloader {
	/**
	 * Namespace prefix including its trailing separator.
	 *
	 * @var string
	 */
	private string $namespace;

	/**
	 * Canonical base directory.
	 *
	 * @var string
	 */
	private string $base_directory;

	/**
	 * Creates the autoloader.
	 *
	 * @param string $namespace_prefix Namespace prefix.
	 * @param string $base_directory Class file directory.
	 */
	public function __construct( string $namespace_prefix, string $base_directory ) {
		$this->namespace      = trim( $namespace_prefix, '\\' ) . '\\';
		$this->base_directory = rtrim( str_replace( '\\', '/', $base_directory ), '/' ) . '/';
	}

	/**
	 * Registers the loader with PHP.
	 */
	public function register(): void {
		spl_autoload_register( array( $this, 'load' ) );
	}

	/**
	 * Loads a class in the configured namespace.
	 *
	 * @param string $class_name Fully-qualified class name.
	 * @return bool Whether a class file was included.
	 */
	public function load( string $class_name ): bool {
		if ( ! str_starts_with( $class_name, $this->namespace ) ) {
			return false;
		}

		$relative = substr( $class_name, strlen( $this->namespace ) );
		$segments = explode( '\\', $relative );
		if ( '' === $relative || array_filter( $segments, static fn( string $segment ): bool => '' === $segment || '.' === $segment || '..' === $segment ) ) {
			return false;
		}

		$file = $this->base_directory . implode( '/', $segments ) . '.php';
		if ( ! is_file( $file ) ) {
			return false;
		}

		$resolved_file = realpath( $file );
		$base          = realpath( $this->base_directory );
		if ( false === $resolved_file || false === $base || ! str_starts_with( str_replace( '\\', '/', $resolved_file ), rtrim( str_replace( '\\', '/', $base ), '/' ) . '/' ) ) {
			return false;
		}

		require_once $resolved_file;

		return true;
	}
}
