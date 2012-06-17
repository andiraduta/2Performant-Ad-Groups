
	<div class="wrap">

		<h2>2Performant Ad Groups Settings</h2>
		
		<?php
		if( !empty($errors) ) {
		?>
		<div id="setting-error-options_error" class="error settings-error">
		<?php
		foreach( $errors as $error ) {
		?>
			<p><?php echo $error; ?></p>
		<?php } ?>
		</div>
		<?php } ?>
		
		<form action="options.php" method="post">
		
			<?php settings_fields('tpag-options-group'); ?>
			<?php do_settings_sections('2performant-ad-groups'); ?>

			<p class="submit">
			<input name="Submit" type="submit" class="button-primary" value="<?php esc_attr_e('Save Changes'); ?>" />
			</p>
		
		</form>
	
	</div>