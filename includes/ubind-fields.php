<?php

//	using UbindPlugin

if( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if( ! class_exists('UbindFields') ) :

class UbindFields extends UbindPlugin {
	var $post_index = false;
	var $forms = array();
	var $env_forms = array();
	var $section = array();
		
	public function __construct($wp_forms, $env_forms) {
		$this->forms = $wp_forms;
		$this->env_forms = $env_forms;
		if (isset($_POST[$this->option_id]) && !is_array($_POST[$this->option_id])) {
			$this->post_index = $this->sanitize($_POST[$this->option_id]);
		}
		$this->initialize();
	}
	
	private function get_post_status_field($index, $status_field) {
		
		if (isset($_POST[$this->option_id])) {
			if ( isset($_POST[$status_field]) ) {
				if (is_array($_POST[$status_field]) && isset($_POST[$status_field][$index])) {
					return (trim($_POST[$status_field][$index]) === '1' ? 1 : 0);
				}
				if ($this->post_index !== false && trim($index) === trim($this->post_index)) {
					return (trim($_POST[$status_field]) === '1' ? 1 : 0);
				}
			}
			if ( $this->post_index === false ) {
				return 0;
			}
		} 
		
		return false;
	}
	
	private function test_default_status($args) {
		$index = $args['index'];
		$wp_options_field = $args['wp_options_field'];
		$dotenv_field = $args['dotenv_field'];
		$status_field = $args['status_field'];
		
		if ( $index == $this->default_index ) {
			return false;
		}
		
		if ( !$this->form_exists($index) ) {
			return false;
		}

		if ($this->field_has_dotenv($index, $dotenv_field)) {
			return true;
		}

		return null;
	}
	
	private function wp_options_field_status($args) {
		$index = $args['index'];
		$status_field = $args['status_field'];
		
		if (!isset($this->forms[$index][$status_field])) {
			return false;
		}

		return trim($this->forms[$index][$status_field]) === '1';
	}
	
	private function has_post_field($args) {
		
		$index = $args['index'];
		$wp_options_field = $args['wp_options_field'];

		$default_status = $this->test_field_status($args);
		
		if (isset($_POST[$wp_options_field]) && !$default_status) {
			if (is_array($_POST[$wp_options_field]) && isset($_POST[$wp_options_field][$index])) {		
				return $_POST[$wp_options_field][$index];
			}
			
			if ($this->post_index !== false && trim($index) === trim($this->post_index)) {
				return $_POST[$wp_options_field];
			}
		}
		return false;
	}
	
	private function has_wp_options($index, $wp_options_field) {
		return isset($this->forms[$index][$wp_options_field]) && trim($this->forms[$index][$wp_options_field]) !== '';
	}
	
	private function has_default_values($dotenv_field, $wp_options_field) {
		if ( !$this->form_exists($this->default_index) ) return false;
		
		$has_default_wp_options = isset($this->forms[$this->default_index][$wp_options_field]) && trim($this->forms[$this->default_index][$wp_options_field]) !== '';
		$has_default_dotenv = isset($this->forms[$this->default_index][$dotenv_field]) && trim($this->forms[$this->default_index][$dotenv_field]) !== '';

		return $has_default_wp_options || $has_default_dotenv;
	}
	
	private function create_shortcode($index, $option_id = 0) {
		if ($index == $this->default_index) {
			return $this->default_shortcode;
		}
		
		$ids = array();
		if ( isset($this->section[$index][$this->tenant_id]) ) {
			$ids[] = substr($this->section[$index][$this->tenant_id] , 0, 4);
		}
		
		if ( isset($this->section[$index][$this->organisation_id]) ) {
			$ids[] = substr($this->section[$index][$this->organisation_id] , 0, 4);
		}
		
		if ( isset($this->section[$index][$this->product_id]) ) {
			$ids[] = substr($this->section[$index][$this->product_id] , 0, 4);
		}
		
		if ( isset($this->section[$index][$this->portal_id]) ) {
			$ids[] = substr($this->section[$index][$this->portal_id] , 0, 4);
		}
		
		if ( isset($this->section[$index][$this->form_type]) ) {
			$ids[] = substr($this->section[$index][$this->form_type] , 0, 4);
		}
		
		if ( isset($this->section[$index][$this->product_env]) ) {
			$ids[] = substr($this->section[$index][$this->product_env] , 0, 4);
		}
		
		if ($option_id > 0) {
			$ids[] = $option_id;
		}

		$suggested_shortcode = implode('_', array_filter($ids));

		$is_unique = true;

		foreach ($this->forms as $counter => $val) {
			if ($val[$this->shortcode] == $suggested_shortcode && $counter != $index) {
				$is_unique = false;
				break;
			}
		}
		
		foreach ($this->section as $counter => $val) {
			if (isset($val[$this->shortcode]) && $val[$this->shortcode] == $suggested_shortcode && $counter != $index) {
				$is_unique = false;
				break;
			}
		}
		
		if ($is_unique) {
			return $suggested_shortcode;
		}
		else {
			return $this->create_shortcode($index, $option_id + 1);
		}
	}
	
	private function create_portal_shortcode($index, $option_id = 0) {
		if ($index == $this->default_index) {
			return $this->default_portal_shortcode;
		}
		
		$ids = array();
		$ids[] = 'portal';
		if ( isset($this->section[$index][$this->tenant_id]) ) {
			$ids[] = substr($this->section[$index][$this->tenant_id] , 0, 4);
		}
		
		if ( isset($this->section[$index][$this->organisation_id]) ) {
			$ids[] = substr($this->section[$index][$this->organisation_id] , 0, 4);
		}
		
		if ( isset($this->section[$index][$this->product_id]) ) {
			$ids[] = substr($this->section[$index][$this->product_id] , 0, 4);
		}
		
		if ( isset($this->section[$index][$this->portal_id]) ) {
			$ids[] = substr($this->section[$index][$this->portal_id] , 0, 4);
		}
		
		if ( isset($this->section[$index][$this->form_type]) ) {
			$ids[] = substr($this->section[$index][$this->form_type] , 0, 4);
		}
		
		if ( isset($this->section[$index][$this->product_env]) ) {
			$ids[] = substr($this->section[$index][$this->product_env] , 0, 4);
		}
		
		if ($option_id > 0) {
			$ids[] = $option_id;
		}

		$suggested_shortcode = implode('_', array_filter($ids));

		$is_unique = true;

		foreach ($this->forms as $counter => $val) {
			if ($val[$this->portal_shortcode] == $suggested_shortcode && $counter != $index) {
				$is_unique = false;
				break;
			}
		}
		
		foreach ($this->section as $counter => $val) {
			if (isset($val[$this->portal_shortcode]) && $val[$this->portal_shortcode] == $suggested_shortcode && $counter != $index) {
				$is_unique = false;
				break;
			}
		}
		
		if ($is_unique) {
			return $suggested_shortcode;
		}
		else {
			return $this->create_portal_shortcode($index, $option_id + 1);
		}
	}
	
	private function test_field_status($args) {
		$index = $args['index'];
		$wp_options_field = $args['wp_options_field'];
		$dotenv_field = $args['dotenv_field'];
		$status_field = $args['status_field'];

		$field_status = $this->test_default_status($args);

		if ( $field_status === null ) {
			$post_field = $this->get_post_status_field($index, $status_field);

			if ($post_field === false && !$this->has_default_values($dotenv_field, $wp_options_field)) {
				return false;
			}

			if ($post_field !== false) {
				return $post_field;
			}
		}
		
		if ( $field_status === null ) {
			$field_status = $this->wp_options_field_status($args);
		}
		
		return $field_status;
	}

	private function field_value_by_status($args) {
		$index = $args['index'];
		$wp_options_field = $args['wp_options_field'];
		$dotenv_field = $args['dotenv_field'];

		if ( $this->test_field_status($args) ) {
			if ($wp_options_field == $this->shortcode) {	
				$status_changed = trim($this->forms[$index][$this->shortcode_status]) != '1';
				
				if ( $status_changed ) {
					return $this->create_shortcode($index);
				}
				
				$not_equal_default = $this->forms[$index][$wp_options_field] != $this->default_shortcode;
				if ($this->has_wp_options($index, $wp_options_field) && $not_equal_default) {
					return $this->forms[$index][$wp_options_field];
				}

				return $this->create_shortcode($index);
			}
			
			if ($wp_options_field == $this->portal_shortcode) {	
				$status_changed = trim($this->forms[$index][$this->portal_shortcode_status]) != '1';
				
				if ( $status_changed ) {
					return $this->create_portal_shortcode($index);
				}
				
				$not_equal_default = $this->forms[$index][$wp_options_field] != $this->default_portal_shortcode;
				if ($this->has_wp_options($index, $wp_options_field) && $not_equal_default) {
					return $this->forms[$index][$wp_options_field];
				}

				return $this->create_portal_shortcode($index);
			}

			if ( $this->has_default_values($dotenv_field, $wp_options_field) ) {
				
				$default_dotenv_exists = $this->field_has_dotenv($this->default_index, $dotenv_field);
				if ($default_dotenv_exists) {
					return $this->forms[$this->default_index][$dotenv_field];
				}
				
				$default_wp_options_exists = $this->has_wp_options($this->default_index, $wp_options_field);
				if ($default_wp_options_exists) {
					return $this->forms[$this->default_index][$wp_options_field];
				}
			}
		}
		
		return false;
	}
	
	private function form_exists($index) {
		return array_key_exists($index, $this->forms);
	}
	
	private function field_has_dotenv($index, $dotenv_field) {
		return isset($this->forms[$index][$dotenv_field]) && trim($this->forms[$index][$dotenv_field]) !== '';
	}
	
	private function wp_options_field_value($args) {
		$index = $args['index'];
		$wp_options_field = $args['wp_options_field'];
		
		$is_shortcode = $wp_options_field == $this->shortcode;
		$is_shortcode_default_field = $is_shortcode && $index == $this->default_index;
		
		$is_portal_shortcode = $wp_options_field == $this->portal_shortcode;
		$is_portal_shortcode_default_field = $is_portal_shortcode && $index == $this->default_index;
		
		if ($is_shortcode_default_field) {
			return $this->default_shortcode;
		}
		
		if ($is_portal_shortcode_default_field) {
			return $this->default_portal_shortcode;
		}

		if ($this->has_wp_options($index, $wp_options_field)) {
			return $this->forms[$index][$wp_options_field];
		}
		
		if ($wp_options_field == $this->product_env) {
			return $this->default_environment;
		}
		
		if ($wp_options_field == $this->form_type) {
			return $this->default_form_type;
		}
		return '';
	}

	private function form_field_value($args) {
		$index = $args['index'];
		$wp_options_field = $args['wp_options_field'];
		$dotenv_field = $args['dotenv_field'];

		if ( !$this->form_exists($index) ) {
			if ($wp_options_field == $this->product_env) {
				return $this->default_environment;
			}
			if ($wp_options_field == $this->form_type) {
				return $this->default_form_type;
			}
			return '';
		}

		if ( $this->field_has_dotenv($index, $dotenv_field) ) {
			return $this->forms[$index][$dotenv_field];
		}

		$field_value = $this->field_value_by_status($args);

		if ( $field_value === false ) {
			$field_value = $this->has_post_field($args);
		}

		if ( $field_value === false ) {
			$field_value = $this->wp_options_field_value($args);
		}

		return $field_value;
	}
		
	private function create_tenant_id($index = 0, $mid_function=null) {
		$args = array(
			"index" => $index,
			"wp_options_field" => $this->tenant_id,
			"dotenv_field" => $this->dotenv_tenant_id,
			"status_field" => $this->tenant_status
		);

		return $this->form_field_value($args);
	}

	private function create_config_type($index = 0, $mid_function=null) {
		$args = array(
			"index" => $index,
			"wp_options_field" => $this->config_type,
			"dotenv_field" => $this->dotenv_config_type,
			"status_field" => $this->config_type_status
		);

		return $this->form_field_value($args);
	}
	
	private function create_organisation_id($index = 0, $mid_function=null) {
		$args = array(
			"index" => $index,
			"wp_options_field" => $this->organisation_id,
			"dotenv_field" => $this->dotenv_organisation_id,
			"status_field" => $this->organisation_status
		);

		return $this->form_field_value($args);
	}
	
	private function create_product_id($index = 0) {
		$args = array(
			"index" => $index,
			"wp_options_field" => $this->product_id,
			"dotenv_field" => $this->dotenv_product_id,
			"status_field" => $this->product_status
		);

		return $this->form_field_value($args);
	}
	
	private function create_portal_id($index = 0) {
		$args = array(
			"index" => $index,
			"wp_options_field" => $this->portal_id,
			"dotenv_field" => $this->dotenv_portal_id,
			"status_field" => $this->portal_id_status
		);

		return $this->form_field_value($args);
	}

	private function create_form_type($index = 0) {
		$args = array(
			"index" => $index,
			"wp_options_field" => $this->form_type,
			"dotenv_field" => $this->dotenv_form_type,
			"status_field" => $this->form_type_status
		);

		return $this->form_field_value($args);
	}
	
	private function create_product_env($index = 0) {
		$args = array(
			"index" => $index,
			"wp_options_field" => $this->product_env,
			"dotenv_field" => $this->dotenv_product_env,
			"status_field" => $this->environment_status
		);

		return $this->form_field_value($args);
	}

	private function create_ubind_shortcode($index = 0) {
		$args = array(
			"index" => $index,
			"wp_options_field" => $this->shortcode,
			"dotenv_field" => $this->dotenv_shortcode,
			"status_field" => $this->shortcode_status
		);
		
		$field_value = $this->form_field_value($args);

		if ( $field_value === '' && trim($index) === '0' ) {
			$field_value = $this->default_shortcode;
		}

		if ( $field_value === '' ) {
			return $field_value;
		}

		preg_match('#\[(.*?)\]#', $field_value, $match);
		if ( isset($match[1]) ) {
			return $match[1];
		} else {
			return $field_value;
		}
	}
	
	private function create_ubind_portal_shortcode($index = 0) {
		$args = array(
			"index" => $index,
			"wp_options_field" => $this->portal_shortcode,
			"dotenv_field" => $this->dotenv_portal_shortcode,
			"status_field" => $this->portal_shortcode_status
		);
		
		$field_value = $this->form_field_value($args);

		if ( $field_value === '' && trim($index) === '0' ) {
			$field_value = $this->default_portal_shortcode;
		}

		if ( $field_value === '' ) {
			return $field_value;
		}

		preg_match('#\[(.*?)\]#', $field_value, $match);
		if ( isset($match[1]) ) {
			return $match[1];
		} else {
			return $field_value;
		}
	}
	
	private function create_portal_fullscreen($index = 0) {
		$args = array(
			"index" => $index,
			"wp_options_field" => $this->portal_fullscreen,
			"dotenv_field" => $this->dotenv_portal_fullscreen,
			"status_field" => $this->portal_fullscreen_status
		);

		return $this->form_field_value($args);
	}
	
	private function create_sidebar_offset($index = 0) {
		$args = array(
			"index" => $index,
			"wp_options_field" => $this->sidebar_offset,
			"dotenv_field" => $this->dotenv_sidebar_offset,
			"status_field" => $this->sidebar_offset_status
		);

		return $this->form_field_value($args);
	}
	
	private function create_tenant_status($index) {
		$args = array(
			"index"=> $index,
			"wp_options_field"=> $this->tenant_id,
			"dotenv_field"=> $this->dotenv_tenant_id,
			"status_field"=>$this->tenant_status
		);
		return $this->test_field_status($args);
	}
	
	private function create_config_type_status($index) {
		$args = array(
			"index"=> $index,
			"wp_options_field"=> $this->config_type,
			"dotenv_field"=> $this->dotenv_config_type,
			"status_field"=>$this->config_type_status
		);
		return $this->test_field_status($args);
	}
	
	private function create_organisation_status($index) {
		$args = array(
			"index"=> $index,
			"wp_options_field"=> $this->organisation_id,
			"dotenv_field"=> $this->dotenv_organisation_id,
			"status_field"=>$this->organisation_status
		);
		return $this->test_field_status($args);
	}
	
	private function create_product_status($index) {
		$args = array(
			"index"=> $index,
			"wp_options_field"=> $this->product_id,
			"dotenv_field"=> $this->dotenv_product_id,
			"status_field"=>$this->product_status
		);
		return $this->test_field_status($args);
	}
	
	private function create_portal_id_status($index) {
		$args = array(
			"index"=> $index,
			"wp_options_field"=> $this->portal_id,
			"dotenv_field"=> $this->dotenv_portal_id,
			"status_field"=>$this->portal_id_status
		);
		return $this->test_field_status($args);
	}
	
	private function create_form_type_status($index) {
		$args = array(
			"index"=> $index,
			"wp_options_field"=> $this->form_type,
			"dotenv_field"=> $this->dotenv_form_type,
			"status_field"=>$this->form_type_status
		);
		return $this->test_field_status($args);
	}
	
	private function create_environment_status($index) {
		$args = array(
			"index"=> $index,
			"wp_options_field"=> $this->product_env,
			"dotenv_field"=> $this->dotenv_product_env,
			"status_field"=>$this->environment_status
		);
		return $this->test_field_status($args);
	}

	private function create_shortcode_status($index) {
		$args = array(
			"index"=> $index,
			"wp_options_field"=> $this->shortcode,
			"dotenv_field"=> $this->dotenv_shortcode,
			"status_field"=>$this->shortcode_status
		);
		return $this->test_field_status($args);
    }
    
	private function create_portal_shortcode_status($index) {
		$args = array(
			"index"=> $index,
			"wp_options_field"=> $this->portal_shortcode,
			"dotenv_field"=> $this->dotenv_portal_shortcode,
			"status_field"=>$this->portal_shortcode_status
		);
		return $this->test_field_status($args);
    }
	
	private function create_portal_fullscreen_status($index) {
		$args = array(
			"index"=> $index,
			"wp_options_field"=> $this->portal_fullscreen,
			"dotenv_field"=> $this->dotenv_portal_fullscreen,
			"status_field"=>$this->portal_fullscreen_status
		);
		return $this->test_field_status($args);
	}
	
	private function create_sidebar_offset_status($index) {
		$args = array(
			"index"=> $index,
			"wp_options_field"=> $this->sidebar_offset,
			"dotenv_field"=> $this->dotenv_sidebar_offset,
			"status_field"=>$this->sidebar_offset_status
		);
		return $this->test_field_status($args);
	}
	
    private function get_range() {
		$post_exists_is_array = isset($_POST[$this->option_id]) && is_array($_POST[$this->option_id]);
        if ( $post_exists_is_array ) {
            return $_POST[$this->option_id];
		}
		
		$post_exists_not_array = isset($_POST[$this->option_id]) && !is_array($_POST[$this->option_id]);
        if ( $post_exists_not_array ) {
			$index = $this->sanitize($_POST[$this->option_id]);
			$new_index_range = $this->forms;
			$new_index_range[$index] = array();
			ksort($new_index_range);
			return $new_index_range;
		}
		
        return $this->forms;
    }
	
	private function get_post_value($index, $field_name) {
		$post_value = '';
		if ( isset($_POST[$field_name]) && !is_array($_POST[$field_name]) ) {
			$post_value = $this->sanitize($_POST[$field_name]);
		}
		if ( isset($_POST[$field_name]) && is_array($_POST[$field_name]) && isset($_POST[$field_name][$index]) ) {
			$post_value = $this->sanitize($_POST[$field_name][$index]);
		}
		return $post_value;
	}
	
	private function test_indexed_form($index) {
		if ( ! $this->form_exists($index) ) {
			$index = $this->sanitize($index);
			$new_form = $this->empty_ubind_form($index);
			$new_form[$this->tenant_id] = $this->get_post_value($index, $this->tenant_id);
			$new_form[$this->config_type] = $this->get_post_value($index, $this->config_type);
			$new_form[$this->organisation_id] = $this->get_post_value($index, $this->organisation_id);
			$new_form[$this->product_id] = $this->get_post_value($index, $this->product_id);
			$new_form[$this->portal_id] = $this->get_post_value($index, $this->portal_id);
			$new_form[$this->form_type] = $this->get_post_value($index, $this->form_type);
			$new_form[$this->product_env] = $this->get_post_value($index, $this->product_env);
			$new_form[$this->portal_fullscreen] = $this->get_post_value($index, $this->portal_fullscreen);
			$new_form[$this->sidebar_offset] = $this->get_post_value($index, $this->sidebar_offset);
			$this->forms[$index] = $new_form;
		}
	}
	
	private function env_value($index, $env_field_name) {
		$dotenv_id = trim($this->forms[$index][$this->dotenv_id]);
		if ( $dotenv_id === '' ) {
			return '';
		}
		
		if ( isset($this->env_forms[$index]) && $dotenv_id == trim($index) ) {
			$this->forms[$index][$env_field_name] = $this->env_forms[$index][$env_field_name];
			return $this->env_forms[$index][$env_field_name];
		}
		
		return '';
	}
	
	public function initialize() {
		$this->section = array();
        $index_range = $this->get_range();

		foreach($index_range as $index=>$item) {
			if ( !isset($_POST['delete_section']) || $this->post_index != $index ) {
				$this->test_indexed_form($index);
				
				$this->section[$index][$this->dotenv_tenant_id] = $this->env_value($index, $this->dotenv_tenant_id);
				$this->section[$index][$this->dotenv_config_type] = $this->env_value($index, $this->dotenv_config_type);
				$this->section[$index][$this->dotenv_organisation_id] = $this->env_value($index, $this->dotenv_organisation_id);
				$this->section[$index][$this->dotenv_product_id] = $this->env_value($index, $this->dotenv_product_id);
				$this->section[$index][$this->dotenv_portal_id] = $this->env_value($index, $this->dotenv_portal_id);
				$this->section[$index][$this->dotenv_form_type] = $this->env_value($index, $this->dotenv_form_type);
				$this->section[$index][$this->dotenv_product_env] = $this->env_value($index, $this->dotenv_product_env);
				$this->section[$index][$this->dotenv_shortcode] = $this->env_value($index, $this->dotenv_shortcode);
				$this->section[$index][$this->dotenv_portal_shortcode] = $this->env_value($index, $this->dotenv_portal_shortcode);
				$this->section[$index][$this->dotenv_portal_fullscreen] = $this->env_value($index, $this->dotenv_portal_fullscreen);
				$this->section[$index][$this->dotenv_sidebar_offset] = $this->env_value($index, $this->dotenv_sidebar_offset);
				
				$this->section[$index][$this->option_id] = $this->forms[$index][$this->option_id];
				$this->section[$index][$this->dotenv_id] = $this->forms[$index][$this->dotenv_id];
				$this->section[$index][$this->tenant_id] = $this->create_tenant_id($index);
				$this->section[$index][$this->tenant_status] = $this->create_tenant_status($index);
				$this->section[$index][$this->config_type] = $this->create_config_type($index);
				$this->section[$index][$this->config_type_status] = $this->create_config_type_status($index);
				$this->section[$index][$this->organisation_id] = $this->create_organisation_id($index);
				$this->section[$index][$this->organisation_status] = $this->create_organisation_status($index);
				$this->section[$index][$this->product_id] = $this->create_product_id($index);
				$this->section[$index][$this->product_status] = $this->create_product_status($index);
				$this->section[$index][$this->portal_id] = $this->create_portal_id($index);
				$this->section[$index][$this->portal_id_status] = $this->create_portal_id_status($index);
				$this->section[$index][$this->form_type] = $this->create_form_type($index);
				$this->section[$index][$this->form_type_status] = $this->create_form_type_status($index);
				$this->section[$index][$this->product_env] = $this->create_product_env($index);
				$this->section[$index][$this->environment_status] = $this->create_environment_status($index);
				$this->section[$index][$this->shortcode] = $this->create_ubind_shortcode($index);
				$this->section[$index][$this->shortcode_status] = $this->create_shortcode_status($index);
				$this->section[$index][$this->portal_shortcode] = $this->create_ubind_portal_shortcode($index);
				$this->section[$index][$this->portal_shortcode_status] = $this->create_portal_shortcode_status($index);
				$this->section[$index][$this->portal_fullscreen] = $this->create_portal_fullscreen($index);
				$this->section[$index][$this->portal_fullscreen_status] = $this->create_portal_fullscreen_status($index);
				$this->section[$index][$this->sidebar_offset] = $this->create_sidebar_offset($index);
				$this->section[$index][$this->sidebar_offset_status] = $this->create_sidebar_offset_status($index);
				
			}
		}

		$this->forms = $this->section;
	}

}

endif; // class_exists check