<?php
/*
Plugin Name: 2Performant Ad Groups
Plugin URI: http://siteafiliere.com
Description: This plugin allows you to manage your ad groups from 2Performant platform and show the ads in your website.
Author: Alexandru Raduta
Version: 0.1
Author URI: http://siteafiliere.com
*/

$pear_dir = WP_PLUGIN_DIR . '/' . str_replace( basename( __FILE__ ), '', plugin_basename( __FILE__ ) ) . 'api';
set_include_path( get_include_path() . PATH_SEPARATOR . $pear_dir . PATH_SEPARATOR . $pear_dir . '/PEAR' );
include_once 'api/2performant.php';
include_once 'api/2performant_wrapper.php';
include_once 'widget.php';


class TPerformantAdGroups {

	public $tp;

	public function __construct() {
		// add a menu for the settings page
		add_action('admin_menu', array(&$this, 'tpag_settings_menu'));
		// register plugin settings
		add_action('admin_init', array(&$this, 'tpag_register_plugin_settings'));
		// register tpag widget
		add_action('widgets_init', create_function('', 'register_widget("TPAG_Widget");'));
	}
	
	
	// load template file
	public function render($name, $vars = array()) {
		if( isset($vars) && is_array($vars) && !empty($vars) ) {
			extract($vars);
		}
		// require template file
		require 'views/' . $name . '.php';
	}
	
	
	// add a menu
	public function tpag_settings_menu() {
		add_options_page( '2Performant Ad Groups Settings', '2Performant Ad Groups Settings', 'manage_options', '2performant-ad-groups', array(&$this, 'tpag_settings_page') );
	}
	
	
	// settings page
	public function tpag_settings_page() {
		// verify user permissions
		if ( ! current_user_can( 'manage_options' ) )  {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}

		// if the user entered the connections settings, verify if he can connect to the 2Perfomant API url
		$connection = get_option( 'tpag_options_connection' );
		$tp = new TPerformant_Wrapper();
		if( !empty($connection) ) { 
			$data['errors'] = $tp->connection();
		} else {
			$data['errors'] = array();
		}
		
		// render template
		$this->render('settings-page', $data);
	}
	
	
	// register plugin settings
	public function tpag_register_plugin_settings() {
		$settings = $this->tpag_settings_fields_array();
		if( !empty($settings) ) {
			foreach ( $settings as $section_id => $section ) {
				register_setting( 'tpag-options-group', "tpag_options_{$section_id}" );
				add_settings_section( "tpag_options_{$section_id}", $section['label'], array(&$this, 'tpag_options_callback'), '2performant-ad-groups' );
				// get saved settings
				$values = get_option( "tpag_options_{$section_id}" );

				foreach ( $section['settings'] as $setting_id => $setting ) {
					$setting['id'] = "tpag_options_{$section_id}_{$setting_id}";
					$setting['name'] = "tpag_options_{$section_id}[{$setting_id}]";
					$setting['value'] = $values[$setting_id];
					add_settings_field( "tpag_options_{$section_id}_{$setting_id}", $setting['label'], array(&$this, 'tpag_render_settings_fields'), '2performant-ad-groups', "tpag_options_{$section_id}", $setting );	
					register_setting('2performant-ad-groups', "tpag_options_{$section_id}_{$setting_id}");
				}
			}
		}
	}
	
	
	// fields for the settings page
	public function tpag_settings_fields_array($section = false, $setting = false) {
		$settings = array(
			'connection' => array(
				'label' => 'Connection settings for the affiliate network',
				'settings' => array(
					'network' => array(
						'type' => 'text',
						'label' => 'Network API URL',
						'description' => 'E.g. http://api.2parale.ro'
					),
					'username' => array(
						'type' => 'text',
						'label' => 'Username',
						'description' => 'E.g. your username from http://2parale.ro'
					),
					'password' => array(
						'type' => 'password',
						'label' => 'Password',
						'description' => 'E.g. your password from http://2parale.ro'
					)
				)
			)
		);
		
		if ( $section !== false ) 
		if ( isset( $settings[$section] ) ) {
			$settings = $settings[$section];

			if( $setting !== false )
			if ( isset( $settings['settings'][$setting] ) ) {
				$settings = $settings['settings'][$setting];
			} else {
				return false;
			}
		} else {
			return false;
		}
		
		return $settings;
	}
	
	
	// first heading on settings page
	public function tpag_options_callback() {
		//
	}
	
	
	// render settings page fields
	public function tpag_render_settings_fields($setting) {
		extract( $setting );
		$type = empty( $type ) ? 'text' : $type;
		$class = isset( $class ) ? $class : array();
		$class = is_array( $class ) ? $class : array ( $class );

		$output = '';
		switch ( $type ) {
			case 'select':
				if ( isset( $options ) && is_array( $options ) ) {
					foreach ( $options as $key => $option ) {
						$selected = $key == $value ? 'selected="selected"' : '';
						$output .= "<option value='".esc_attr($key)."' {$selected}>" . esc_attr( $option ) . "</option>";
					}
				}
				$class = implode( ' ', $class );
				$output = "<select name='" . esc_attr( $name ) . "' id='" . esc_attr( $id ) . "' class='".esc_attr( $class ) . "'>$output</select>";
				break;
			case 'text':
			case 'number':
			case 'password':
				$class = array_merge( $type == 'number' ? array( 'small-text' ) : array ( 'regular-text' ), $class );
				$class = implode( ' ', $class );
				$output = "<input type='" . esc_attr( $type == 'number' ? 'text' : $type ) . "' class='" . esc_attr( $class ) . "' name='" . esc_attr( $name ) . "' id='" . esc_attr( $id ) . "' value='" . esc_attr( $value ) . "' />";
				break;
			case 'hidden':
				$output = "<input type='hidden' name='" . esc_attr( $name ) . "' id='" . esc_attr( $id ) . "' value='" . esc_attr( $value ) . "' />";
				break;
			case 'textarea':
				$class = array_merge( array ( 'large-text', 'code' ), $class );
				$class = implode( ' ', $class );
				$output = "<textarea rows='10' cols='50' class='" . esc_attr( $class ) . "' name='" . esc_attr( $name ) . "' id='" . esc_attr( $id ) . "'>" . esc_attr( $value ) . "</textarea>";
				break;
			case 'checkbox':
				$class = implode( ' ', $class );
				$output = "<input type='checkbox' class='" . esc_attr( $class ) . "' name='" . esc_attr( $name ) . "' id='" . esc_attr( $id ) . "' ".((boolean)($value) ? "checked='checked'" : "")." />";
				break;
			case 'custom':
				if ( isset( $callback ) ) {
					$output .= call_user_func( $callback, $setting );
				}
				break;
			default:
				trigger_error( 'Invalid setting type', E_WARNING );
		}

		if ( isset( $description ) )
			$output .= " <span class='description'>" . esc_attr( $description ) . "</span>";

		echo $output;
	}
	
}

new TPerformantAdGroups();


