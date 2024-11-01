<?php

//	using UbindPlugin

if( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if( ! class_exists('UbindValidation') ) :

class UbindValidation extends UbindPlugin {
	var $validated = false;
	var $authorized = false;
	var $admin_notice = array();
	var $error_list = array();
	var $forms = array();
	var $options = array();
	var $form_id = false;

	public function __construct($forms) {
		$this->authorized = $this->is_authorized();
		$this->forms = $forms;
		$this->options = $this->forms_to_wp_options();
		
		$this->validate();
	}
	
	private function forms_to_wp_options() {
		$http_post_vars = array();
		foreach ( $this->ubind_form_fields() as $field_name ) {
			$http_post_vars[$field_name] = array();
			foreach ( $this->forms as $index=>$form ) {
				$http_post_vars[$field_name][$index] = $form[$field_name];
			}
		}
		return $http_post_vars;
	}

	private function test_section_update() {
		if ( isset($_POST[$this->option_id]) && !is_array($_POST[$this->option_id]) ) {
			$this->form_id = $this->sanitize($_POST[$this->option_id]);
		}
	}
	
	private function validate() {
		$this->admin_notice = array();
		$this->error_list = array();
		$this->validated = true;
		
		$this->test_section_update();
		
		$this->validate_bitwise();
		$this->validate_string();
		$this->validate_required();
		$this->validate_unique();
	}

	private function validate_bitwise() {
		$errors_found = false;

		foreach ($this->bitwise() as $key) {
			if ( !isset($this->options[$key]) ) continue;

			foreach ( $this->options[$key] as $index=>$item ) {
				
				if ($this->form_id !== false && $this->form_id != $index) continue;
				
				$config_type = $this->options[$this->config_type][$index];
				$config_type_fields_list = $this->configuration_type_fields_list();

				if ( !in_array($key, $config_type_fields_list[$config_type]) ) continue;
				
				$trimmed = trim($item);

				if ( $trimmed == 'true' || $trimmed == '1' ) {
					$trimmed = '1';
				}

				if ( $trimmed == 'false' || $trimmed == '0' || $trimmed == '' ) {
					$trimmed = '0';
				}

				if ( $trimmed != '1' && $trimmed != '0' ) {
					if ( !isset($this->error_list[$key]) ) {
						$this->error_list[$key] = array();
					}
					if ( !isset($this->error_list[$key][$index]) ) {
						$this->error_list[$key][$index] = $index;
					}

					$errors_found = true;
				}
			}
		}

		if ($errors_found) {
			$this->admin_notice[] = '
				<div class="notice notice-error is-dismissible">
					<p>'.$this->general_error.'</p>
				</div>';
			$this->validated = false;
		}
	}

	private function validate_string() {
		$errors_found = array();

		foreach ($this->invalid_string() as $key) {
			if ( !isset($this->options[$key]) ) continue;

			foreach ( $this->options[$key] as $index=>$item ) {
				
				if ($this->form_id !== false && $this->form_id != $index) continue;
				
				$config_type = $this->options[$this->config_type][$index];
				$config_type_fields_list = $this->configuration_type_fields_list();

				if ( !in_array($key, $config_type_fields_list[$config_type]) ) continue;
				
				$new_value = $this->sanitize($item);

				if ( $key == $this->shortcode || $key == $this->portal_shortcode) {
					if ( substr($item, -1, 1) != "]" ) {
						$new_value = $new_value."]";
					}
					
					if ( substr($item, 0, 1) != "[" ) {
						$new_value = "[".$new_value;
					}
				}

				if ( trim($item) != $new_value && trim($item) != '' ) {
					if ( !isset($this->error_list[$key]) ) {
						$this->error_list[$key] = array();
					}
					if ( !isset($this->error_list[$key][$index]) ) {
						$this->error_list[$key][$index] = $index;
					}
					
					if ( trim($index) == '0' ) {
						$errors_found[$index] = '<li>uBind form default values</li>';
					} else {
						$errors_found[$index] = '<li>uBind Form '.$index.'</li>';
					}
				}
			}
		}
		
		if ( count($errors_found) > 0 ){
			$this->admin_notice[] = '
				<div class="notice notice-error is-dismissible">
					<p>'.sprintf($this->invalid_string, '<ul style="list-style: initial; margin-left: 30px;">'.implode("\r\n", $errors_found).'</ul>').'</p>
				</div>';
			$this->validated = false;
		}
	}

	private function validate_required() {
		$errors_found = array();

		foreach ($this->required() as $key) {
			if ( !isset($this->options[$key]) ) continue;
			
			foreach ( $this->options[$key] as $index=>$item ) {
				
				if ($this->form_id !== false && $this->form_id != $index) continue;
				
				$config_type = $this->options[$this->config_type][$index];
				$config_type_fields_list = $this->configuration_type_fields_list();

				if ( !in_array($key, $config_type_fields_list[$config_type]) ) continue;

				if ( $key == $this->shortcode ) {
					if ( isset($this->options[$this->shortcode_status][$index]) && 
							$this->options[$this->shortcode_status][$index] == '1' ) continue;
				}
				if ( $key == $this->portal_shortcode ) {
					if ( isset($this->options[$this->portal_shortcode_status][$index]) && 
							$this->options[$this->portal_shortcode_status][$index] == '1' ) continue;
				}
				if ( trim($item) == '' ) {
					if ( !isset($this->error_list[$key]) ) {
						$this->error_list[$key] = array();
					}
					if ( !isset($this->error_list[$key][$index]) ) {
						$this->error_list[$key][$index] = $index;
					}

					if ( trim($index) == '0' ) {
						$errors_found[$index] = '<li>uBind form default values</li>';
					} else {
						$errors_found[$index] = '<li>uBind Form '.$index.'</li>';
					}
				}
			}

		}
		if ( count($errors_found) > 0 ){
			$this->admin_notice[] = '
				<div class="notice notice-error is-dismissible">
					<p>'.sprintf($this->required, '<ul style="list-style: initial; margin-left: 30px;">'.implode("\r\n", $errors_found).'</ul>').'</p>
				</div>';
			$this->validated = false;
		}
	}
	
	private function validate_unique() {
		$errors_found = array();
		
		foreach ($this->unique() as $key) {
			if ( !isset($this->options[$key]) ) continue;

			$compare_list = $this->options[$key];

			foreach ( $this->options[$key] as $index=>$item ) {
				
				if ($this->form_id !== false && $this->form_id != $index) continue;
				
				$config_type = $this->options[$this->config_type][$index];
				$config_type_fields_list = $this->configuration_type_fields_list();

				if ( !in_array($key, $config_type_fields_list[$config_type]) ) continue;
				
				if ( trim($item) != '' ) {
					foreach ( $compare_list as $compare_index=>$compare_value ) {
						if ( $compare_value == $item && $compare_index != $index ) {

							if ( !isset($this->error_list[$key]) ) {
								$this->error_list[$key] = array();
							}
							if ( !isset($this->error_list[$key][$index]) ) {
								$this->error_list[$key][$index] = $index;
							}
														
							if ( trim($compare_index) == '0' ) {
								$errors_found[$compare_index] = '<li>uBind form default values</li>';
							} else {
								$errors_found[$compare_index] = '<li>uBind Form '.$compare_index.'</li>';
							}
							
							if ( trim($index) == '0' ) {
								$errors_found[$index] = '<li>uBind form default values</li>';
							} else {
								$errors_found[$index] = '<li>uBind Form '.$index.'</li>';
							}

						}
					}
				}
			}
		}

		if ( count($errors_found) > 0 ){
			$this->admin_notice[] = '
				<div class="notice notice-error is-dismissible">
					<p>'.sprintf($this->duplicate_detected, '<ul style="list-style: initial; margin-left: 30px;">'.implode("\r\n", $errors_found).'</ul>').'</p>
				</div>';
			$this->validated = false;
		}
	}
    
	private function is_post_method_authorized() {

		if ( !function_exists('wp_get_current_user') || !function_exists('current_user_can') ) {
			return false;
		}
		
		if ( !current_user_can( $this->user_capability ) || !is_admin() ) {
			return false;
		}
		
		if ( ! isset( $_POST[$this->nonce_field_name] ) ) {
			return false;
		}

		if ( ! isset($_POST[$this->nonce_field_name]) || ! wp_verify_nonce( $_POST[$this->nonce_field_name], $this->plugin_ref ) ) {
			return false;
		}
		
		return true;
	}

	public function is_authorized() {
		
		$authorized = $this->is_post_method_authorized();
		
		if ( ! $authorized ) {
			foreach ($this->ubind_form_fields() as $field_name) {
				if ( isset($_POST[$field_name]) ) {
					$this->admin_notice[] = '
						<div class="notice notice-error is-dismissible">
							<p>Error: Settings not saved.</p>
						</div>';

					return false;
				}
			}
		}
		
		return true;

	}

}

endif; // class_exists check