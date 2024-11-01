<?php
//	using UbindPlugin

if( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if( ! class_exists('UbindDotEnv') ) :

class UbindDotEnv extends UbindPlugin {
    var $envs = array();
    var $failed_index_count = 0;
    var $prefix = "UBIND_";
    
	public function __construct() {
		$this->initialize();
	}
	
    private function validate_data($data) {
        if ($data[$this->dotenv_tenant_id] ||
			$data[$this->dotenv_config_type] ||
			$data[$this->dotenv_organisation_id] ||
            $data[$this->dotenv_product_id] ||
			$data[$this->dotenv_portal_id] ||
			$data[$this->dotenv_form_type] ||
            $data[$this->dotenv_product_env] ||
            $data[$this->dotenv_shortcode] ||
			$data[$this->dotenv_portal_shortcode] ||
			$data[$this->dotenv_portal_fullscreen] ||
			$data[$this->dotenv_sidebar_offset]) {

                return $data;
            } else {
                return false;   
            }
    }
    
    private function get_values($index=0) {
        $suffix = '_'.$index;
        if ($index==0) {
            $suffix = '';
        }
        $data = array();
		$data[$this->dotenv_tenant_id] = getenv($this->prefix . $this->dotenv_tenant_id . $suffix);
		$data[$this->dotenv_config_type] = getenv($this->prefix . $this->dotenv_config_type . $suffix);
		$data[$this->dotenv_organisation_id] = getenv($this->prefix . $this->dotenv_organisation_id . $suffix);
		$data[$this->dotenv_product_id] = getenv($this->prefix . $this->dotenv_product_id . $suffix);
		$data[$this->dotenv_portal_id] = getenv($this->prefix . $this->dotenv_portal_id . $suffix);
		$data[$this->dotenv_form_type] = strtolower( getenv( $this->prefix . $this->dotenv_form_type . $suffix));
        $data[$this->dotenv_product_env] = strtolower( getenv( $this->prefix . $this->dotenv_product_env . $suffix));
        $data[$this->dotenv_shortcode] = getenv($this->prefix . $this->dotenv_shortcode . $suffix);
		$data[$this->dotenv_portal_shortcode] = getenv($this->prefix . $this->dotenv_portal_shortcode . $suffix);
		$data[$this->dotenv_portal_fullscreen] = getenv($this->prefix . $this->dotenv_portal_fullscreen . $suffix);
		$data[$this->dotenv_sidebar_offset] = getenv($this->prefix . $this->dotenv_sidebar_offset . $suffix);

        return $this->validate_data($data);
    }

    public function initialize($index=0) {
        if ( !function_exists('getenv') ) {
            return;
        }

        if ($index==0) {
            $this->failed_index_count = 0;
            $this->envs = array();
        }

        $data = $this->get_values($index);

        if ($data) {
            $this->envs[$index] = $data;
            $this->failed_index_count = 0;
        } else {
            $this->failed_index_count = $this->failed_index_count + 1;
        }

        if ( $this->failed_index_count < $this->failed_index_max ) {
            $this->initialize($index+1);
        }
    }

	public function empty_dotenv() {
		return array(
				$this->dotenv_tenant_id=>'',
				$this->dotenv_config_type=>'',
				$this->dotenv_organisation_id=>'',
				$this->dotenv_product_id=>'',
				$this->dotenv_portal_id=>'',
				$this->dotenv_form_type=>'',
                $this->dotenv_product_env=>'',
                $this->dotenv_shortcode=>'',
				$this->dotenv_portal_shortcode=>'',
				$this->dotenv_portal_fullscreen=>'',
				$this->dotenv_sidebar_offset=>''
				);
    }
}

endif; // class_exists check