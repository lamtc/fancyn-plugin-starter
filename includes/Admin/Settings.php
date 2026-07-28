<?php
/**
 * Settings service.
 *
 * @package Fancyn_Plugin_Starter
 */

namespace Fancyn\PluginStarter\Admin;

use Fancyn\PluginStarter\Core\ServiceProvider;
use Fancyn\PluginStarter\Support\Validation;
use Fancyn\PluginStarter\Support\View;

/**
 * Owns the starter Settings API schema and page view model.
 */
final class Settings implements ServiceProvider {
	/** Settings API group. */
	public const GROUP = 'fancyn_plugin_starter';

	/** Stored option name. */
	public const OPTION = 'fancyn_plugin_starter_settings';

	/**
	 * View renderer.
	 *
	 * @var View
	 */
	private View $view;

	/**
	 * Field type schema.
	 *
	 * @var array<string, string>
	 */
	private array $schema = array(
		'company_name'   => 'text',
		'company_email'  => 'email',
		'website_url'    => 'url',
		'items_per_page' => 'integer',
		'enabled'        => 'boolean',
		'brand_color'    => 'color',
		'logo_id'        => 'attachment_id',
		'content'        => 'rich_text',
	);

	/**
	 * Creates the settings service.
	 *
	 * @param View $view View renderer.
	 */
	public function __construct( View $view ) {
		$this->view = $view;
	}

	/** Registers the admin-init hook. */
	public function register(): void {
		add_action( 'admin_init', array( $this, 'register_settings' ) );
	}

	/** Boots the service. */
	public function boot(): void {}

	/** Registers the option and its logical section. */
	public function register_settings(): void {
		register_setting(
			self::GROUP,
			self::OPTION,
			array(
				'type'              => 'array',
				'default'           => $this->defaults(),
				'sanitize_callback' => array( $this, 'sanitize' ),
				'show_in_rest'      => false,
			)
		);

		add_settings_section(
			'fancyn_plugin_starter_general',
			__( 'General settings', 'fancyn-plugin-starter' ),
			'__return_false',
			self::GROUP
		);
	}

	/**
	 * Sanitizes known fields and discards unknown input.
	 *
	 * @param mixed $input Untrusted option input.
	 * @return array<string, mixed>
	 */
	public function sanitize( mixed $input ): array {
		$input    = is_array( $input ) ? $input : array();
		$previous = get_option( self::OPTION, $this->defaults() );
		$previous = is_array( $previous ) ? $previous : $this->defaults();
		$result   = array();

		foreach ( $this->schema as $field => $type ) {
			$value            = array_key_exists( $field, $input ) ? $input[ $field ] : ( 'boolean' === $type ? false : ( $previous[ $field ] ?? null ) );
			$result[ $field ] = Validation::by_type( $type, $value, $previous[ $field ] ?? $this->defaults()[ $field ] );
		}

		return $result;
	}

	/** Renders the settings page. */
	public function render_page(): void {
		$options = get_option( self::OPTION, $this->defaults() );
		$this->view->render(
			'admin/settings',
			array(
				'options' => is_array( $options ) ? array_merge( $this->defaults(), $options ) : $this->defaults(),
				'group'   => self::GROUP,
				'option'  => self::OPTION,
			)
		);
	}

	/**
	 * Returns default option values.
	 *
	 * @return array<string, mixed> Default option values.
	 */
	public function defaults(): array {
		return array(
			'company_name'   => '',
			'company_email'  => '',
			'website_url'    => '',
			'items_per_page' => 10,
			'enabled'        => false,
			'brand_color'    => '#F4511E',
			'logo_id'        => 0,
			'content'        => '',
		);
	}
}
