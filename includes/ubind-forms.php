<?php
//	using UbindPlugin
//	using UbindDotEnv
//	using UbindOptions

if (!defined('ABSPATH')) exit; // Exit if accessed directly
if (!class_exists('UbindForms')):

class UbindForms extends UbindPlugin {
	var $dotenv_changed = 0;
	var $sections = array();
	var $dotenv = false;
	var $wp_options = false;

	public function __construct($wp_options=false) {
		$this->initialize($wp_options);
	}
	
	private function create_default_form($options) {
		if (!isset($this->sections[$this->default_index]) ) {

			foreach ($options as $index => $val) {
				$not_dotenv_form = trim($val[$this->dotenv_id]) === '';
				$is_default_wp_option = trim($val[$this->option_id]) === '0';
				if ($not_dotenv_form && $is_default_wp_option) {
					$this->sections[$this->default_index] = array_merge($this->dotenv->empty_dotenv() , $val);
					return;
				}
			}

			$this->sections[$this->default_index] = $this->empty_ubind_form($this->default_index);
		}
	}
	
	private function append_non_mapped($options) {
		if ($options) {
			foreach ($options as $index => $item) {

				$no_default_exists = !isset($this->sections[$this->default_index]);
				$not_dotenv_form = trim($item[$this->dotenv_id]) === '';
				$is_default_wp_option = trim($item[$this->option_id]) === '0';

				if ($not_dotenv_form && $is_default_wp_option && $no_default_exists) {
					$this->sections[$this->default_index] = array_merge($this->dotenv->empty_dotenv() , $item);
				}
				else {
					ksort($this->sections);
					if (count($this->sections) == 0) {
						$new_index = 1;
					}
					else {
						end($this->sections);
						$new_index = key($this->sections) + 1;
						reset($this->sections);
					}
					if (trim($item[$this->dotenv_id]) === '') {
						$not_empty = ($item[$this->tenant_id] != '' || $item[$this->product_id] != '') || trim($index) != '0';
						
						if ( $not_empty ) {
							$this->sections[$new_index] = array_merge($this->dotenv->empty_dotenv() , $item);
							$this->sections[$new_index][$this->option_id] = $new_index;
						}
					}
					else {
						$this->dotenv_changed += 1;
					}
				}
			}
			
		}
	}
	
	private function test_section_field($section, $field_name) {
		if ( isset($section[$field_name]) ) {
			return trim($section[$field_name]);
		} else {
			return '';
		}
	}
	
