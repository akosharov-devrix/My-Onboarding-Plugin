<?php
/**
 * Plugin Name: My Onboarding Plugin
 * Plugin URI: https://wordpress.org/plugins/my-onboarding-plugin/
 * Description: Plugin for onboarding tasks
 * Version: 1.0
 * Author: Atanas Kosharov
 * Author URI: http://devrix.com/
 * Text Domain: my-onboarding-plugin
 * Domain Path: /languages
 * GitHub Plugin URI: https://github.com/akosharov-devrix/my-onboarding-plugin
 * License: None
 *
 * @package my-onboarding-plugin
 */

namespace DX\MOP;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'MOP_PLUGIN__FILE__', __FILE__ );
define( 'MOP__PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

$mop_recursive_flag = false;

require MOP__PLUGIN_DIR . 'includes/class-bootloader-plugin.php';

new Bootloader_Plugin();
