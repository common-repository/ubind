<tr id="ubind_row_<?php echo esc_attr($index); ?>" class="iedit author-self type-page hentry entry">
    <td id="ubind_title_<?php echo esc_attr($index); ?>" class="title column-title has-row-actions column-primary page-title" data-id="<?php echo esc_attr($index); ?>">
        <div id="ubind_header_<?php echo esc_attr($index); ?>" <?php echo $this->hide_on_error($index); ?>>
            <strong>
                <a class="row-title" href="javascript:;" data-index="<?php echo esc_attr($index); ?>">
                    <?php if ($index) { 
                        echo 'uBind Form '. $index.' ('.($this->forms[$index][$this->config_type]?'Portal':'Product').')';
                    } else {
                        echo 'uBind form default values ';
                    }
                    ?>
                </a>
            </strong>
            <?php 
            if ( trim($this->forms[$index][$this->dotenv_id]) != '' ) {
                echo $this->loaded_from_dotenv;
            }
            ?>
            <div class="row-actions">
                <span class="inline hide-if-no-js"><button type="button" class="button-link editinline" data-index="<?php echo esc_attr($index); ?>" inline" aria-expanded="false">Quick&nbsp;Edit</button> | </span>
                <span <?php $this->hide_element($index, 'style="display:inline-block;"'); ?>class="trash"><a href="javascript:;" class="submitdelete" data-index="<?php echo esc_attr($index); ?>">Delete</a> | </span>
            </div>
        </div>
		
        <div id="ubind_form_<?php echo esc_attr($index); ?>" class="ubind-form" style="<?php echo $this->highlight_error($index, ''); ?> border: 1px solid #e1e1e1; background:#f1f1f1; margin:10px; padding:10px;">
            <h2>
            <?php if ($index) { 
                echo 'uBind Form '. $index.' ('.($this->forms[$index][$this->config_type]?'Portal':'Product').')';
            } else {
                echo 'uBind form default values ';
            }
            ?>
            </h2>
            <?php 
            if ( trim($this->forms[$index][$this->dotenv_id]) != '' ) {
                echo $this->loaded_from_dotenv;
            }
            ?>

            <table class="form-table wp-list-table" data-index="<?php echo $index; ?>" data-env="<?php echo (trim($this->forms[$index][$this->dotenv_id]) != ''?'1':'0'); ?>">
                <tbody>
                    <tr valign="top" data-row-type="0|1">
                        <th scope="row">&nbsp;</th>
                        <td>
							<label><input class="ubind_config_type" data-index="<?php echo $index; ?>" type="radio" name="<?php echo $this->config_type; ?>[<?php echo $index; ?>]" value="0" <?php echo ($this->forms[$index][$this->config_type]?'':'checked="checked"'); ?>> Product Form</label>
							<label><input class="ubind_config_type" data-index="<?php echo $index; ?>" type="radio" name="<?php echo $this->config_type; ?>[<?php echo $index; ?>]" value="1" <?php echo ($this->forms[$index][$this->config_type]?'checked="checked"':''); ?>> Portal</label>
                        </td>
                    </tr>
                    <tr valign="top" data-row-type="0|1">
                        <th scope="row">uBind Tenant<span class="description">(required)</span></th>
                        <td>
							<input <?php $this->field_attributes($index, $this->dotenv_tenant_id, $this->tenant_id); ?> value="<?php echo esc_attr( $this->forms[$index][$this->tenant_id] ); ?>" type="text" class="regular-text" />
                            <label data-index="<?php echo $index; ?>">
								<?php
									$is_checked = ($this->forms[$index][$this->tenant_status]?'checked="checked"':'');
								?>
                                <input type="checkbox" name="<?php echo $this->tenant_status; ?>[<?php echo $index; ?>]" value="1" <?php echo $is_checked; ?>> Use Default</label>
                            <input type="hidden" name="<?php echo $this->option_id; ?>[<?php echo $index; ?>]" value="<?php echo esc_attr( $this->forms[$index][$this->option_id] ); ?>" />
                            <input type="hidden" name="<?php echo $this->dotenv_id; ?>[<?php echo $index; ?>]" value="<?php echo esc_attr( $this->forms[$index][$this->dotenv_id] ); ?>" />
                        </td>
                    </tr>
                    <tr valign="top" data-row-type="0|1">
                        <th scope="row">uBind Organisation</th>
                        <td>
							<input <?php $this->field_attributes($index, $this->dotenv_organisation_id, $this->organisation_id); ?> value="<?php echo esc_attr( $this->forms[$index][$this->organisation_id] ); ?>" type="text" class="regular-text" />
                            <label data-index="<?php echo $index; ?>">
								<?php
									$is_checked = ($this->forms[$index][$this->organisation_status]?'checked="checked"':'');
								?>
                                <input type="checkbox" name="<?php echo $this->organisation_status; ?>[<?php echo $index; ?>]" value="1" <?php echo $is_checked; ?> > Use Default</label>
                        </td>
                    </tr>
                    <tr valign="top" data-row-type="0">
                        <th scope="row">uBind Product<span class="description">(required)</span></th>
                        <td>
							<input <?php $this->field_attributes($index, $this->dotenv_product_id, $this->product_id); ?> value="<?php echo esc_attr( $this->forms[$index][$this->product_id] ); ?>" type="text" class="regular-text" />
                            <label data-index="<?php echo $index; ?>">
								<?php
									$is_checked = ($this->forms[$index][$this->product_status]?'checked="checked"':'');
								?>
                                <input type="checkbox" name="<?php echo $this->product_status; ?>[<?php echo $index; ?>]" value="1" <?php echo $is_checked; ?> > Use Default</label>
                        </td>
                    </tr>
                    <tr valign="top" data-row-type="0">
                        <th scope="row">Form Type <span class="description">(required)</span></th>
                        <td>
                            <select <?php $this->field_attributes($index, $this->dotenv_form_type, $this->form_type); ?>  class="regular-text"> 
                                <?php
                                    foreach ( $this->form_type_options as $item ) {
                                        $option = '<option value="' . $item . '" ';
                                        $option .= ( $this->forms[$index][$this->form_type] == $item ) ? 'selected="selected"' : '';
                                        $option .= '>';
                                        $option .= $item;
                                        $option .= '</option>';
                                        echo $option;
                                    }
                                ?>
                            </select>
                            <label data-index="<?php echo $index; ?>">
								<?php
									$is_checked = ($this->forms[$index][$this->form_type_status]?'checked="checked"':'');
								?>
                                <input type="checkbox" name="<?php echo $this->form_type_status; ?>[<?php echo $index; ?>]" value="1" <?php echo $is_checked; ?>> Use Default</label>
                        </td>
                    </tr>
                    <tr valign="top" data-row-type="0|1">
                        <th scope="row">uBind Environment <span class="description">(required)</span></th>
                        <td>
                            <select <?php $this->field_attributes($index, $this->dotenv_product_env, $this->product_env); ?>  class="regular-text"> 
                                <?php
                                    foreach ( $this->environment_options as $item ) {
                                        $option = '<option value="' . $item . '" ';
                                        $option .= ( $this->forms[$index][$this->product_env] == $item ) ? 'selected="selected"' : '';
                                        $option .= '>';
                                        $option .= $item;
                                        $option .= '</option>';
                                        echo $option;
                                    }
                                ?>
                            </select>
                            <label data-index="<?php echo $index; ?>">
								<?php
									$is_checked = ($this->forms[$index][$this->environment_status]?'checked="checked"':'');
								?>
                                <input type="checkbox" name="<?php echo $this->environment_status; ?>[<?php echo $index; ?>]" value="1" <?php echo $is_checked; ?>> Use Default</label>
                        </td>
                    </tr>
                    <tr valign="top" data-row-type="0">
                        <th scope="row">Product Shortcode <span class="description">(required)</span></th>
                        <td>
							<input <?php $this->field_attributes($index, $this->dotenv_shortcode, $this->shortcode); ?> value="<?php echo esc_attr( $this->forms[$index][$this->shortcode] ); ?>" type="text" class="regular-text shortcode" />
                            <label data-index="<?php echo $index; ?>">
                                <?php
									$is_checked = ($this->forms[$index][$this->shortcode_status]?'checked="checked"':'');
								?>
                                <input type="checkbox" name="<?php echo $this->shortcode_status; ?>[<?php echo $index; ?>]" value="1" <?php echo $is_checked; ?>> Use Default</label>
                        </td>
                    </tr>
                    <tr valign="top" data-row-type="1">
                        <th scope="row">uBind Portal <span class="description">(required)</span></th>
                        <td>
							<input <?php $this->field_attributes($index, $this->dotenv_portal_id, $this->portal_id); ?> value="<?php echo esc_attr( $this->forms[$index][$this->portal_id] ); ?>" type="text" class="regular-text" />
                            <label data-index="<?php echo $index; ?>">
								<?php
									$is_checked = ($this->forms[$index][$this->portal_id_status]?'checked="checked"':'');
								?>
                                <input type="checkbox" name="<?php echo $this->portal_id_status; ?>[<?php echo $index; ?>]" value="1" <?php echo $is_checked; ?> > Use Default</label>
                        </td>
                    </tr>
                    <tr valign="top" data-row-type="1">
                        <th scope="row">Portal Shortcode <span class="description">(required)</span></th>
                        <td>
							<input <?php $this->field_attributes($index, $this->dotenv_portal_shortcode, $this->portal_shortcode); ?> value="<?php echo esc_attr( $this->forms[$index][$this->portal_shortcode] ); ?>" type="text" class="regular-text shortcode" />
                            <label data-index="<?php echo $index; ?>">
                                <?php
									$is_checked = ($this->forms[$index][$this->portal_shortcode_status]?'checked="checked"':'');
								?>
                                <input type="checkbox" name="<?php echo $this->portal_shortcode_status; ?>[<?php echo $index; ?>]" value="1" <?php echo $is_checked; ?>> Use Default</label>
                        </td>
                    </tr>
                    <tr valign="top" data-row-type="1">
                        <th scope="row">Full Screen Mode</th>
                        <td>
							<label><input class="ubind_full_screen" data-index="<?php echo $index; ?>" type="radio" name="<?php echo $this->portal_fullscreen; ?>[<?php echo $index; ?>]" value="1" <?php echo ($this->forms[$index][$this->portal_fullscreen]?'checked="checked"':''); ?>> Yes</label>
							<label><input class="ubind_full_screen" data-index="<?php echo $index; ?>" type="radio" name="<?php echo $this->portal_fullscreen; ?>[<?php echo $index; ?>]" value="0" <?php echo ($this->forms[$index][$this->portal_fullscreen]?'':'checked="checked"'); ?>> No</label>
                        </td>
                    </tr>
                    <tr valign="top" data-row-type="0">
                        <th scope="row">Sidebar Offset</th>
                        <td>
							<input <?php $this->field_attributes($index, $this->dotenv_sidebar_offset, $this->sidebar_offset); ?> value="<?php echo esc_attr( $this->forms[$index][$this->sidebar_offset] ); ?>" type="text" class="regular-text sidebar_offset" />
                            <label data-index="<?php echo $index; ?>">
                                <?php
									$is_checked = ($this->forms[$index][$this->sidebar_offset_status]?'checked="checked"':'');
								?>
                                <input type="checkbox" name="<?php echo $this->sidebar_offset_status; ?>[<?php echo $index; ?>]" value="1" <?php echo $is_checked; ?>> Use Default</label>
                        </td>
                    </tr>
                </tbody>
            </table>

            <div class="submit inline-edit-save" style="max-width:720px;">
			    <button data-index="<?php echo esc_attr($index); ?>" type="button" class="button cancel alignleft ubind-cancel">Cancel</button>&nbsp;
                <button data-index="<?php echo esc_attr($index); ?>" <?php $this->hide_element($index, 'style="display:block;  margin-left: 10px;'); ?> type="button" style="margin-left: 300px !important;" class="button delete alignleft ubind-delete">Delete</button>
                <span class="spinner"></span>
                <button type="button" class="button button-primary save alignright" disabled="disabled">Save Changes</button>
			</div>
            <div class="ubind-notice notice-alt inline hidden">

			</div>
		</div>
    </td>
</tr>