	private function new_section($index, $section) {
		$section[$this->option_id] = $index;
		$section[$this->dotenv_id] = $index;
		
		$section[$this->tenant_id] = $this->test_section_field($section, $this->dotenv_tenant_id);
		if ( $section[$this->tenant_id] == '' && isset($this->sections[$this->default_index]) && $this->sections[$this->default_index][$this->tenant_id] != '' ) {
			$section[$this->tenant_id] = $this->sections[$this->default_index][$this->tenant_id];
		}
		$section[$this->tenant_status] = ($section[$this->tenant_id]?1:0);
		
		$section[$this->config_type] = $this->test_section_field($section, $this->dotenv_config_type);
		if ( $section[$this->config_type] == '' && isset($this->sections[$this->default_index]) && $this->sections[$this->default_index][$this->config_type] != '' ) {
			$section[$this->config_type] = $this->sections[$this->default_index][$this->config_type];
		}
		$section[$this->config_type_status] = ($section[$this->config_type]?1:0);
		
		$section[$this->organisation_id] = $this->test_section_field($section, $this->dotenv_organisation_id);
		if ( $section[$this->organisation_id] == '' && isset($this->sections[$this->default_index]) && $this->sections[$this->default_index][$this->organisation_id] != '' ) {
			$section[$this->organisation_id] = $this->sections[$this->default_index][$this->organisation_id];
		}
		$section[$this->tenant_status] = ($section[$this->organisation_id]?1:0);
		
		$section[$this->product_id] = $this->test_section_field($section, $this->dotenv_product_id);
		if ( $section[$this->product_id] == '' && isset($this->sections[$this->default_index]) && $this->sections[$this->default_index][$this->product_id] != '' ) {
			$section[$this->product_id] = $this->sections[$this->default_index][$this->product_id];
		}
		$section[$this->product_status] = ($section[$this->product_id]?1:0);
		
		$section[$this->portal_id] = $this->test_section_field($section, $this->dotenv_portal_id);
		if ( $section[$this->portal_id] == '' && isset($this->sections[$this->default_index]) && $this->sections[$this->default_index][$this->portal_id] != '' ) {
			$section[$this->portal_id] = $this->sections[$this->default_index][$this->portal_id];
		}
		$section[$this->portal_id_status] = ($section[$this->portal_id]?1:0);
		
		$section[$this->form_type] = $this->test_section_field($section, $this->dotenv_form_type);
		if ( $section[$this->form_type] == '' ) {
			$section[$this->form_type] = $this->default_form_type;
		}
		$section[$this->form_type_status] = ($section[$this->form_type]?1:0);
		
		$section[$this->product_env] = $this->test_section_field($section, $this->dotenv_product_env);
		if ( $section[$this->product_env] == '' ) {
			$section[$this->product_env] = $this->default_environment;
		}
		$section[$this->environment_status] = ($section[$this->product_env]?1:0);
		
		$section[$this->shortcode] = $this->test_section_field($section, $this->dotenv_shortcode);
		if ( $section[$this->shortcode] == '' ) {
			$section[$this->shortcode] = $this->create_shortcode($index, $section);
		}
		$section[$this->shortcode_status] = ($section[$this->shortcode]?1:0);
		
		$section[$this->portal_shortcode] = $this->test_section_field($section, $this->dotenv_portal_shortcode);
		if ( $section[$this->portal_shortcode] == '' ) {
			$section[$this->portal_shortcode] = $this->create_portal_shortcode($index, $section);
		}
		$section[$this->portal_shortcode_status] = ($section[$this->portal_shortcode]?1:0);
		
		$section[$this->portal_fullscreen] = $this->test_section_field($section, $this->dotenv_portal_fullscreen);
		if ( $section[$this->portal_fullscreen] == '' && isset($this->sections[$this->default_index]) && $this->sections[$this->default_index][$this->portal_fullscreen] != '' ) {
			$section[$this->portal_fullscreen] = $this->sections[$this->default_index][$this->portal_fullscreen];
		}
		$section[$this->portal_fullscreen_status] = ($section[$this->portal_fullscreen]?1:0);
		
		$section[$this->sidebar_offset] = $this->test_section_field($section, $this->dotenv_sidebar_offset);
		if ( $section[$this->sidebar_offset] == '' ) {
			$section[$this->sidebar_offset] = $this->default_sidebar_offset;
		}
		$section[$this->sidebar_offset_status] = ($section[$this->sidebar_offset]?1:0);
		return $section;
	}
	
