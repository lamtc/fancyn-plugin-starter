<?php
/**
 * PHPUnit bootstrap file.
 *
 * @package Fancyn_Plugin_Starter
 */

$fancyn_plugin_starter_root = dirname( __DIR__, 2 );

require_once __DIR__ . '/support/WordPressTestCase.php';
require_once __DIR__ . '/support/WordPress.php';
require_once $fancyn_plugin_starter_root . '/includes/Autoloader.php';

$fancyn_plugin_starter_autoloader = new Fancyn\PluginStarter\Autoloader(
	'Fancyn\\PluginStarter',
	$fancyn_plugin_starter_root . '/includes'
);
$fancyn_plugin_starter_autoloader->register();
