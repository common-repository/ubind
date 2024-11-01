<?php

if( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if( ! class_exists('UbindPlugin') ) :

class UbindPlugin {
	//	index number of the default uBind form configurations
	var $default_index = 0;
	
    //  plugin reference string
    var $plugin_ref = 'ubind_settings';
	
    //  plugin reference version
    var $plugin_version = '1.7.1';
	
    //	public url for the ubind JS script
    var $ubind_script_url = 'https://app.ubind.com.au/assets/ubind.js';

    //	max number of failed cycles before the plugin stops searching for more dotEnv indexes
    var $failed_index_max = 10;

    //  default shortcode to be used if none is initialized 
    var $default_shortcode = 'ubind_embed';

    //  default portal shortcode to be used if none is initialized 
    var $default_portal_shortcode = 'ubind_portal_embed';
	
    //  default option to be used if none is defined in the dotenv file.
    var $default_portal_fullscreen = '1';
	
    //  default environment to be used if none is defined in the dotenv file.
    var $default_environment = 'staging';
	
    //  default form type to be used if none is defined in the dotenv file.
    var $default_form_type = 'quote';
	
    //  default sidebar_offset to be used if none is defined in the dotenv file.
    var $default_sidebar_offset = '';
	
    //  list of acceptable options/dotenv environment, 
    //  same list that will appear in the plugin admin interface dropdown for environments 
    var $environment_options = array("development","staging","production");

    //  list of acceptable options/dotenv form types, 
    //  same list that will appear in the plugin admin interface dropdown for form types 
    var $form_type_options = array("quote","claim");
	
    //  required user capability to use the plugin admin UI and perform actions such as add/edit/delete
    var $user_capability = 'manage_options';    

    /**
     * wp_option table columns used for saving the uBind form configurations
     */
    var $option_id = 'ubind_option_id';
    var $dotenv_id = 'ubind_dotenv_id';
	var $tenant_id = 'ubind_tenant_id';
	var $tenant_status = 'default_ubind_tenant_id';
	var $config_type = 'ubind_row_type';
	var $config_type_status = 'default_ubind_row_type';
	var $organisation_id = 'ubind_organisation_id';
	var $organisation_status = 'default_ubind_organisation_id';
    var $product_id = 'ubind_product_id';
    var $product_status = 'default_ubind_product_id';
    var $portal_id = 'ubind_portal_id';
    var $portal_id_status = 'default_ubind_portal_id';
	var $form_type = 'ubind_form_type';
	var $form_type_status = 'default_ubind_form_type';
	var $product_env = 'ubind_product_env';
	var $environment_status = 'default_ubind_product_env';
	var $shortcode = 'ubind_shortcode';
    var $shortcode_status = 'default_ubind_shortcode';
	var $portal_shortcode = 'ubind_portal_shortcode';
    var $portal_shortcode_status = 'default_ubind_portal_shortcode';
	var $portal_fullscreen = 'ubind_portal_fullscreen';
	var $portal_fullscreen_status = 'default_ubind_portal_fullscreen';
	var $sidebar_offset = 'ubind_sidebar_offset';
	var $sidebar_offset_status = 'default_ubind_sidebar_offset';
	
    /**
     * dotenv variables: when used in searching .env configs, these will be prefixed with "UBIND_" and suffixed with an incremental number e.g. "_1", "_2"
     */
	var $dotenv_tenant_id = 'TENANT';
	var $dotenv_config_type = 'CONFIG_TYPE';
	var $dotenv_organisation_id = 'ORGANISATION';
    var $dotenv_product_id = 'PRODUCT';
	var $dotenv_portal_id = 'PORTAL';
	var $dotenv_portal_fullscreen = 'PORTAL_FULLSCREEN';
	var $dotenv_form_type = 'FORM_TYPE';
	var $dotenv_product_env = 'ENVIRONMENT';
	var $dotenv_shortcode = 'SHORTCODE';
	var $dotenv_portal_shortcode = 'PORTAL_SHORTCODE';
	var $dotenv_sidebar_offset = 'SIDEBAR_OFFSET';
	
    /**
     *  wp_option group name
     */
    var $plugin_option_group = 'ubind-plugin-settings-group';

    /**
     * ajax nonce slug
     */
    var $nonce_slug = 'ubind_ajax_check';

	/**
	 *  returns a list of input variables from the Admin UI
	 */
	public function ubind_form_fields() {
		return array($this->option_id, 
				$this->dotenv_id, 
				$this->tenant_id, 
				$this->tenant_status,
				$this->config_type, 
				$this->config_type_status,
				$this->organisation_id, 
				$this->organisation_status, 
				$this->product_id, 
				$this->product_status, 
				$this->portal_id, 
				$this->portal_id_status, 
				$this->form_type, 
				$this->form_type_status, 
				$this->product_env, 
				$this->environment_status, 
				$this->shortcode, 
				$this->shortcode_status,
				$this->portal_shortcode, 
				$this->portal_shortcode_status,
				$this->portal_fullscreen, 
				$this->portal_fullscreen_status,
				$this->sidebar_offset, 
				$this->sidebar_offset_status
				);
	}
	
	/**
	 *  returns an an empty uBind form configuration
	 */
	public function empty_ubind_form($index=0) {
		return array(
				$this->dotenv_tenant_id => '',
				$this->dotenv_organisation_id => '',
				$this->dotenv_product_id => '',
				$this->dotenv_portal_id => '',
				$this->dotenv_form_type => '',
				$this->dotenv_product_env => '',
				$this->dotenv_shortcode => '',
				$this->dotenv_portal_shortcode => '',
				$this->dotenv_sidebar_offset => '',
				$this->option_id => $index, 
				$this->dotenv_id => '',
				$this->tenant_id => '',
				$this->tenant_status => '1',
				$this->config_type => '',
				$this->config_type_status => '',
				$this->organisation_id => '',
				$this->organisation_status => '',
				$this->product_id => '',
				$this->product_status => '1',
				$this->portal_id => '',
				$this->portal_id_status => '1',
				$this->form_type => '',
				$this->form_type_status => '1',
				$this->product_env => '',
				$this->environment_status => '1',
				$this->shortcode => (trim($index)=='0'?$this->default_shortcode:''),
				$this->shortcode_status => '1',
				$this->portal_shortcode => (trim($index)=='0'?$this->default_portal_shortcode:''),
				$this->portal_shortcode_status => '1',
				$this->sidebar_offset => '',
				$this->sidebar_offset_status => '1',
				);
	}
	
	/**
     *  return list of fields present in each form type (product/portal)
     */
	public function configuration_type_fields_list() {
		return array(
					"0"=>array($this->tenant_id, $this->organisation_id, $this->product_id, $this->form_type, $this->product_env, $this->shortcode, $this->sidebar_offset),
					"1"=>array($this->tenant_id, $this->organisation_id, $this->product_env, $this->portal_id, $this->portal_shortcode, $this->portal_fullscreen)
					);
	}
    /**
     *  return list of required variables used for validation
     */
    public function required() {
        return  array($this->tenant_id, $this->product_id, $this->portal_id, $this->product_env, $this->shortcode);
    }
	
    /**
     *  return list of unique variables used for validation
     */
    public function unique() {
        return  array($this->shortcode, $this->portal_shortcode);
    }

    /**
     * return list of variables that must be sanitized, used for validation
     */
    public function invalid_string() {
        return  array($this->tenant_id, $this->product_id, $this->portal_id, $this->form_type, $this->product_env, $this->sidebar_offset);
    }

    /**
     * return list of variables that may only contain a value of 1, used for validation
     */
    public function bitwise() {
        return  array($this->config_type, $this->tenant_status, $this->product_status, $this->environment_status, $this->shortcode_status, $this->portal_fullscreen);
    }

    /**
     * Error/Update messages
     */
	var $dotenv_changed = 'ERROR: dotEnv file has been recently updated, your changes in the Admin were not saved.';
	var $ajax_dotenv_changed = 'ERROR: dotEnv file has been recently updated, your changes in the Admin were not saved. Please reload page.';
    var $invalid_string = 'ERROR: Invalid characters found in entries for the following forms : %sEntries may only have alphabets, numbers, underscores, dashes and spaces. While shortcodes can have square brackets.<br/> Field value/s have been sanitized, confirm change then click "Save Changes" button.';
    var $general_error = 'ERROR: Invalid data found.';
    var $required = 'ERROR: Required fields are missing on the following forms: %sPlease enter a value for each of the blank fields then click "Save Changes" button.';
	var $duplicate_detected = 'ERROR: Duplicate shortcodes found on the following forms: %sPlease update each Form to have a different shortcode then click "Save Changes" button.';
    /**
     * Other Messages
     */
    var $loaded_from_dotenv = '<i>configuration for ubind form is loaded from .env file</i>';
	/*
	 *	WP nonce
	 */
	public function nonce_field_value() {
		return wp_create_nonce($this->plugin_ref);
	}
	
    var $nonce_field_name = 'ubind_settings_nonce';
    
    /*
     *  Sanitation method
     */
	public function sanitize($data) {
		$data = strip_tags(trim($data));
		//$retVal = preg_replace('/[^\' \w-]/', '', $data);
		//echo $data.'<br/>';
		return $data;
	}

}

endif; // class_exists check