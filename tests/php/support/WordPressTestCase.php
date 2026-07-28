<?php
/**
 * Base case and state registry for WordPress unit tests.
 *
 * @package Fancyn_Plugin_Starter
 */

namespace Fancyn\PluginStarter\Tests\Support;

use PHPUnit\Framework\TestCase;

// phpcs:disable Generic.Files.OneObjectStructurePerFile.MultipleFound -- Closely coupled test-only support types share one bootstrap file.
// phpcs:disable Squiz.Commenting.VariableComment.MissingVar -- Generic array shapes are documented inline for static analysis.
// phpcs:disable Squiz.Commenting.FunctionComment.MissingParamTag -- Test helper signatures are self-describing and documented by their public contract.
// phpcs:disable WordPress.NamingConventions.ValidFunctionName.MethodNameInvalid -- Public test API follows the approved camelCase contract.

/**
 * Stores deterministic WordPress function behavior and calls.
 */
final class WordPressFunctionRegistry {
	/** Fixed function results. @var array<string, mixed> */
	private static array $stubs = array();

	/** Function callbacks. @var array<string, callable> */
	private static array $callbacks = array();

	/** Recorded function calls. @var array<string, array<int, array<int, mixed>>> */
	private static array $calls = array();

	/**
	 * Registers a fixed result.
	 *
	 * @param string $function_name Function name.
	 * @param mixed  $return_value  Fixed result.
	 */
	public static function stub( string $function_name, mixed $return_value ): void {
		self::$stubs[ $function_name ] = $return_value;
		unset( self::$callbacks[ $function_name ] );
	}

	/**
	 * Registers a callback.
	 *
	 * @param string   $function_name Function name.
	 * @param callable $callback      Callback implementation.
	 */
	public static function stubCallback( string $function_name, callable $callback ): void {
		self::$callbacks[ $function_name ] = $callback;
		unset( self::$stubs[ $function_name ] );
	}

	/**
	 * Invokes configured behavior and records arguments.
	 *
	 * @param string $function_name Function name.
	 * @param array  $arguments     Function arguments.
	 * @param mixed  $fallback      Default result or callable.
	 */
	public static function invoke( string $function_name, array $arguments, mixed $fallback = null ): mixed {
		self::$calls[ $function_name ][] = $arguments;
		if ( isset( self::$callbacks[ $function_name ] ) ) {
			return ( self::$callbacks[ $function_name ] )( ...$arguments );
		}
		if ( array_key_exists( $function_name, self::$stubs ) ) {
			return self::$stubs[ $function_name ];
		}

		return is_callable( $fallback ) ? $fallback( ...$arguments ) : $fallback;
	}

	/**
	 * Returns recorded calls.
	 *
	 * @param string $function_name Function name.
	 */
	public static function calls( string $function_name ): array {
		return self::$calls[ $function_name ] ?? array();
	}

	/** Clears all state. */
	public static function reset(): void {
		self::$stubs     = array();
		self::$callbacks = array();
		self::$calls     = array();
	}
}

/** Represents wp_send_json_* termination. */
final class JsonResponseException extends \RuntimeException {
	/** Creates a JSON termination exception. */
	public function __construct( public readonly bool $success, public readonly mixed $payload, public readonly int $status ) {
		parent::__construct( 'WordPress JSON response terminated execution.', $status );
	}
}

/** Represents wp_die termination. */
final class WpDieException extends \RuntimeException {
	/** Creates a wp_die termination exception. */
	public function __construct( public readonly mixed $message_value, public readonly mixed $title_value, public readonly array $arguments ) {
		parent::__construct( is_scalar( $message_value ) ? (string) $message_value : 'WordPress terminated execution.' );
	}
}

/** Base test case exposing the shim API. */
abstract class WordPressTestCase extends TestCase {
	/** Resets shim state before each test. */
	protected function setUp(): void {
		parent::setUp();
		WordPressFunctionRegistry::reset();
	}

	/** Resets shim state after each test. */
	protected function tearDown(): void {
		WordPressFunctionRegistry::reset();
		parent::tearDown();
	}

	/** Registers a fixed function result. */
	public static function stub( string $function_name, mixed $return_value ): void {
		WordPressFunctionRegistry::stub( $function_name, $return_value );
	}

	/** Registers a function callback. */
	public static function stubCallback( string $function_name, callable $callback ): void {
		WordPressFunctionRegistry::stubCallback( $function_name, $callback );
	}

	/** Returns recorded function calls. */
	public static function calls( string $function_name ): array {
		return WordPressFunctionRegistry::calls( $function_name );
	}

	/** Asserts a function call count. */
	public static function assertCalledTimes( string $function_name, int $times ): void {
		self::assertCount( $times, self::calls( $function_name ), sprintf( 'Expected %s to be called %d times.', $function_name, $times ) );
	}
}