	private function test_env_unchanged($env_index, $wp_option, $dotenv) {
		if ( isset($dotenv[$this->dotenv_tenant_id]) && trim($dotenv[$this->dotenv_tenant_id]) !== '' && trim($wp_option[$this->tenant_id]) !== trim($dotenv[$this->dotenv_tenant_id]) ) {
			$this->sections[$env_index][$this->tenant_id] = $this->sanitize($dotenv[$this->dotenv_tenant_id]);
			$this->dotenv_changed += 1;
		}
		if ( isset($dotenv[$this->dotenv_config_type]) && trim($dotenv[$this->dotenv_config_type]) !== '' && trim($wp_option[$this->config_type]) !== trim($dotenv[$this->dotenv_config_type]) ) {
			$this->sections[$env_index][$this->config_type] = $this->sanitize($dotenv[$this->dotenv_config_type]);
			$this->dotenv_changed += 1;
		}
		if ( isset($dotenv[$this->dotenv_organisation_id]) && trim($dotenv[$this->dotenv_organisation_id]) !== '' && trim($wp_option[$this->organisation_id]) !== trim($dotenv[$this->dotenv_organisation_id]) ) {
			$this->sections[$env_index][$this->organisation_id] = $this->sanitize($dotenv[$this->dotenv_organisation_id]);
			$this->dotenv_changed += 1;
		}
		if ( isset($dotenv[$this->dotenv_product_id]) && trim($dotenv[$this->dotenv_product_id]) !== '' && trim($wp_option[$this->product_id]) !== trim($dotenv[$this->dotenv_product_id]) ) {
			$this->sections[$env_index][$this->product_id] = $this->sanitize($dotenv[$this->dotenv_product_id]);
			$this->dotenv_changed += 1;
		}
		if ( isset($dotenv[$this->dotenv_portal_id]) && trim($dotenv[$this->dotenv_portal_id]) !== '' && trim($wp_option[$this->portal_id]) !== trim($dotenv[$this->dotenv_portal_id]) ) {
			$this->sections[$env_index][$this->portal_id] = $this->sanitize($dotenv[$this->dotenv_portal_id]);
			$this->dotenv_changed += 1;
		}
		if ( isset($dotenv[$this->dotenv_form_type]) && trim($dotenv[$this->dotenv_form_type]) !== '' && trim($wp_option[$this->form_type]) !== trim($dotenv[$this->dotenv_form_type]) ) {
			$this->sections[$env_index][$this->form_type] = $this->sanitize($dotenv[$this->dotenv_form_type]);
			$this->dotenv_changed += 1;
		}
		if ( isset($dotenv[$this->dotenv_product_env]) && trim($dotenv[$this->dotenv_product_env]) !== '' && trim($wp_option[$this->product_env]) !== trim($dotenv[$this->dotenv_product_env]) ) {
			$this->sections[$env_index][$this->product_env] = $this->sanitize($dotenv[$this->dotenv_product_env]);
			$this->dotenv_changed += 1;
		}
		if ( isset($dotenv[$this->dotenv_shortcode]) && trim($dotenv[$this->dotenv_shortcode]) !== '' && trim($wp_option[$this->shortcode]) !== trim($dotenv[$this->dotenv_shortcode]) ) {
			$this->sections[$env_index][$this->shortcode] = $this->sanitize($dotenv[$this->dotenv_shortcode]);
			$this->dotenv_changed += 1;
		}
		if ( isset($dotenv[$this->dotenv_portal_shortcode]) && trim($dotenv[$this->dotenv_portal_shortcode]) !== '' && trim($wp_option[$this->portal_shortcode]) !== trim($dotenv[$this->dotenv_portal_shortcode]) ) {
			$this->sections[$env_index][$this->portal_shortcode] = $this->sanitize($dotenv[$this->dotenv_portal_shortcode]);
			$this->dotenv_changed += 1;
		}
		if ( isset($dotenv[$this->dotenv_portal_fullscreen]) && trim($dotenv[$this->dotenv_portal_fullscreen]) !== '' && trim($wp_option[$this->portal_fullscreen]) !== trim($dotenv[$this->dotenv_portal_fullscreen]) ) {
			$this->sections[$env_index][$this->portal_fullscreen] = $this->sanitize($dotenv[$this->dotenv_portal_fullscreen]);
			$this->dotenv_changed += 1;
		}
		if ( isset($dotenv[$this->dotenv_sidebar_offset]) && trim($dotenv[$this->dotenv_sidebar_offset]) !== '' && trim($wp_option[$this->sidebar_offset]) !== trim($dotenv[$this->dotenv_sidebar_offset]) ) {
			$this->sections[$env_index][$this->sidebar_offset] = $this->sanitize($dotenv[$this->dotenv_sidebar_offset]);
			$this->dotenv_changed += 1;
		}
	}
	
