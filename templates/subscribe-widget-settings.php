<?php
/**
 * @var Prompt_Subscribe_Widget $widget
 * @var array $instance
 */
?>
<p>
	<label for="<?php echo $widget->get_field_id( 'title' ); ?>"
		 title="<?php _e( 'Widget heading, leave blank to omit.', 'Prompt_Core' ); ?>">
		 <?php _e( 'Title:', 'Prompt_Core' ); ?>
		<span class="help-tip">?</span>
		<input class="widefat"
			 id="<?php echo $widget->get_field_id( 'title' ); ?>"
			 name="<?php echo $widget->get_field_name( 'title' ); ?>"
			 type="text"
			 value="<?php echo $widget->get_default_value( $instance, 'title' ); ?>" />
	</label>
	<label for="<?php echo $widget->get_field_id( 'collect_name' ); ?>">
		<input class="widefat"
			 id="<?php echo $widget->get_field_id( 'collect_name' ); ?>"
			 name="<?php echo $widget->get_field_name( 'collect_name' ); ?>"
			 type="checkbox"
			 <?php checked( $widget->get_default_value( $instance, 'collect_name', true ) ); ?>
			 value="true" />
		<?php _e( 'Collect name (in addition to email)', 'Prompt_Core' ); ?>
	</label>
</p>