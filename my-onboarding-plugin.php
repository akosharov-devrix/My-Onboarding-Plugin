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

/**
 * Navigation menu filter
 *
 * @param array  $items An array of menu items.
 * @param object $menu  The menu object.
 */
function mop_wp_get_nav_menu_items( $items, $menu ) {

	if ( ! is_admin() && is_user_logged_in() ) {
		$items[] = (object) array(
			'ID'               => PHP_INT_MAX,
			'title'            => 'Profile',
			'url'              => get_edit_profile_url(),
			'menu_item_parent' => 0,
			'menu_order'       => null,
			'type'             => null,
			'object'           => null,
			'object_id'        => null,
			'db_id'            => null,
			'classes'          => null,
		);
	}
	return $items;
}

// Set filters to wp_get_nav_menu_items.
add_filter( 'wp_get_nav_menu_items', 'mop_wp_get_nav_menu_items', 10, 2 );
