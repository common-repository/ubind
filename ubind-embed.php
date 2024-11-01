<?php
/*

Plugin Name: uBind 
Plugin URI: http://www.ubind.io/
Description: Injects uBind forms into pages on your Wordpress website.
Version: 1.8
Author: uBind
License: GPL2

uBind  is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.
 
uBind is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
 
You should have received a copy of the GNU General Public License
along with uBind . If not, see https://www.gnu.org/licenses/old-licenses/gpl-2.0.en.html.
*/

if( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if( !class_exists('UbindEmbed') ) :

include_once( plugin_dir_path( __FILE__ ) . 'includes/ubind-plugin.php' );
include_once( plugin_dir_path( __FILE__ ) . 'includes/ubind-validation.php' );
include_once( plugin_dir_path( __FILE__ ) . 'includes/ubind-options.php' );
include_once( plugin_dir_path( __FILE__ ) . 'includes/ubind-dotenv.php' );
include_once( plugin_dir_path( __FILE__ ) . 'includes/ubind-ajax.php' );
include_once( plugin_dir_path( __FILE__ ) . 'includes/ubind-forms.php' );
include_once( plugin_dir_path( __FILE__ ) . 'includes/ubind-fields.php' );

class UbindEmbed extends UbindPlugin {
	var $plugin_name = 'uBind Settings';
	var $plugin_script = 'ubind_script';
	var $plugin_style = 'ubind_plugin_css';
	
	var $admin_notice = array();
	var $error_list = array();
	var $validated = null;
	var $authorized = false;
	var $wp_options = array();
	var $wp_forms = array();
	var $env_forms = array();

	var $forms = array();
	var $dotenv_updated = 0;
	
	var $ubind_script_inserted = false;
	
	public function __construct() {
		$ubind_forms = new UbindForms();
		$this->forms = $ubind_forms->sections;
		$this->dotenv_updated = $ubind_forms->dotenv_changed;
		$this->wp_forms = $ubind_forms->wp_options->indexed_options;
		$this->env_forms = $ubind_forms->dotenv->envs;
		
		add_action( 'wp_enqueue_scripts', array($this, 'add_ubind_script'), 10);
		add_action( 'plugins_loaded', array($this, 'register_admin') );
	}

	function validate_forms() {
		if ( $this->validated === null ) {
			$validation = new UbindValidation($this->forms);
			$this->admin_notice = $validation->admin_notice;
			$this->error_list = $validation->error_list;
			$this->authorized = $validation->authorized;
			$this->validated = $validation->validated;
			$this->wp_options = $validation->options;
		}
	}
		
	function save() {
		$this->validate_forms();
		if ( ! $this->authorized ) {
			return;
		}
		if ( ! $this->validated && !$this->dotenv_updated ) {
			return;
		}

		foreach ( $this->ubind_form_fields() as $field_name ) {
			update_option( $field_name, $this->wp_options[$field_name] );
		}

		$this->admin_notice[] = '
		<div class="notice notice-success is-dismissible">
			<p>Success: Settings updated.</p>
		</div>';
	}
	
	function update_forms() {
        if ( isset( $_POST[$this->nonce_field_name] ) || $this->dotenv_updated ) {
			if ( isset( $_POST[$this->nonce_field_name] ) ) {
				$ubind_fields = new UbindFields($this->wp_forms, $this->env_forms);
				$this->wp_forms = $ubind_fields->forms;
				$ubind_forms = new UbindForms($this->wp_forms);
				$this->forms = $ubind_forms->sections;
			}
			$this->save();
		}		
	}
	
	function register_admin() {
		$this->update_forms();
		$ubind_ajax = new UbindAjax($this);

		if ( !$ubind_ajax->is_ajax_request ) {
			$this->register_shortcodes();

			if ( current_user_can( $this->user_capability ) && is_admin() ) {
				$this->validate_forms();
				
				add_action( 'admin_init', array($this, 'register_ubind_settings') );
				add_action( 'admin_menu', array($this, 'register_admin_menu') );
				add_action( 'admin_enqueue_scripts', array($this, 'register_plugin_assets') );
				add_action( 'admin_enqueue_scripts', array($ubind_ajax, 'register' ));
			}
		}
	}

	function register_shortcodes() {
		add_shortcode( $this->default_shortcode, array($this, 'ubind_embed'));	// general default shortcode
		add_shortcode( $this->default_portal_shortcode, array($this, 'ubind_portal_embed'));	// general default shortcode

		foreach ($this->forms as $index=>$section) {
			if ( trim($section[$this->shortcode]) !== '' && $section[$this->config_type] == '0' ) {
				add_shortcode( $section[$this->shortcode], array($this, 'ubind_embed'));
			}
			if ( trim($section[$this->portal_shortcode]) !== '' && $section[$this->config_type] == '1' ) {
				add_shortcode( $section[$this->portal_shortcode], array($this, 'ubind_portal_embed'));
			}
		}
	}

	function add_ubind_script(){
		wp_enqueue_style($this->plugin_style, plugin_dir_url( __FILE__ ) . "/assets/css/style.css", array(), filemtime( plugin_dir_path( __FILE__ ) . "/assets/css/style.css" ) );
		wp_register_script($this->plugin_script, $this->ubind_script_url, array(), $this->plugin_version, false);
	}

	function add_ubind_async_script(){
		echo '<script async="true" src="'.$this->ubind_script_url.'" type="text/javascript"></script>';
	}

	function ubind_json_encode($values) {
		if ( !is_array($values) ) {
			return $values;
		}
		$new_values = array();
		foreach ($values as $index=>$item) {
			$new_values[$index] = $this->sanitize($item);
		}

		return wp_json_encode($new_values);
	}

	function register_ubind_settings() {
		foreach ( $this->ubind_form_fields() as $field_name ) {
			register_setting($this->plugin_option_group, $field_name, array($this,'ubind_json_encode'));
		}
	}

	function register_plugin_assets($hook) {
		if ($hook == 'toplevel_page_ubind_settings') {
			wp_enqueue_script( $this->plugin_ref, plugins_url("/assets/js/admin.js",__FILE__), array('jquery', 'jquery-ui-dialog'), time(), true);
		}
	}	

	function ubind_embed($atts, $content = "", $tag) {
		$data = array();
		$script_inserted = false;
		foreach ($this->forms as $index=>$item) {
			
			if ( $tag == $this->forms[$index][$this->shortcode] ) {
					if ( !$this->ubind_script_inserted ) {
						add_action('wp_footer', array($this, 'add_ubind_async_script'));
						$this->ubind_script_inserted = true;
					}
					$data[] = $this->create_ubind_form($index);
			}

		}

		return implode("\r\n", $data);
	}
	
	function ubind_portal_embed($atts, $content = "", $tag) {
		$data = array();
		foreach ($this->forms as $index=>$item) {
						
			if ( $tag == $this->forms[$index][$this->portal_shortcode] ) {
					if ( !$this->ubind_script_inserted ) {
						add_action('wp_footer', array($this, 'add_ubind_async_script'));
						$this->ubind_script_inserted = true;
					}
					$data[] = $this->create_ubind_portal_form($index);
			}
		}

		return implode("\r\n", $data);
	}

	function create_ubind_form($index) {
		$product_id = $this->forms[$index][$this->product_id];
		$environment = $this->forms[$index][$this->product_env];
		$form_type = $this->forms[$index][$this->form_type];
		
		if (is_admin()) {
			return $this->forms[$index][$this->shortcode];
		}

		if ($product_id =='' || $environment =='' || $form_type ==''){
			return '<h2>Required field/s missing please check the settings on dashboard</h2> ';
		}else{
			$ubind_tenant_id = '';
			if ( $this->forms[$index][$this->tenant_id] ) {
				$ubind_tenant_id = ' data-tenant="'.$this->forms[$index][$this->tenant_id].'"';
			}
			
			$ubind_organisation_id = '';
			if ( $this->forms[$index][$this->organisation_id] ) {
				$ubind_organisation_id = ' data-organisation="'.$this->forms[$index][$this->organisation_id].'"';
			}
			
			$ubind_form_type = '';
			if ( $this->forms[$index][$this->form_type] ) {
				$ubind_form_type = ' data-formtype="'.$this->forms[$index][$this->form_type].'"';
			}
		
			$ubind_sidebar_offset = '';
			if ( $this->forms[$index][$this->sidebar_offset] ) {
				$ubind_sidebar_offset = ' data-sidebar-offset="'.$this->forms[$index][$this->sidebar_offset].'"';
			}
			
			return '
					<div class="ubind-product"'.$ubind_tenant_id.$ubind_organisation_id.$ubind_form_type.$ubind_sidebar_offset.' data-product-id="'.$product_id.'" data-environment="'.$environment.'">
			';
		}
	}
	
	function create_ubind_portal_form($index) {
		$product_id = $this->forms[$index][$this->portal_id];
		$environment = $this->forms[$index][$this->product_env];
		$form_type = $this->forms[$index][$this->form_type];

		if (is_admin()) {
			return $this->forms[$index][$this->shortcode];
		}
		
		if ($product_id =='' || $environment ==''){
			return '<h2>Required field/s missing please check the settings on dashboard</h2> ';
		}else{
			$ubind_tenant_id = '';
			if ( $this->forms[$index][$this->tenant_id] ) {
				$ubind_tenant_id = ' data-tenant="'.$this->forms[$index][$this->tenant_id].'"';
			}
			
			$ubind_organisation_id = '';
			if ( $this->forms[$index][$this->organisation_id] ) {
				$ubind_organisation_id = ' data-organisation="'.$this->forms[$index][$this->organisation_id].'"';
			}
		
			$ubind_sidebar_offset = '';
			if ( $this->forms[$index][$this->sidebar_offset] ) {
				$ubind_sidebar_offset = ' data-sidebar-offset="'.$this->forms[$index][$this->sidebar_offset].'"';
			}

			$css_full_screen = '';
			$js_full_screen = '';
			if ( $this->forms[$index][$this->portal_fullscreen] ) {
				$css_full_screen = '
					<style>
						html, body {
							height: 100%;
						}
					</style>
					';
				$js_full_screen = '
						let portal_loaded_'.str_replace("-","_",$product_id).' = setInterval(timer_loaded_'.str_replace("-","_",$product_id).', 300);
						function timer_loaded_'.str_replace("-","_",$product_id).'() {
							if ( $(\'.ubind-portal[data-portal="'.$product_id.'"] div:nth-child(2)\').length ) {
								let css_value = $(\'.ubind-portal[data-portal="'.$product_id.'"] div:nth-child(2)\').css(\'display\');
								if ( css_value == \'none\' ) {
									$(\'.ubind-portal[data-portal="'.$product_id.'"]\').css(\'position\',\'fixed\');
									$(\'.ubind-portal[data-portal="'.$product_id.'"]\').css(\'top\',\'0\');
									$(\'.ubind-portal[data-portal="'.$product_id.'"]\').css(\'left\',\'0\');
									$(\'.ubind-portal[data-portal="'.$product_id.'"]\').css(\'width\',\'100%\');
									$(\'.ubind-portal[data-portal="'.$product_id.'"]\').css(\'height\',\'100%\');
									$(\'.ubind-portal[data-portal="'.$product_id.'"]\').css(\'z-index\',\'99999\');
									clearInterval(portal_loaded_'.str_replace("-","_",$product_id).');
								}
							}
						}
				';
			}

			return '
					<div class="ubind-portal" '.$ubind_tenant_id.$ubind_organisation_id.$ubind_sidebar_offset.' id="embedded-portal" data-portal="'.$product_id.'" data-environment="'.$environment.'">
					<div id="preloader_'.str_replace("-","_",$product_id).'" class="ubind-material-preloader"></div></div>
					'.$css_full_screen.'
					<script>

					jQuery(function($){
						let portal_'.str_replace("-","_",$product_id).' = setInterval(timer_'.str_replace("-","_",$product_id).', 300);					
						function timer_'.str_replace("-","_",$product_id).'() {
							if ( $(\'.ubind-portal[data-portal="'.$product_id.'"] div .material-preloader\').length ) {
								$(\'#preloader_'.str_replace("-","_",$product_id).'\').hide();
								clearInterval(portal_'.str_replace("-","_",$product_id).');
							}
						}
						'.$js_full_screen.'
					});

					</script>
			';
		}
	}

	function hide_on_error($index) {
		$index_in_errors = false;
		foreach ($this->error_list as $val) {
			if (in_array($index, $val)) {
				$index_in_errors = true;
				break;
			}
		}
		if ( $index_in_errors || $index == 0 ) {
			return 'style="display: none;"';
		} else {
			return '';
		}
	}

	function ubind_admin_products_section() {
		foreach ($this->forms as $index=>$item) {
			include( plugin_dir_path( __FILE__ ) . 'views/admin-products-section.php' );
		}
	}

	function display_admin_page(){
		include( plugin_dir_path( __FILE__ ) . 'views/admin-page.php' );
	}

	function register_admin_menu() {
		add_menu_page( $this->plugin_name, $this->plugin_name, $this->user_capability, $this->plugin_ref, array($this,'display_admin_page'), 'dashicons-admin-generic', 50  );
	}

	function form_has_dotenv($index) {
		if ( !isset($this->forms[$index]) ) {
			return false;
		}

		$dotenv = $this->forms[$index];

		if ( !isset($dotenv[$this->dotenv_tenant_id]) &&
				!isset($dotenv[$this->dotenv_config_type]) &&		
				!isset($dotenv[$this->dotenv_product_id]) &&
				!isset($dotenv[$this->dotenv_portal_id]) &&
				!isset($dotenv[$this->dotenv_form_type]) &&
				!isset($dotenv[$this->dotenv_product_env]) &&
				!isset($dotenv[$this->dotenv_shortcode]) &&
				!isset($dotenv[$this->dotenv_portal_shortcode]) &&
				!isset($dotenv[$this->dotenv_sidebar_offset]) ) {
			return false;
		}

		if ( isset($dotenv[$this->dotenv_tenant_id]) && $dotenv[$this->dotenv_tenant_id] != '' ) {
			return true;
		}

		if ( isset($dotenv[$this->dotenv_config_type]) && $dotenv[$this->dotenv_config_type] != '' ) {
			return true;
		}
		
		if ( isset($dotenv[$this->dotenv_product_id]) && $dotenv[$this->dotenv_product_id] != '' ) {
			return true;
		}

		if ( isset($dotenv[$this->dotenv_portal_id]) && $dotenv[$this->dotenv_portal_id] != '' ) {
			return true;
		}
		
		if ( isset($dotenv[$this->dotenv_form_type]) && $dotenv[$this->dotenv_form_type] != '' ) {
			return true;
		}
		
		if ( isset($dotenv[$this->dotenv_product_env]) && $dotenv[$this->dotenv_product_env] != '' ) {
			return true;
		}

		if ( isset($dotenv[$this->dotenv_shortcode]) && $dotenv[$this->dotenv_shortcode] != '' ) {
			return true;
		}

		if ( isset($dotenv[$this->dotenv_portal_shortcode]) && $dotenv[$this->dotenv_portal_shortcode] != '' ) {
			return true;
		}
		
		if ( isset($dotenv[$this->dotenv_sidebar_offset]) && $dotenv[$this->dotenv_sidebar_offset] != '' ) {
			return true;
		}
		
		return false;
	}
	
	function hide_element($index, $display_style='') {
		if ( $this->form_has_dotenv($index) ) {
			echo 'style="display:none;  margin-left: 10px;"';	
		}
		echo $display_style;
	}

	function highlight_error($index, $key='') {
		$index_in_errors = false;
		foreach ($this->error_list as $val) {
			if (in_array($index, $val)) {
				$index_in_errors = true;
				break;
			}
		}

		if ($key=='') {
			if ( $index_in_errors || $index == 0 ) {
				return 'display: block;';
			} else {
				return 'display: none;';
			}
		} else {
			if (isset($this->error_list[$key]) && 
				in_array($index, $this->error_list[$key]) ) {

				return 'style="border: 1px solid red;"';
			}
			return '';
		}
	}

	function field_attributes($index, $dotenv, $wp_options) {
		$attributes = array();
		$attributes[] = 'data-env="'. $this->forms[$index][$dotenv] .'"';
		$attributes[] = $this->highlight_error($index, $wp_options);
		$attributes[] = 'name="'. $wp_options .'['. $index .']"';
		
		echo implode(" ", $attributes);
	}

	function ubind_logo() {
		return plugin_dir_url( __FILE__ ).'assets/img/ubind-logo.jpg';
	}
}

$ubind_obj = new UbindEmbed();

endif; // class_exists check