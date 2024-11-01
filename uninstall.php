<?php

// if uninstall.php is not called by WordPress, die
if (!defined('WP_UNINSTALL_PLUGIN')) {
    die;
}
include_once( plugin_dir_path( __FILE__ ) . '/includes/ubind-plugin.php' );
$ubind_config = new UbindPlugin();
delete_option($ubind_config->option_id);
delete_option($ubind_config->dotenv_id);
delete_option($ubind_config->tenant_id);
delete_option($ubind_config->tenant_status);
delete_option($ubind_config->config_type);
delete_option($ubind_config->config_type_status);
delete_option($ubind_config->organisation_id);
delete_option($ubind_config->organisation_status);
delete_option($ubind_config->product_id);
delete_option($ubind_config->product_status);
delete_option($ubind_config->portal_id);
delete_option($ubind_config->portal_id_status);
delete_option($ubind_config->form_type);
delete_option($ubind_config->form_type_status);
delete_option($ubind_config->product_env);
delete_option($ubind_config->environment_status);
delete_option($ubind_config->shortcode);
delete_option($ubind_config->shortcode_status);
delete_option($ubind_config->portal_shortcode);
delete_option($ubind_config->portal_shortcode_status);
delete_option($ubind_config->sidebar_offset);
delete_option($ubind_config->sidebar_offset_status);

?>