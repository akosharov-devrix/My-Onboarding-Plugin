<?php
/**
 * CPT Student class
 *
 * @package DX/MOP
 */

namespace DX\MOP;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Cpt_Student
 *
 * This is the bootloader class that load and initialize the plugin's classes.
 *
 * @package DX/MOP
 */
class Cpt_Student {
	/**
	 * Object contruct method.
	 */
	public function __construct() {
		// Set filters to the_conent if enabled.
		$filters_enabled = get_option( 'mop-filters-enabled', '0' );
		if ( '1' === $filters_enabled ) {
			add_filter( 'the_content', array( $this, 'prepend_filter_the_content' ) );
			add_filter( 'the_content', array( $this, 'append_filter_the_content' ) );
			add_filter( 'the_content', array( $this, 'replace_filter_the_content' ) );
			add_filter( 'the_content', array( $this, 'paragraph_filter_the_content' ), 9 );
		}

		// Set filters to wp_get_nav_menu_items.
		add_filter( 'wp_get_nav_menu_items', array( $this, 'wp_get_nav_menu_items' ), 10, 2 );

		// Set action function for personal_options_update and edit_user_profile_update.
		add_action( 'personal_options_update', array( $this, 'admin_email_user_profile_update' ) );
		add_action( 'edit_user_profile_update', array( $this, 'admin_email_user_profile_update' ) );

		// Set adction function for admin_menu.
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );

		// Set ajax action function.
		add_action( 'wp_ajax_mop_plugin_options_action', array( $this, 'plugin_options_action' ) );

		// Init Custom post type.
		$this->cpt_init();

		// Set activation function.
		register_activation_hook( MOP_PLUGIN__FILE__, array( $this, 'rewrite_flush' ) );

		// Set filter function.
		add_filter( 'the_content', array( $this, 'student_archive_filter_the_content' ) );

		// Set add_meta_boxes function.
		add_action( 'add_meta_boxes', array( $this, 'student_add_custom_box' ) );

		// Set action function to save_post.
		add_action( 'save_post', array( $this, 'student_save_postdata' ) );

		// Set action function to admin_init.
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_custom_scripts' ) );

		// Set filter function.
		add_filter( 'the_content', array( $this, 'student_filter_the_content' ) );

		// Set filter function for manage_student_posts_columns.
		add_filter( 'manage_student_posts_columns', array( $this, 'manage_student_posts_columns' ) );

		// Set filter function for manage_student_posts_columns.
		add_action( 'manage_student_posts_custom_column', array( $this, 'custom_student_column' ), 10, 2 );

		// Set ajax action function.
		add_action( 'wp_ajax_mop_student_active_action', array( $this, 'student_active_action' ) );

		// Set shortcode function.
		add_shortcode( 'student', array( $this, 'student_query' ) );

