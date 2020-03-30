<?php
/**
 * Plugin bootloader
 *
 * @package DX/MOP
 */

namespace DX\MOP;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Bootloader_Plugin
 *
 * This is the bootloader class that load and initialize the plugin's classes.
 *
 * @package DX/MOP
 */
class Bootloader_Plugin {
	/**
	 * Object contruct method.
	 */
	public function __construct() {
		add_action( 'plugins_loaded', array( $this, 'include_classes' ), 10 );
		add_action( 'init', array( $this, 'init' ), 20 );
		add_action( 'widgets_init', array( $this, 'widgets_init' ), 20 );
		add_action( 'widgets_init', array( 'DX\MOP\Cpt_Student', 'register_sidebars' ) );
	}

	/**
	 * This function includes all the classes.
	 */
	public function include_classes() {
		require MOP__PLUGIN_DIR . 'includes/class-cpt-student.php';
		require MOP__PLUGIN_DIR . 'includes/class-student-widget.php';
	}

	/**
	 * This function initializes the classes/traits on initialization of WordPress.
	 */
	public function init() {
		new Cpt_Student();
	}

	/**
	 * This function registers widgets.
	 */
	public function widgets_init() {
		// Set widgets_init action function.
		add_action( 'widgets_init', array( $this, 'register_sidebars' ) );
		register_widget( 'DX\MOP\Student_Widget' );
	}
}
