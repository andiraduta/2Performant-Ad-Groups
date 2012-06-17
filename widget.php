<?php

/* TPAG WIDGET */
class TPAG_Widget extends WP_Widget {
	
	// the widget constructor
    function TPAG_Widget() {
        parent::WP_Widget(false, $name = '2Performant Ad Groups');
		parent::__construct(__CLASS__, '2Performant Ad Groups', array(
			'classname' => __CLASS__,
			'description' => "This widget will show ad groups from 2Performant platform."
		));
    }


	// content of the widget - front-end
    function widget($args, $instance) {
		extract( $args );
		$title     = apply_filters('widget_title', $instance['title']);
		?>
			<?php echo $before_widget; ?>
			<?php if ( $title )
				echo $before_title . $title . $after_title; ?>

			<script src='http://event.2parale.ro/ad_group/<?php echo $instance['group_name']; ?>_<?php echo $instance['affiliate_id']; ?>.js' type='text/javascript'></script>

			<?php echo $after_widget; ?>
		<?php
    }

	
	// get the options
    function update($new_instance, $old_instance) {
		$instance = $old_instance;
		$instance['group_name']     = strip_tags($new_instance['group_name']);
		$instance['affiliate_id']   = strip_tags($new_instance['affiliate_id']);
		return $instance;
    }

	
	// the form that shows in your administration area - here you insert the widget options
    function form($instance) {
		$tp = new TPerformant_Wrapper;
		$tp->connection();

		$ad_groups_list = $tp->api->ad_groups_list();
		$user_details   = $tp->api->user_loggedin();
        $group_name  = esc_attr($instance['group_name']);
		?>
		<p>
			<label for="<?php echo $this->get_field_id('group_name'); ?>">Group name:</label>
			<select id="<?php echo $this->get_field_id('group_name'); ?>" name="<?php echo $this->get_field_name('group_name'); ?>">
				<?php
				if( !empty($ad_groups_list) ) {print_r($ad_groups_list);
					foreach($ad_groups_list->{'ad-group'} as $ad) {
						echo '<option value="'.$ad->{'unique-code'}.'" '.($group_name == $ad->{'unique-code'} ? 'selected="selected"' : '').'>'.$ad->name.'</option>';
					} 
				}
				?>
			</select>
		</p>
		<input type="hidden" name="<?php echo $this->get_field_name('affiliate_id'); ?>" value="<?php echo $user_details->{'unique-code'}; ?>" />
        <?php 
    }
   
}
