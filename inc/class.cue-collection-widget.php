<?php

// Disable direct access
if (!defined('ABSPATH')) {
	die();
}

/**
 * "My Cue" widget
 * @package Cue
 */
class My_Cue_Widget extends WP_Widget {

	/**
	 * Sets up the widgets name etc
	 */
	public function __construct() {
		parent::__construct(
			'my_cue_widget', // Base ID
			__('My Cue', 'cue-connect'), // Name
			array('description' => __('Link to Cue Wall and Cue Box', 'cue-connect'))
		);
	}

	/**
	 * Outputs the content of the widget
	 *
	 * @param array $args
	 * @param array $instance
	 */
	public function widget($args, $instance) {
		echo $args['before_widget'];
		if (!empty($instance['title'])) {
			echo $args['before_title'] . apply_filters('widget_title', $instance['title']) . $args['after_title'];
		}
		echo '<a class="cue-stream"></a>';
		echo $args['after_widget'];
	}

	/**
	 * Outputs the options form on admin
	 *
	 * @param array $instance The widget options
	 */
	public function form($instance) {
		$title = !empty($instance['title'])?$instance['title']:null;
		?>
		<p>
		<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', 'cue-connect'); ?></label> 
		<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>">
		</p>
		<?php
	}

	/**
	 * Processing widget options on save
	 *
	 * @param array $new_instance The new options
	 * @param array $old_instance The previous options
	 */
	public function update($new_instance, $old_instance) {
		$instance = array();
		$instance['title'] = (!empty($new_instance['title']))?strip_tags($new_instance['title']):'';

		return $instance;
	}
}

function cue_register_widgets() {
	register_widget('My_Cue_Widget');
}

add_action( 'widgets_init', 'cue_register_widgets' );
