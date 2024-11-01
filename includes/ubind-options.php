<?php
//	using UbindPlugin

if( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if( ! class_exists('UbindOptions') ) :

class UbindOptions extends UbindPlugin {
	var $options = array();
	var $indexed_options = array();

    
	public function __construct() {
		$this->initialize();
	}
	
	private function get_value($key) {
		if ($this->options[$key]) {
			foreach ($this->options[$key] as $index=>$item) {

				if ( !isset($this->indexed_options[$index]) ) {
					$this->indexed_options[$index] = $this->empty_options('', '0');
				}

				$this->indexed_options[$index][$key] = $item;
			}
		}
	}

	private function get_all() {
		foreach ( $this->empty_options() as $key=>$value) {
			$this->get_value($key);
		}
	}
	
	private function ubind_json_decode($option_value) {
		if ( is_array($option_value) ) {
			return $option_value;
		}
		if ( preg_match('/[^,:{}\\[\\]0-9.\\-+Eaeflnr-u \\n\\r\\t]/', preg_replace('/"(\\.|[^"\\\\])*"/', '', $option_value)) ) {
			return array($option_value);
		} else {
			return json_decode($option_value, true);
		}
	}

	public function initialize() {
		$this->options = array();
		$this->indexed_options = array();

		foreach ($this->empty_options() as $key=>$val) {
			$this->options[$key] = $this->ubind_json_decode( get_option($key) );
		}

		$this->get_all();
	}

	public function empty_options($option_id='', $defaults='1') {
		return array(
				$this->option_id=>$option_id,
				$this->dotenv_id=>'',
				$this->tenant_id=>'',
				$this->tenant_status=>$defaults,
				$this->config_type=>'0',
				$this->config_type_status=>$defaults,
				$this->organisation_id=>'',
				$this->organisation_status=>$defaults,
				$this->product_id=>'',
				$this->product_status=>$defaults,
				$this->portal_id=>'',
				$this->portal_id_status=>$defaults,
				$this->form_type=>'',
				$this->form_type_status=>$defaults,
				$this->product_env=>'',
				$this->environment_status=>$defaults,
				$this->shortcode=>'',
				$this->shortcode_status=>$defaults,
				$this->portal_shortcode=>'',
				$this->portal_shortcode_status=>$defaults,
				$this->portal_fullscreen=>'1',
				$this->portal_fullscreen_status=>$defaults,
				$this->sidebar_offset=>'',
				$this->sidebar_offset_status=>$defaults
				);
	}
}

endif; // class_exists check