<div class="wrap">
    <h1 class="wp-heading-inline" style="width:100%; background-color: #2b2b2b;"><a href="https://www.ubind.io/"><img src="<?php echo $this->ubind_logo(); ?>" alt="uBind"></a></h1>
    <form id="ubind_options" action="" method="post">
        <div>
            <?php 
            foreach ($this->admin_notice as $val) {
                echo $val;
            }
            ?>
        </div>     
        <?php 
            settings_fields( $this->plugin_option_group ); 
            do_settings_sections( $this->plugin_option_group );
        ?>
		<input type="hidden" id="<?php echo $this->nonce_field_name; ?>" name="<?php echo $this->nonce_field_name; ?>" value="<?php echo $this->nonce_field_value(); ?>">
        <table id="ubind_table" class="wp-list-table widefat fixed striped pages" style="table-layout: fixed;">
            <tbody>
                <?php 
					$this->ubind_admin_products_section(); 
				?>
            <tbody>
        </table>
        
		<p>
        <?php submit_button('Save Changes', 'primary', 'submit', false); ?>
		<?php submit_button('Add New', 'secondary', 'add_new', false); ?>
		</p>
    </form>
</div>
<div style="display: none;" id="ubind_confirm_delete">
<p>test</p>
</div>