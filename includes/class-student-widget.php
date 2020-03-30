<?php
/**
 * Students List Widget
 *
 * @package DX/MOP
 */

namespace DX\MOP;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Students List Widget
 */
class Student_Widget extends \WP_Widget {
	/**
	 * Register widget with WordPress.
	 */
	public function __construct() {
		parent::__construct(
			'mop_student_widget', // Base ID.
			'Student Widget', // Name.
			array(
				'description' => 'A Student Widget',
			) // Args.
		);
	}

	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {
		echo wp_kses_post( $args['before_widget'] );
		echo wp_kses_post( $args['before_title'] );
		echo esc_html( $instance['title'] );
		echo wp_kses_post( $args['after_title'] );

		$query_args = array(
			'post_type'      => 'student',
			'posts_per_page' => $instance['post_per_page'],
			'meta_key'       => 'mop_student_active',
			'meta_value'     => $instance['student_status'],
			'paged'          => get_query_var( 'paged' ),
		);

		$transient = 'mop_student_widget_' . $instance['post_per_page'] . '_' . $instance['student_status'] . '_' . get_query_var( 'paged' );
		$query     = get_transient( $transient );
		if ( false === $query ) {
			$query = new \WP_Query( $query_args );
			set_transient( $transient, $query, MINUTE_IN_SECONDS );
		}

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
		echo wp_kses_post( $args['after_widget'] );
	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {
		if ( isset( $instance['title'] ) ) {
			$title = $instance['title'];
		} else {
			$title = 'Students';
		}
		if ( isset( $instance['post_per_page'] ) ) {
			$post_per_page = $instance['post_per_page'];
		} else {
			$post_per_page = '5';
		}
		if ( isset( $instance['student_status'] ) ) {
			$student_status = $instance['student_status'];
		} else {
			$student_status = '1';
		}
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>">Title</label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_name( 'post_per_page' ) ); ?>">Post per page</label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'post_per_page' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'post_per_page' ) ); ?>" type="text" value="<?php echo esc_attr( $post_per_page ); ?>" />
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_name( 'student_status' ) ); ?>">Student status</label>
			<select class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'student_status' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'student_status' ) ); ?>">
				<option value="0" <?php selected( $student_status, '0' ); ?>>Inactive</option>
				<option value="1" <?php selected( $student_status, '1' ); ?>>Active</option>
			</select>
		</p>
		<?php
	}

	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();

		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? wp_strip_all_tags( $new_instance['title'] ) : 'Students';

		$instance['post_per_page'] = ( ! empty( $new_instance['post_per_page'] ) ) ? wp_strip_all_tags( $new_instance['post_per_page'] ) : '5';

		$instance['student_status'] = ( isset( $new_instance['student_status'] ) ) ? wp_strip_all_tags( $new_instance['student_status'] ) : '1';

		return $instance;
	}
}
