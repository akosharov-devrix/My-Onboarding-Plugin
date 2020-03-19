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

$mop_filters_enabled = get_option( 'mop-filters-enabled', '0' );

/**
 * Content prepend filter function
 *
 * @param string $content content value.
 */
function mop_prepend_filter_the_content( $content ) {
	global $post;
	if ( 'student' === $post->post_type ) {
		$content = 'Onboarding Filter: ' . $content;
	}
	return $content;
}

/**
 * Content append filter function
 *
 * @param string $content content value.
 */
function mop_append_filter_the_content( $content ) {
	global $post;
	if ( 'student' === $post->post_type ) {
		$content .= 'By Atanas Kosharov';
	}
	return $content;
}

/**
 * Content replace filter function
 *
 * @param string $content content value.
 */
function mop_replace_filter_the_content( $content ) {
	global $post;
	if ( 'student' === $post->post_type ) {
		$content = preg_replace( '/(<\/p>)/i', '${1}<div style="display: none;"></div>', $content, 1 );
	}
	return $content;
}

/**
 * Content paragraph filter function
 *
 * @param string $content content value.
 */
function mop_paragraph_filter_the_content( $content ) {
	global $post;
	if ( 'student' === $post->post_type ) {
		$content = '<p>Some paragraph</p>' . $content;
	}
	return $content;
}

// Set filters to the_conent if enabled.
if ( '1' === $mop_filters_enabled ) {
	add_filter( 'the_content', 'mop_prepend_filter_the_content' );
	add_filter( 'the_content', 'mop_append_filter_the_content' );
	add_filter( 'the_content', 'mop_replace_filter_the_content' );
	add_filter( 'the_content', 'mop_paragraph_filter_the_content', 9 );
}

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

/**
 * Set admin option page
 */
function mop_admin_menu() {
	add_options_page( 'My Onboarding', 'My Onboarding Plugin', 'manage_options', 'my-onboarding-admin-menu', 'mop_plugin_options' );
	add_menu_page( 'My Onboarding', 'My Onboarding', 'manage_options', 'my-onboarding-admin-main-menu', 'mop_plugin_options' );
}

// Set adction function for admin_menu.
add_action( 'admin_menu', 'mop_admin_menu' );

/**
 * Admin option page output
 */
function mop_plugin_options() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'You do not have sufficient permissions to access this page.' );
	}
	$mop_filters_enabled = get_option( 'mop-filters-enabled', '0' );
	$mop_students_page   = get_option( 'mop-students-page', 0 );
	?>
	<div class="wrap">
		<h2>My Onboarding Plugin Options</h2>
		<table class="form-table">
			<tr>
				<th scope="row">Plugin Filters</th>
				<td>
					<div>
						<input type="checkbox" id="mop-filters-enabled" name="mop-filters-enabled" value="1" data-nonce="<?php echo esc_attr( wp_create_nonce( 'mop-filters-enabled' ) ); ?>" <?php checked( '1', $mop_filters_enabled ); ?> />
						<label for="mop-filters-enabled">Filters Enabled</label>
					</div>
				</td>
			</tr>
			<tr>
				<th scope="row">Students Page</th>
				<td>
					<div>
						<label for="mop-students-page">Select page</label>
						<select id="mop-students-page" name="mop-students-page" data-nonce="<?php echo esc_attr( wp_create_nonce( 'mop-students-page' ) ); ?>">
	<?php
	$pages = get_pages();
	foreach ( $pages as $page ) {
		?>
							<option value="<?php echo esc_attr( $page->ID ); ?>" <?php selected( $mop_students_page, $page->ID ); ?>><?php echo esc_attr( $page->post_title ); ?></option>
		<?php
	}
	?>
						</select>
					</div>
				</td>
			</tr>
	</div>
<script type="text/javascript" >
jQuery( document ).ready( function($) {
	jQuery('#mop-filters-enabled').change(function () {
		var data = {
			'action': 'mop_plugin_options_action',
			'nonce': jQuery( this ).data( 'nonce' ),
			'mop-filters-enabled': jQuery( this ).prop( 'checked' ) ? '1' : '0',
		};
		jQuery.post( ajaxurl, data, function( response ) {
			alert( response );
		} );
	});

	jQuery('#mop-students-page').change(function () {
		var data = {
			'action': 'mop_plugin_options_action',
			'nonce': jQuery( this ).data( 'nonce' ),
			'mop-students-page': jQuery( this ).val(),
		};
		jQuery.post( ajaxurl, data, function( response ) {
			alert( response );
		} );
	});
});
</script>
	<?php
}

/**
 * Ajax action function for Filters Enabled checkbox
 */
function mop_plugin_options_action() {
	ob_clean();
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( 'You do not have sufficient permissions to access this page.' );
	}
	if ( isset( $_POST['mop-filters-enabled'] ) ) {
		$nonce_check = check_ajax_referer( 'mop-filters-enabled', 'nonce', false );
		if ( ! $nonce_check ) {
			wp_die( 'Invalid nonce.' );
		}
		$mop_filters_enabled = sanitize_text_field( wp_unslash( $_POST['mop-filters-enabled'] ) );
		if ( '1' === $mop_filters_enabled ) {
			echo 'Filters Enabled.';
		} else {
			$mop_filters_enabled = '0';
			echo 'Filters Disabled.';
		}
		update_option( 'mop-filters-enabled', $mop_filters_enabled );
	}
	if ( isset( $_POST['mop-students-page'] ) ) {
		$nonce_check = check_ajax_referer( 'mop-students-page', 'nonce', false );
		if ( ! $nonce_check ) {
			wp_die( 'Invalid nonce.' );
		}
		$mop_students_page = sanitize_text_field( wp_unslash( $_POST['mop-students-page'] ) );
		update_option( 'mop-students-page', $mop_students_page );
		echo 'Students page selected.';
	}
	wp_die();
}

// Set ajax action function.
add_action( 'wp_ajax_mop_plugin_options_action', 'mop_plugin_options_action' );

/**
 * Init custom post type
 */
function mop_cpt_init() {
	$labels = array(
		'name'           => 'Students',
		'singular_name'  => 'Student',
		'menu_name'      => 'Students',
		'name_admin_bar' => 'Student',
		'add_new_item'   => 'Add New Student',
	);

	$supports = array(
		'thumbnail',
		'excerpt',
		'title',
		'editor',
	);

	$args = array(
		'label'               => 'Student',
		'description'         => 'Student Description',
		'labels'              => $labels,
		'supports'            => $supports,
		'taxonomies'          => array( 'category' ),
		'hierarchical'        => false,
		'public'              => true,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'menu_position'       => 5,
		'show_in_admin_bar'   => true,
		'show_in_nav_menus'   => true,
		'can_export'          => true,
		'has_archive'         => true,
		'exclude_from_search' => false,
		'publicly_queryable'  => true,
		'capability_type'     => 'page',
	);

	register_post_type(
		'student',
		$args,
	);
}

// Set init function.
add_action( 'init', 'mop_cpt_init' );

/**
 * Flush rewrite rules on plugin activation for the custom post type.
 */
function mop_rewrite_flush() {
	mop_cpt_init();
	flush_rewrite_rules();
}

// Set activation function.
register_activation_hook( __FILE__, 'mop_rewrite_flush' );
