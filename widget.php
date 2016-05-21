<?php
/**
 * Adds Ritmo_Widget widget.
 */
class Ritmo_Widget extends WP_Widget {

	/**
	 * Register widget with WordPress.
	 */
	function __construct() {
		parent::__construct(
			'ritmo_widget',
			__( 'Ritmo', 'ritmo' ),
			array( 'description' => __( 'Ritmo widget', 'ritmo' ), )
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
		echo $args['before_widget'];
		if ( ! empty( $instance['title'] ) ) {
			echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ). $args['after_title'];
		}
		
		if( isset($instance['url']) ) {

			$ritmo_url = rtrim($instance['url'], '/');
			echo '<iframe src="'.$ritmo_url.'/embed" style="border:none" width="300" height="379"></iframe>';
		}

		echo $args['after_widget'];
	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {
		$title = ! empty( $instance['title'] ) ? $instance['title'] : __( 'Ritmo player', 'ritmo' );
		$url = ! empty( $instance['url'] ) ? $instance['url'] : '';
		include dirname( __FILE__ ) . "/templates/ritmo-widget.php";
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
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		$instance['url'] = ( ! empty( $new_instance['url'] ) ) ? $new_instance['url'] : '';

		return $instance;
	}

}

// register widget
function register_ritmo_widget() {
    register_widget( 'Ritmo_Widget' );
}
add_action( 'widgets_init', 'register_ritmo_widget' );