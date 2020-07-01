<?php
if ( !defined( 'ABSPATH' ) ) exit;
add_action( 'widgets_init', 'create_haf_forms' );
function create_haf_forms() {
    register_widget('haf_forms');
}

class haf_forms extends WP_Widget {
 
	function __construct() {
		parent::__construct (
			'haf_forms_widgets',
			'HA - Forms',

			array(
			  'description' => __('Show HA Forms', 'ha-forms')
			)
		);
	}

	function form( $instance ) {
		$form = '';
		if ( isset( $instance[ 'form' ] ) ) {
			$form = $instance[ 'form' ];
		}
		global $wpdb;
		$table = $wpdb->prefix . 'ha_forms';
		$sql = "SELECT SQL_CALC_FOUND_ROWS  * FROM {$table}";
		$results = $wpdb->get_results($sql, ARRAY_A);
		$formOptions = '';
		if($results){		
			foreach($results as $item){
				$selected = $form ? ' selected' : '';
				$formOptions .= '<option value="'. $item['id'] .'"'. $selected .'>'. $item['title'] .'</option>';
			}
		} ?>
		<p>
		<label for="<?php echo $this->get_field_id( 'form' ); ?>"><?php _e( 'Form:' ); ?></label> 
		<select class="form-control widefat" name="<?php echo $this->get_field_name( 'form' ); ?>">
			<option value=""><?php __('Choose a form', 'ha-forms');?></option>
			<?php echo $formOptions; ?>
		</select>
		</p>
		<?php
	}

	function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['form'] = ( ! empty( $new_instance['form'] ) ) ? strip_tags( $new_instance['form'] ) : '';
		return $instance;
	}

	function widget( $args, $instance ) {
		if($instance['form']){
			echo do_shortcode('[haf-form id="'. $instance['form'] .'"]');
		}
	}
}