	private function create_shortcode($index, $section, $option_id = 0) {
		if ($index == $this->default_index) {
			return $this->default_shortcode;
		}
		
		$ids = array();
		$ids[] = substr($section[$this->tenant_id] , 0, 4);
		$ids[] = substr($section[$this->organisation_id] , 0, 4);
		$ids[] = substr($section[$this->product_id] , 0, 4);
		$ids[] = substr($section[$this->portal_id] , 0, 4);
		$ids[] = substr($section[$this->form_type] , 0, 4);
		$ids[] = substr($section[$this->product_env] , 0, 4);
		
		if ($option_id > 0) {
			$ids[] = $option_id;
		}

		$suggested_shortcode = implode('_', array_filter($ids));

		$is_unique = true;
		
		foreach ($this->sections as $counter => $val) {
			if (isset($val[$this->shortcode]) && $val[$this->shortcode] == $suggested_shortcode && $counter != $index) {
				$is_unique = false;
				break;
			}
		}
		
		if ($is_unique) {
			return $suggested_shortcode;
		}
		else {
			return $this->create_shortcode($index, $section, $option_id + 1);
		}
	}
	
	private function create_portal_shortcode($index, $section, $option_id = 0) {
		if ($index == $this->default_index) {
			return $this->default_portal_shortcode;
		}
		
		$ids = array();
		$ids[] = 'portal';
		$ids[] = substr($section[$this->tenant_id] , 0, 4);
		$ids[] = substr($section[$this->organisation_id] , 0, 4);
		$ids[] = substr($section[$this->product_id] , 0, 4);
		$ids[] = substr($section[$this->portal_id] , 0, 4);
		$ids[] = substr($section[$this->form_type] , 0, 4);
		$ids[] = substr($section[$this->product_env] , 0, 4);
		
		if ($option_id > 0) {
			$ids[] = $option_id;
		}

		$suggested_shortcode = implode('_', array_filter($ids));

		$is_unique = true;
		
		foreach ($this->sections as $counter => $val) {
			if (isset($val[$this->portal_shortcode]) && $val[$this->portal_shortcode] == $suggested_shortcode && $counter != $index) {
				$is_unique = false;
				break;
			}
		}
		
		if ($is_unique) {
			return $suggested_shortcode;
		} else {
			return $this->create_portal_shortcode($index, $section, $option_id + 1);
		}
	}
	
	private function pair_dotenv($options, $dotenv_form, $env_index) {

		foreach ($options as $key => $wp_form) {

			if (trim($env_index) === trim($wp_form[$this->dotenv_id])) {

				if (is_array($wp_form)) {
					$this->sections[$env_index] = $dotenv_form; //array_merge($dotenv_form, $wp_form);
					foreach ($this->ubind_form_fields() as $field_name ) {
						$this->sections[$env_index][$field_name] = $wp_form[$field_name];
					}
					$this->test_env_unchanged($env_index, $wp_form, $dotenv_form);
					$new_options = $options;
					unset($new_options[$key]);
					return $new_options;
				}
			}
		}

		$this->dotenv_changed += 1;
		$this->sections[$env_index] = $this->new_section($env_index, $dotenv_form);
		return $options;
	}
	
	private function merge_env_options($options) {
		foreach ($this->dotenv->envs as $index => $value) {
			$options = $this->pair_dotenv($options, $value, $index);
		}

		$this->append_non_mapped($options);
		$this->create_default_form($options);
	}

	public function initialize($wp_options=false) {
		$this->dotenv_changed = 0;
		$this->sections = array();
		$this->dotenv = new UbindDotEnv();
		
		if ( $wp_options == false ) {
			$this->wp_options = new UbindOptions();
			$this->merge_env_options($this->wp_options->indexed_options);	
		} else {
			$this->merge_env_options($wp_options);	
		}
		ksort($this->sections);		
	}
}

endif; // class_exists check