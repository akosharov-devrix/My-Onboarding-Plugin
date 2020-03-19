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
	if ( ! is_admin() && is_user_logged_in() && 'test' === $menu->slug ) {
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
			'target'           => null,
			'xfn'              => null,
		);
	}
	return $items;
}

// Set filters to wp_get_nav_menu_items.
add_filter( 'wp_get_nav_menu_items', 'mop_wp_get_nav_menu_items', 10, 2 );

/**
 * Send email to admin when user profile is updated
 *
 * @param int $user_id The user ID.
 */
function mop_admin_email_user_profile_update( $user_id ) {
	$profile_user = get_userdata( $user_id );
	$to           = '';
	$users        = get_users( array( 'role' => 'Administrator' ) );
	foreach ( $users as $user ) {
		$to .= ( empty( $to ) ? '' : ', ' ) . $user->user_email;
	}
	$subject = 'User updated profile';
	$message = 'User ' . $profile_user->display_name . ' has updated their profile. User ID: ' . $profile_user->ID;
	wp_mail( $to, $subject, $message );
}

// Set action function for personal_options_update and edit_user_profile_update.
add_action( 'personal_options_update', 'mop_admin_email_user_profile_update' );
add_action( 'edit_user_profile_update', 'mop_admin_email_user_profile_update' );
