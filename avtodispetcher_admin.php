<?php

add_action('admin_menu', 'distance_calculator_add_page');

function distance_calculator_add_page() {

    add_options_page(__('Distance Calculator Settings','distance-calculator'), __('Distance Calculator Settings','distance-calculator'), 'manage_options', 'distance-calculator-settings', 'distance_calculator_settings_page');

}

function distance_calculator_settings_page() {
    echo "<h2>" . __( 'Distance Calculator Settings', 'distance-calculator' ) . "</h2>";
	distance_calculator_settings_form();

}

function distance_calculator_settings_form() {
	$dc_type = get_option('dc_type');
	if ( isset($_POST['dc_type']) ) {
		$dc_type = $_POST['dc_type'];
		
		if ( $dc_type == 'simple' )
			autodisp_advanced_form_deactivate();
		elseif ( $dc_type == 'advanced' )
			autodisp_advanced_form_activate();
			
		update_option('dc_type', $dc_type);
	}
?>
	<form action="" name="dc_form" method="post">
	<label for="dc_type">
	<select name="dc_type" id="dc_type">
		<option value="simple" <?php echo $dc_type == 'simple' ? 'selected="selected"' : ''; ?>><?php _e("Simple form", 'distance-calculator') ?></option>
		<option value="advanced" <?php echo $dc_type == 'advanced' ? 'selected="selected"' : ''; ?>><?php _e("Advanced form", 'distance-calculator') ?></option>
	</select>
	<input type="submit" name="submit" value="<?php _e("Submit") ?>" />
<?php

}

?>