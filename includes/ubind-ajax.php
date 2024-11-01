<?php

//	using UbindPlugin

if( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if( ! class_exists('UbindAjax') ) :

class UbindAjax extends UbindPlugin {
	var $ajax_ref = 'ubind_ajax';
	var $admin_ajax_url = 'admin-ajax.php';
	var $is_ajax_request = false;

	var $forms = array();
	var $admin_notice = array();
	var $error_list = array();
	var $validated = false;
	var $authorized = false;
	var $dotenv_updated = false;
	
	public function __construct($plugin) {
		if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
			$this->is_ajax_request = true;
			$this->authorized = $plugin->authorized;
			$this->dotenv_updated = $plugin->dotenv_updated;
			$this->validated = $plugin->validated;
			$this->error_list = $plugin->error_list;
			$this->admin_notice = $plugin->admin_notice;
			$this->forms = $plugin->forms;
		}

		add_action('wp_ajax_save_section', array($this,'save_section'));
		add_action('wp_ajax_nopriv_save_section', array($this,'save_section'));
		add_action('wp_ajax_delete_section', array($this,'delete_section'));
		add_action('wp_ajax_nopriv_delete_section', array($this,'delete_section'));	
	}

	public function register($hook) {
		if ($hook == 'toplevel_page_ubind_settings') {
			$protocol = isset( $_SERVER['HTTPS'] ) ? 'https://' : 'http://';
		
			$params = array(
				'ajaxurl' => admin_url($this->admin_ajax_url, $protocol),
				'ajax_nonce' => $this->nonce_field_value(),
			);
		
			wp_localize_script( $this->plugin_ref, $this->ajax_ref, $params );
		}
  }
	
	private function is_authorized() {
		if ( !$this->authorized ) {
			$post_index = $_POST[$this->option_id];
			header('HTTP/1.1 403 Forbidden');
			echo json_encode(array("error"=>$post_index, 'notice'=>array($this->general_error)));
			die();
		}
	}

	public function save_section(){
		$this->is_authorized();

		$post_index = $_POST[$this->option_id];
		if ( !$this->validated ) {
			echo json_encode(array("error"=>$post_index, "data"=>$this->error_list, 'notice'=>$this->admin_notice));
			die();
		}
		
		if ( $this->dotenv_updated ) {
			$this->admin_notice = array();
			$this->admin_notice[] = '
			<div class="notice notice-error is-dismissible">
				<p>'.$this->ajax_dotenv_changed.'</p>
			</div>';
			echo json_encode(array("error"=>$post_index, 'notice'=>$this->admin_notice));
			die();
		}

		echo json_encode(array("success"=>$post_index, "data"=>$this->forms[$post_index]));
		exit;
	}

	public function delete_section(){
		$this->is_authorized();

		$post_index = $_POST[$this->option_id];
		if ( !$this->validated ) {
			echo json_encode(array("error"=>$post_index, "data"=>$this->error_list, 'notice'=>$this->admin_notice));
			die();
		}
		if ( $this->dotenv_updated ) {
			$this->admin_notice = array();
			$this->admin_notice[] = '
			<div class="notice notice-error is-dismissible">
				<p>'.$this->ajax_dotenv_changed.'</p>
			</div>';
			echo json_encode(array("error"=>$post_index, 'notice'=>$this->admin_notice));
			die();
		}
		echo json_encode(array("success"=>$post_index));
		exit;
	}
}

endif; // class_exists check