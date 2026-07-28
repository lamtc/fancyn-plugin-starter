<?php
/**
 * Plugin Name: Fancyn Plugin Starter
 * Description: A production-ready WordPress plugin starter kit.
 * Version: 1.0.0
 * Requires at least: 6.5
 * Requires PHP: 8.1
 * Text Domain: fancyn-plugin-starter
 *
 * @package Fancyn_Plugin_Starter
 */

use Fancyn\PluginStarter\Core\Activator;
use Fancyn\PluginStarter\Core\Deactivator;
use Fancyn\PluginStarter\Core\Plugin;
use Fancyn\PluginStarter\Admin\Admin;
use Fancyn\PluginStarter\Admin\Assets;
use Fancyn\PluginStarter\Admin\Menu;
use Fancyn\PluginStarter\Admin\Settings;
use Fancyn\PluginStarter\Support\View;
use Fancyn\PluginStarter\Http\Ajax;
use Fancyn\PluginStarter\Http\Rest;

defined( 'ABSPATH' ) || exit;

define( 'FANCYN_PLUGIN_STARTER_VERSION', '1.0.0' );
define( 'FANCYN_PLUGIN_STARTER_FILE', __FILE__ );
define( 'FANCYN_PLUGIN_STARTER_PATH', plugin_dir_path( __FILE__ ) );
define( 'FANCYN_PLUGIN_STARTER_URL', plugin_dir_url( __FILE__ ) );

if ( ! defined( 'FANCYN_PLUGIN_STARTER_DEV_MODE' ) ) {
	define( 'FANCYN_PLUGIN_STARTER_DEV_MODE', false );
}

require_once FANCYN_PLUGIN_STARTER_PATH . 'includes/Autoloader.php';

$fancyn_plugin_starter_autoloader = new Fancyn\PluginStarter\Autoloader(
	'Fancyn\\PluginStarter',
	FANCYN_PLUGIN_STARTER_PATH . 'includes'
);
$fancyn_plugin_starter_autoloader->register();

register_activation_hook( __FILE__, array( Activator::class, 'activate' ) );
register_deactivation_hook( __FILE__, array( Deactivator::class, 'deactivate' ) );

add_action(
	'plugins_loaded',
	static function (): void {
		$view     = new View( FANCYN_PLUGIN_STARTER_PATH . 'templates' );
		$settings = new Settings( $view );
		$menu     = new Menu( $view, $settings, FANCYN_PLUGIN_STARTER_DEV_MODE );
		$assets   = new Assets(
			array(
				'plugin_url'     => FANCYN_PLUGIN_STARTER_URL,
				'plugin_path'    => FANCYN_PLUGIN_STARTER_PATH,
				'version'        => FANCYN_PLUGIN_STARTER_VERSION,
				'dev_server_url' => 'http://127.0.0.1:5173',
				'allowed_hooks'  => array(
					'toplevel_page_fancyn-plugin-starter',
					'fancyn-plugin-starter_page_fancyn-plugin-starter-settings',
					'tools_page_fancyn-plugin-starter-components',
				),
			)
		);
		$plugin   = new Plugin( array( new Admin( array( $settings, $menu, $assets ) ), new Rest(), new Ajax() ) );
		$plugin->run();
	}
);
