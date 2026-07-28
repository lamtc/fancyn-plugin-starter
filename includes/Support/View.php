<?php
/**
 * PHP view renderer.
 *
 * @package Fancyn_Plugin_Starter
 */

namespace Fancyn\PluginStarter\Support;

use InvalidArgumentException;
use Throwable;

/**
 * Renders templates located beneath one configured root.
 */
final class View {
	/**
	 * Canonical template root.
	 *
	 * @var string
	 */
	private string $root;

	/**
	 * Creates the view renderer.
	 *
	 * @param string $root Template root directory.
	 * @throws InvalidArgumentException When the template root is invalid.
	 */
	public function __construct( string $root ) {
		$resolved = realpath( $root );
		if ( false === $resolved || ! is_dir( $resolved ) ) {
			throw new InvalidArgumentException( 'The template root does not exist.' );
		}

		$this->root = rtrim( $resolved, '/\\' );
	}

	/**
	 * Returns rendered template output.
	 *
	 * @param string               $template Slash-delimited template name without extension.
	 * @param array<string, mixed> $data     Prepared template variables.
	 * @throws Throwable When template execution fails.
	 */
	public function make( string $template, array $data = array() ): string {
		$path = $this->resolve( $template );

		extract( $data, EXTR_SKIP ); // phpcs:ignore WordPress.PHP.DontExtract.extract_extract -- Constrained view data API.
		ob_start();

		try {
			include $path;
			return (string) ob_get_clean();
		} catch ( Throwable $throwable ) {
			ob_end_clean();
			throw $throwable;
		}
	}

	/**
	 * Echoes rendered template output.
	 *
	 * @param string               $template Slash-delimited template name without extension.
	 * @param array<string, mixed> $data     Prepared template variables.
	 */
	public function render( string $template, array $data = array() ): void {
		echo $this->make( $template, $data ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Templates escape by context.
	}

	/**
	 * Resolves a template without allowing traversal.
	 *
	 * @param string $template Template name.
	 * @throws InvalidArgumentException When the template is invalid or missing.
	 */
	private function resolve( string $template ): string {
		if ( 1 !== preg_match( '/^[a-zA-Z0-9\/_-]+$/', $template ) ) {
			throw new InvalidArgumentException( 'The template name is invalid.' );
		}

		$path = realpath( $this->root . DIRECTORY_SEPARATOR . $template . '.php' );
		if ( false === $path || ! str_starts_with( $path, $this->root . DIRECTORY_SEPARATOR ) ) {
			throw new InvalidArgumentException( 'The requested template does not exist.' );
		}

		return $path;
	}
}
