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

defined( 'ABSPATH' ) || exit;

define( 'FANCYN_PLUGIN_STARTER_VERSION', '1.0.0' );
define( 'FANCYN_PLUGIN_STARTER_FILE', __FILE__ );
define( 'FANCYN_PLUGIN_STARTER_PATH', plugin_dir_path( __FILE__ ) );
define( 'FANCYN_PLUGIN_STARTER_URL', plugin_dir_url( __FILE__ ) );

require_once FANCYN_PLUGIN_STARTER_PATH . 'vendor/autoload.php';

register_activation_hook( __FILE__, array( Activator::class, 'activate' ) );
register_deactivation_hook( __FILE__, array( Deactivator::class, 'deactivate' ) );

add_action(
	'plugins_loaded',
	static function (): void {
		$plugin = new Plugin( array() );
		$plugin->run();
	}
);