		// Set filter function.
		add_filter( 'the_content', array( $this, 'student_sidebar_the_content' ) );
	}

	/**
	 * Content prepend filter function
	 *
	 * @param string $content content value.
	 */
	public function prepend_filter_the_content( $content ) {
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
	public function append_filter_the_content( $content ) {
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
	public function replace_filter_the_content( $content ) {
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
	public function paragraph_filter_the_content( $content ) {
		global $post;
		if ( 'student' === $post->post_type ) {
			$content = '<p>Some paragraph</p>' . $content;
		}
		return $content;
	}

	/**
	 * Navigation menu filter
	 *
	 * @param array  $items An array of menu items.
	 * @param object $menu  The menu object.
	 */
	public function wp_get_nav_menu_items( $items, $menu ) {
		$menu_filter = intval( get_option( 'mop-menu-filter', '0' ) );
		if ( ! is_admin() && is_user_logged_in() && $menu_filter === $menu->term_id ) {
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

	/**
	 * Send email to admin when user profile is updated
	 *
	 * @param int $user_id The user ID.
	 */
	public function admin_email_user_profile_update( $user_id ) {
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

	/**
	 * Set admin option page
	 */
	public function admin_menu() {
		add_options_page( 'My Onboarding', 'My Onboarding Plugin', 'manage_options', 'my-onboarding-admin-menu', array( $this, 'plugin_options' ) );
		add_menu_page( 'My Onboarding', 'My Onboarding', 'manage_options', 'my-onboarding-admin-main-menu', array( $this, 'plugin_options' ) );
	}

	/**
	 * Admin option page output
	 */
	public function plugin_options() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'You do not have sufficient permissions to access this page.' );
		}

		$filters_enabled = get_option( 'mop-filters-enabled', '0' );
		$students_page   = get_option( 'mop-students-page', 0 );
		$menu_filter     = get_option( 'mop-menu-filter', 0 );
		?>
		<div class="wrap">
			<h2>My Onboarding Plugin Options</h2>
			<table class="form-table">
				<tr>
					<th scope="row">Plugin Filters</th>
					<td>
						<div>
							<input type="checkbox" id="mop-filters-enabled" name="mop-filters-enabled" value="1" data-nonce="<?php echo esc_attr( wp_create_nonce( 'mop-filters-enabled' ) ); ?>" <?php checked( '1', $filters_enabled ); ?> />
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
								<option value=""> - None - </option>
		<?php
		$pages = get_pages();
		foreach ( $pages as $page ) {
			?>
								<option value="<?php echo esc_attr( $page->ID ); ?>" <?php selected( $students_page, $page->ID ); ?>><?php echo esc_attr( $page->post_title ); ?></option>
			<?php
		}
		?>
							</select>
						</div>
					</td>
				</tr>
				<tr>
					<th scope="row">Menu filter</th>
					<td>
						<div>
							<label for="mop-menu-filter">Select menu</label>
							<select id="mop-menu-filter" name="mop-menu-filter" data-nonce="<?php echo esc_attr( wp_create_nonce( 'mop-menu-filter' ) ); ?>">
								<option value=""> - None - </option>
		<?php
		$menus = wp_get_nav_menus();
		foreach ( $menus as $menu ) {
			?>
								<option value="<?php echo esc_attr( $menu->term_id ); ?>" <?php selected( $menu_filter, $menu->term_id ); ?>><?php echo esc_attr( $menu->name ); ?></option>
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

		jQuery('#mop-menu-filter').change(function () {
			var data = {
				'action': 'mop_plugin_options_action',
				'nonce': jQuery( this ).data( 'nonce' ),
				'mop-menu-filter': jQuery( this ).val(),
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
	public function plugin_options_action() {
		ob_clean();
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'You do not have sufficient permissions to access this page.' );
		}
		if ( isset( $_POST['mop-filters-enabled'] ) ) {
			$nonce_check = check_ajax_referer( 'mop-filters-enabled', 'nonce', false );
			if ( ! $nonce_check ) {
				wp_die( 'Invalid nonce.' );
			}
			$value = sanitize_text_field( wp_unslash( $_POST['mop-filters-enabled'] ) );
			if ( '1' === $value ) {
				echo 'Filters Enabled.';
			} else {
				$value = '0';
				echo 'Filters Disabled.';
			}
			update_option( 'mop-filters-enabled', $value );
		}
		if ( isset( $_POST['mop-students-page'] ) ) {
			$nonce_check = check_ajax_referer( 'mop-students-page', 'nonce', false );
			if ( ! $nonce_check ) {
				wp_die( 'Invalid nonce.' );
			}
			$value = sanitize_text_field( wp_unslash( $_POST['mop-students-page'] ) );
			update_option( 'mop-students-page', $value );
			echo 'Students page selected.';
		}
		if ( isset( $_POST['mop-menu-filter'] ) ) {
			$nonce_check = check_ajax_referer( 'mop-menu-filter', 'nonce', false );
			if ( ! $nonce_check ) {
				wp_die( 'Invalid nonce.' );
			}
			$value = sanitize_text_field( wp_unslash( $_POST['mop-menu-filter'] ) );
			update_option( 'mop-menu-filter', $value );
			echo 'Menu filter selected.';
		}
		wp_die();
	}

	/**
	 * Init custom post type
	 */
	public function cpt_init() {
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

	/**
	 * Flush rewrite rules on plugin activation for the custom post type.
	 */
	public function rewrite_flush() {
		$this->cpt_init();
		flush_rewrite_rules();
	}


	/**
	 * Content filter function for Student Archive Page
	 *
	 * @param string $content content value.
	 */
	public function student_archive_filter_the_content( $content ) {
		global $post, $mop_recursive_flag;
		$mop_students_page = get_option( 'mop-students-page', 0 );
		if ( intval( $mop_students_page ) !== $post->ID ) {
			return $content;
		}
		if ( $mop_recursive_flag ) {
			return $content;
		}
		$mop_recursive_flag = true;
		ob_start();
		?>
		<?php the_content(); ?>
		<?php
		$args = array(
			'post_type'      => 'student',
			'cat'            => 3,
			'posts_per_page' => 4,
			'paged'          => get_query_var( 'paged' ),
		);

		$query = new \WP_Query( $args );
		if ( $query->have_posts() ) {
			$paginate_links = paginate_links(
				array(
					'base'    => str_replace( PHP_INT_MAX, '%#%', esc_url( get_pagenum_link( PHP_INT_MAX ) ) ),
					'format'  => '?paged=%#%',
					'current' => max( 1, get_query_var( 'paged' ) ),
					'total'   => $query->max_num_pages,
				)
			);
			while ( $query->have_posts() ) {
				$query->the_post();
				?>
				<div class="student-entry">
					<?php if ( has_post_thumbnail() ) : ?>
						<div class="student-featured-image"><?php the_post_thumbnail(); ?></div>
					<?php endif; ?>
					<h1><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h1>
					<p><?php the_excerpt(); ?></p>
				</div>
				<?php
			}
			if ( ! empty( $paginate_links ) ) {
				?>
				<p class="student-archive-pagination">Pages: <?php echo wp_kses_post( $paginate_links ); ?></p>
				<?php
			}
		} else {
			?>
			<p>No students found.</p>
			<?php
		}
		wp_reset_postdata();
		?>
		<?php
		$content = ob_get_clean();

		$mop_recursive_flag = false;
		return $content;
	}

	/**
	 * Add admin custom fields for Quick Event
	 */
	public function student_add_custom_box() {
		add_meta_box(
			'mop_student_box_id', // Unique ID.
			'Student fields', // Box title.
			array( $this, 'student_custom_box_html' ), // Content callback, must be of type callable.
			'student' // Post type.
		);
	}

	/**
	 * Admin custom fields content callback
	 *
	 * @param object $post post object.
	 */
	public function student_custom_box_html( $post ) {
		$country = get_post_meta( $post->ID, 'mop_student_country', true );
		?>
	<div class="inside">
		<label for="mop_student_country_field">Country</label>
		<input type="text" name="mop_student_country_field" id="mop_student_country_field" value="<?php echo esc_attr( $country ); ?>" />
	</div>
		<?php
		$city = get_post_meta( $post->ID, 'mop_student_city', true );
		?>
	<div class="inside">
		<label for="mop_student_city_field">City</label>
		<input type="text" name="mop_student_city_field" id="mop_student_city_field" value="<?php echo esc_attr( $city ); ?>" />
	</div>
		<?php
		$address = get_post_meta( $post->ID, 'mop_student_address', true );
		?>
	<div class="inside">
		<label for="mop_student_address_field">Address</label>
		<input type="text" name="mop_student_address_field" id="mop_student_address_field" value="<?php echo esc_attr( $address ); ?>" />
	</div>
		<?php
		$birthdate = get_post_meta( $post->ID, 'mop_student_birthdate', true );
		?>
	<script type="text/javascript">
	jQuery(document).ready(function($) {
		$('#mop_student_birthdate_field').datepicker({
			dateFormat : 'yy-mm-dd'
		});
	});
	</script>
	<div class="inside">
		<label for="mop_student_birthdate_field">Birthdate</label>
		<input type="text" name="mop_student_birthdate_field" id="mop_student_birthdate_field" value="<?php echo esc_attr( $birthdate ); ?>" />
	</div>
		<?php
		$grade = get_post_meta( $post->ID, 'mop_student_grade', true );
		?>
	<div class="inside">
		<label for="mop_student_grade_field">Class / Grade</label>
		<input type="text" name="mop_student_grade_field" id="mop_student_grade_field" value="<?php echo esc_attr( $grade ); ?>" />
	</div>
		<?php
		wp_nonce_field( 'mop_student_custom_box_action', 'mop_student_custom_box_field' );
	}

	/**
	 * Admin custom content save
	 *
	 * @param int $post_id post ID.
	 */
	public function student_save_postdata( $post_id ) {
		if ( ! isset( $_POST['mop_student_custom_box_field'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['mop_student_custom_box_field'] ) ), 'mop_student_custom_box_action' ) ) {
			return;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		if ( array_key_exists( 'mop_student_country_field', $_POST ) ) {
			$country = sanitize_text_field( wp_unslash( $_POST['mop_student_country_field'] ) );
			update_post_meta(
				$post_id,
				'mop_student_country',
				$country
			);
		}
		if ( array_key_exists( 'mop_student_city_field', $_POST ) ) {
			$city = sanitize_text_field( wp_unslash( $_POST['mop_student_city_field'] ) );
			update_post_meta(
				$post_id,
				'mop_student_city',
				$city
			);
		}
		if ( array_key_exists( 'mop_student_address_field', $_POST ) ) {
			$address = sanitize_text_field( wp_unslash( $_POST['mop_student_address_field'] ) );
			update_post_meta(
				$post_id,
				'mop_student_address',
				$address
			);
		}
		if ( array_key_exists( 'mop_student_birthdate_field', $_POST ) ) {
			$birthdate = sanitize_text_field( wp_unslash( $_POST['mop_student_birthdate_field'] ) );
			update_post_meta(
				$post_id,
				'mop_student_birthdate',
				$birthdate
			);
		}
		if ( array_key_exists( 'mop_student_grade_field', $_POST ) ) {
			$grade = sanitize_text_field( wp_unslash( $_POST['mop_student_grade_field'] ) );
			update_post_meta(
				$post_id,
				'mop_student_grade',
				$grade
			);
		}
	}

	/**
	 * Include DatePicker js/css scripts to administration
	 */
	public function admin_custom_scripts() {
		wp_enqueue_script( 'jquery-ui-datepicker' );
		wp_register_style( 'jquery-ui', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css', false, '1' );
		wp_enqueue_style( 'jquery-ui' );
		wp_enqueue_script( 'mop-script', plugin_dir_url( MOP_PLUGIN__FILE__ ) . 'assets/scripts/scripts.js', array( 'jquery' ), '1.0', true );
	}

	/**
	 * Content filter function for Student Archive Page
	 *
	 * @param string $content content value.
	 */
	public function student_filter_the_content( $content ) {
		global $post, $mop_recursive_flag;
		if ( 'student' !== $post->post_type ) {
			return $content;
		}
		if ( $mop_recursive_flag ) {
			return $content;
		}
		$mop_recursive_flag = true;
		ob_start();
		$country   = get_post_meta( $post->ID, 'mop_student_country', true );
		$city      = get_post_meta( $post->ID, 'mop_student_city', true );
		$address   = get_post_meta( $post->ID, 'mop_student_address', true );
		$birthdate = get_post_meta( $post->ID, 'mop_student_birthdate', true );
		$grade     = get_post_meta( $post->ID, 'mop_student_grade', true );
		?>
		<?php if ( ! empty( $country ) || ! empty( $city ) ) : ?>
		<p>Lives in <?php echo esc_html( $country ) . ( empty( $country ) ? '' : ', ' ) . esc_html( $city ); ?></p>
		<?php endif; ?>
		<?php if ( ! empty( $address ) ) : ?>
		<p>Address: <?php echo esc_html( $address ); ?></p>
		<?php endif; ?>
		<?php if ( ! empty( $birthdate ) ) : ?>
		<p>Birthdate: <?php echo esc_html( gmdate( 'jS \o\f F Y', strtotime( $birthdate ) ) ); ?></p>
		<?php endif; ?>
		<?php if ( ! empty( $grade ) ) : ?>
		<p>Class / Grade: <?php echo esc_html( $grade ); ?></p>
		<?php endif; ?>
		<?php the_content(); ?>
		<?php
		$content = ob_get_clean();

		$mop_recursive_flag = false;
		return $content;
	}

	/**
	 * Admin custom student list column
	 *
	 * @param array $columns columns array.
	 */
	public function manage_student_posts_columns( $columns ) {
		$columns['mop_student_active'] = 'Active';
		return $columns;
	}

	/**
	 * Admin custom student list column
	 *
	 * @param string $column column key.
	 * @param int    $post_id post ID.
	 */
	public function custom_student_column( $column, $post_id ) {
		if ( 'mop_student_active' !== $column ) {
			return;
		}
		$active = get_post_meta( $post_id, 'mop_student_active', true );
		?>
		<input type="checkbox" name="mop_student_active_checkbox" data-post-id="<?php echo esc_attr( $post_id ); ?>"  data-nonce="<?php echo esc_attr( wp_create_nonce( 'mop-students-active-checkbox' ) ); ?>" value="1" <?php checked( $active, '1' ); ?> />
		<?php
	}

	/**
	 * Ajax action function for Student Active Checkbox
	 */
	public function student_active_action() {
		$nonce_check = check_ajax_referer( 'mop-students-active-checkbox', 'nonce', false );
		if ( ! $nonce_check ) {
			wp_die( 'Invalid nonce.' );
		}
		ob_clean();
		if ( isset( $_POST['mop-post-id'] ) && isset( $_POST['mop-student-active'] ) ) {
			$post_id = sanitize_text_field( wp_unslash( $_POST['mop-post-id'] ) );
			$active  = sanitize_text_field( wp_unslash( $_POST['mop-student-active'] ) );
			update_post_meta(
				$post_id,
				'mop_student_active',
				$active
			);
			if ( '1' === $active ) {
				wp_die( 'Student active.' );
			} else {
				wp_die( 'Student not active.' );
			}
		}
		wp_die();
	}

	/**
	 * Shortcode function for student query
	 *
	 * @param array $atts columns array.
	 */
	public function student_query( $atts ) {
		$a = shortcode_atts(
			array(
				'id' => 0,
			),
			$atts
		);
		ob_start();
		?>
		<?php
		$args = array(
			'post_type' => 'student',
			'p'         => $a['id'],
		);

		$query = new \WP_Query( $args );
		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				$grade = get_post_meta( $query->post->ID, 'mop_student_grade', true );
				?>
				<div class="student-entry">
				<?php if ( has_post_thumbnail() ) : ?>
					<div class="student-featured-image"><?php the_post_thumbnail(); ?></div>
				<?php endif; ?>
					<h1><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h1>
				<?php if ( ! empty( $grade ) ) : ?>
					<p>Class / Grade: <?php echo esc_html( $grade ); ?></p>
				<?php endif; ?>
				</div>
				<?php
			}
		} else {
			?>
			<p>Student not found.</p>
			<?php
		}
		wp_reset_postdata();
		?>
		<?php
		$content = ob_get_clean();
		return $content;
	}

	/**
	 * Register sidebar
	 */
	public static function register_sidebars() {
		register_sidebar(
			array(
				'id'            => 'mop_sidebar',
				'name'          => __( 'Student Sidebar' ),
				'description'   => __( 'A short description of the sidebar.' ),
				'before_widget' => '<div id="%1$s" class="widget %2$s">',
				'after_widget'  => '</div>',
				'before_title'  => '<h3 class="widget-title">',
				'after_title'   => '</h3>',
			)
		);
	}

	/**
	 * Content filter function for Student Archive Page
	 *
	 * @param string $content content value.
	 */
	public function student_sidebar_the_content( $content ) {
		global $post, $mop_recursive_flag;
		if ( 'student' !== $post->post_type ) {
			return $content;
		}
		if ( $mop_recursive_flag ) {
			return $content;
		}
		$mop_recursive_flag = true;
		ob_start();
		?>
		<?php the_content(); ?>
		<?php if ( is_active_sidebar( 'mop_sidebar' ) ) : ?>
			<?php dynamic_sidebar( 'mop_sidebar' ); ?>
		<?php else : ?>
			<!-- Time to add some widgets! -->
		<?php endif; ?>
		<?php
		$content = ob_get_clean();

		$mop_recursive_flag = false;
		return $content;
	}
}
