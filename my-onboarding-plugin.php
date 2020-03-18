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

/**
 * Content prepend filter function
 *
 * @param string $content content value.
 */
function mop_prepend_filter_the_content( $content ) {
	$content = 'Onboarding Filter: ' . $content;
	return $content;
}

/**
 * Content append filter function
 *
 * @param string $content content value.
 */
function mop_append_filter_the_content( $content ) {
	$content .= 'By Atanas Kosharov';
	return $content;
}

/**
 * Content replace filter function
 *
 * @param string $content content value.
 */
function mop_replace_filter_the_content( $content ) {
	$content = preg_replace( '/(<\/p>)/i', '${1}<div style="display: none;"></div>', $content, 1 );
	return $content;
}

/**
 * Content paragraph filter function
 *
 * @param string $content content value.
 */
function mop_paragraph_filter_the_content( $content ) {
	$content = '<p>Some paragraph</p>' . $content;
	return $content;
}

// Set filters to the_conent.
add_filter( 'the_content', 'mop_prepend_filter_the_content' );
add_filter( 'the_content', 'mop_append_filter_the_content' );
add_filter( 'the_content', 'mop_replace_filter_the_content' );
add_filter( 'the_content', 'mop_paragraph_filter_the_content', 9 );
