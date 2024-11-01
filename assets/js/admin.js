jQuery(function($){
	var ubind_form = '#ubind_options';

	function create_clipboard(index) {
		$('input[name="ubind_tenant_id['+index+']"]').attr('data-old', $('input[name="ubind_tenant_id['+index+']"]').val());
		$('input[name="ubind_config_type['+index+']"]').attr('data-old', $('input[name="ubind_config_type['+index+']"]').val());
		$('input[name="ubind_organisation_id['+index+']"]').attr('data-old', $('input[name="ubind_organisation_id['+index+']"]').val());
		$('input[name="ubind_product_id['+index+']"]').attr('data-old', $('input[name="ubind_product_id['+index+']"]').val());
		$('input[name="ubind_portal_id['+index+']"]').attr('data-old', $('input[name="ubind_portal_id['+index+']"]').val());
		$('input[name="ubind_shortcode['+index+']"]').attr('data-old', $('input[name="ubind_shortcode['+index+']"]').val());
		$('input[name="ubind_portal_shortcode['+index+']"]').attr('data-old', $('input[name="ubind_portal_shortcode['+index+']"]').val());
		$('input[name="ubind_portal_fullscreen['+index+']"]').attr('data-old', $('input[name="ubind_portal_fullscreen['+index+']"]').val());
		$('select[name="ubind_form_type['+index+']"]').attr('data-old', $('select[name="ubind_form_type['+index+']"]').val());
		$('select[name="ubind_product_env['+index+']"]').attr('data-old', $('select[name="ubind_product_env['+index+']"]').val());
		$('input[name="ubind_sidebar_offset['+index+']"]').attr('data-old', $('input[name="ubind_sidebar_offset['+index+']"]').val());
		
		
		$('input[name="default_ubind_tenant_id['+index+']"]').attr('data-old', $('input[name="default_ubind_tenant_id['+index+']"]').is(':checked'));
		$('input[name="default_ubind_organisation_id['+index+']"]').attr('data-old', $('input[name="default_ubind_organisation_id['+index+']"]').is(':checked'));
		$('input[name="default_ubind_product_id['+index+']"]').attr('data-old', $('input[name="default_ubind_product_id['+index+']"]').is(':checked'));
		$('input[name="default_ubind_portal_id['+index+']"]').attr('data-old', $('input[name="default_ubind_portal_id['+index+']"]').is(':checked'));
		$('input[name="default_ubind_form_type['+index+']"]').attr('data-old', $('input[name="default_ubind_form_type['+index+']"]').is(':checked'));
		$('input[name="default_ubind_product_env['+index+']"]').attr('data-old', $('input[name="default_ubind_product_env['+index+']"]').is(':checked'));
		$('input[name="default_ubind_sidebar_offset['+index+']"]').attr('data-old', $('input[name="default_ubind_sidebar_offset['+index+']"]').is(':checked'));
		$('input[name="default_ubind_shortcode['+index+']"]').attr('data-old', $('input[name="default_ubind_shortcode['+index+']"]').is(':checked'));
		$('input[name="default_ubind_portal_shortcode['+index+']"]').attr('data-old', $('input[name="default_ubind_portal_shortcode['+index+']"]').is(':checked'));
		toggle_brackets();
	}
	
	function toggle_edit(index) {
		if ( index ) {
			$( "#ubind_title_"+index ).toggleClass( "colspanchange" );
			$( "#ubind_title_"+index ).toggleClass("title column-title has-row-actions column-primary page-title");

			$( "#ubind_header_"+index ).toggle();
			$( "#ubind_form_"+index ).toggle(100);
		}
	}
	
	function generate_shortcode(index) {
		$('input[name="ubind_shortcode['+index+']"]').val('save to get generated shortcode');
	}

	function generate_portal_shortcode(index) {
		$('input[name="ubind_portal_shortcode['+index+']"]').val('save to get generated shortcode');
	}
	
	function toggle_brackets() {
		$.each($(ubind_form+' .shortcode'), function() {
			toggle_element_brackets(this);
		});
	}

	function toggle_element_brackets(element) {
		var bracket_value = $(element).val();

		bracket_value = bracket_value.replace(/\[/g, "")
					.replace(/]/g, "");

		if ( bracket_value != '' ) {
			$(element).val('['+bracket_value+']');
		} else {
			$(element).val('');
		}
	}
	
	function delete_section(element) {
		if ($(element).attr('data-index')) {
			var index = $(element).attr('data-index');
		} else {
			var table = $(element).parent().prev('table:first');
			var index = table.attr('data-index');
		}

		var confirm_delete =window.confirm("Confirm DELETE of uBind Form "+index);
		if ( !confirm_delete ) {
			return;
		}
		
		var postData = {
			'ubind_option_id' : $('input[name="ubind_option_id['+index+']"]').val(),
			'ubind_settings_nonce' : ubind_ajax.ajax_nonce,
			'delete_section' : $('input[name="ubind_option_id['+index+']"]').val(),
		};

		$.post(ubind_ajax.ajaxurl+'?action=delete_section', postData).done(function (data) {
			var json_data = JSON.parse(data);
			var index = '';
			if (typeof json_data.success != 'undefined' ) {
				index = json_data.success;
			}
			if (typeof json_data.errors != 'undefined' ) {
				index = json_data.errors;
			}
			$('#ubind_row_'+index).remove();
		});
	}

	function toggle_save_button(index) {
		var table = $('table[data-index="'+index+'"]');
		var save_button = table.next('div').children('button.save:first');
		save_button.removeAttr('disabled');
		save_button.removeProp('disabled');
	}

	function toggle_forms() {
		$.each($('#ubind_table table'), function() {
			var index = $(this).attr('data-index');
			toggle_fields(index);
		});
	}

	function toggle_checkbox_associate(associate_element, generate_code, generate_portal_code) {
		$(associate_element).next('label').show();
		var checkbox_label = $(associate_element).next('label');
		checkbox_label.show();
		var checkbox_element = checkbox_label.children('input[type="checkbox"]:first');
		if (checkbox_element.is(':checked')) {
			$(associate_element).prop('disabled', 'disabled');
			
			var associate_name = $(associate_element).attr('name');
			var default_field_name = associate_name.replace(/\[(.*?)\]/g,'[0]');
			var default_field_element = $('input[name="'+default_field_name+'"]');

			if ( !default_field_element.length ) {
				default_field_element = $('select[name="'+default_field_name+'"]');
			}

			if (associate_name.indexOf('portal_shortcode') !== -1) {
				var index = checkbox_label.attr('data-index');
				if ( generate_code ) {
					generate_portal_shortcode(index);
				}
			} else if (associate_name.indexOf('shortcode') !== -1) {
				var index = checkbox_label.attr('data-index');
				if ( generate_code ) {
					generate_shortcode(index);
				}
			} else {
				$(associate_element).val(default_field_element.val());
			}
		} else {
			$(associate_element).removeAttr('disabled');
			$(associate_element).removeProp('disabled');
		}
	}

	function toggle_fields(index) {
		var form_id = '#ubind_form_'+index;
		var form_environment = $(form_id+' table').attr('data-env');
		var field_environment = false;

		$.each($(form_id+' input[type="text"]'), function() {
			var text_element = this;
			field_environment = $(text_element).attr('data-env');
			if (( form_environment == 1 && field_environment != '' )) {// || ( index == 0 )
				$(text_element).prop('disabled', 'disabled');
				$(text_element).next('label').hide();
			} else {
				toggle_checkbox_associate(text_element, 0);
			}
			if (index==0) {
				$(text_element).next('label').hide();
			}
		});

		var select_element = $(form_id+' select');
		field_environment = select_element.attr('data-env');
		if (( form_environment == 1  && field_environment != '')) {// || ( index == 0 )
			select_element.prop('disabled','disabled');
			select_element.next('label').hide();
		} else {
			toggle_checkbox_associate(select_element, 0);
		}
		if (index==0) {
			$(select_element).next('label').hide();
		}
	}

	function toggle_default_form() {
		$( "#ubind_title_0" ).attr("class","colspanchange" );
		$( "#ubind_header_0" ).hide();
		$( "#ubind_form_0" ).show(300);

		$('button[data-index="0"]').hide();
		$('label[data-index="0"]').hide();
		
		$.each($('#ubind_form_0 input[type="checkbox"]'), function() {
			$(this).removeAttr('checked');
			$(this).removeProp('checked');
		});
	}

	function create_form_data(index) {
		return {
			'ubind_option_id':$('input[name="ubind_option_id['+index+']"]').val(),
			'ubind_dotenv_id':$('input[name="ubind_dotenv_id['+index+']"]').val(),
			'ubind_tenant_id':$('input[name="ubind_tenant_id['+index+']"]').val(),
			'default_ubind_tenant_id':($('input[name="default_ubind_tenant_id['+index+']"]').is(':checked')?1:0),
			'ubind_config_type':$('input[name="ubind_config_type['+index+']"]:checked').val(),
			'ubind_organisation_id':$('input[name="ubind_organisation_id['+index+']"]').val(),
			'default_ubind_organisation_id':($('input[name="default_ubind_organisation_id['+index+']"]').is(':checked')?1:0),
			'ubind_product_id':$('input[name="ubind_product_id['+index+']"]').val(),
			'default_ubind_product_id':($('input[name="default_ubind_product_id['+index+']"]').is(':checked')?1:0),
			'ubind_portal_id':$('input[name="ubind_portal_id['+index+']"]').val(),
			'default_ubind_portal_id':($('input[name="default_ubind_portal_id['+index+']"]').is(':checked')?1:0),
			'ubind_form_type':$('select[name="ubind_form_type['+index+']"]').val(),
			'default_ubind_form_type':($('input[name="default_ubind_form_type['+index+']"]').is(':checked')?1:0),
			'ubind_product_env':$('select[name="ubind_product_env['+index+']"]').val(),
			'default_ubind_product_env':($('input[name="default_ubind_product_env['+index+']"]').is(':checked')?1:0),
			'ubind_shortcode':$('input[name="ubind_shortcode['+index+']"]').val(),
			'default_ubind_shortcode':($('input[name="default_ubind_shortcode['+index+']"]').is(':checked')?1:0),
			'ubind_portal_shortcode':$('input[name="ubind_portal_shortcode['+index+']"]').val(),
			'default_ubind_portal_shortcode':($('input[name="default_ubind_portal_shortcode['+index+']"]').is(':checked')?1:0),
			'ubind_portal_fullscreen':$('input[name="ubind_portal_fullscreen['+index+']"]:checked').val(),
			'ubind_sidebar_offset':$('input[name="ubind_sidebar_offset['+index+']"]').val(),
			'default_ubind_sidebar_offset':($('input[name="default_ubind_sidebar_offset['+index+']"]').is(':checked')?1:0),
			"ubind_settings_nonce" : ubind_ajax.ajax_nonce,
		};
	}
	
	function show_xmlhttprequest_success(json_data) {
		if (typeof json_data.success != 'undefined' ) {
			var index = json_data.success;
			$('#ubind_form_'+index+' .save').prop('disabled', 'disabled');
			if ( typeof json_data.data != 'undefined' ) {
				$('input[name="ubind_shortcode['+index+']"]').val(json_data.data.ubind_shortcode);
				$('input[name="ubind_portal_shortcode['+index+']"]').val(json_data.data.ubind_portal_shortcode);
				$.each(json_data.data, function(i,e){
					if (i == 'ubind_product_env' || i == 'ubind_form_type') {
						$('select[name="'+i+'['+index+']"]').css("border", "");
					} else {
						$('input[name="'+i+'['+index+']"]').css("border", "");
					}
				});
			}	
			$('#ubind_form_'+index+' .spinner').css('visibility', 'hidden');
			create_clipboard(index);
		}
	}
	
	function show_xmlhttprequest_errors(json_data) {
		if (typeof json_data.error != 'undefined' ) {
			var index = json_data.error;
			
			if ( typeof json_data.data != 'undefined' ) {
				$('#ubind_form_'+index+' input').css("border", "");
				$('#ubind_form_'+index+' select').css("border", "");
				
				$.each(json_data.data, function(i,e){
					if (i == 'ubind_product_env' || i == 'ubind_form_type') {
						$('select[name="'+i+'['+index+']"]').css("border", "1px solid red");
					} else {
						$('input[name="'+i+'['+index+']"]').css("border", "1px solid red");
					}
				});
			}

			if ( typeof json_data.notice != 'undefined' ) {
				$.each(json_data.notice, function(i,e){
					$('#ubind_form_'+index+' .ubind-notice').append(e);
					$('#ubind_form_'+index+' .ubind-notice').show(100);
				});
			}
			
			$('#ubind_form_'+index+' .spinner').css('visibility', 'hidden');
		}
	}
	
	function toggle_config_type(e) {
		var data_index = $(e).attr('data-index');
		var config_type = $(e).val();
		
		$.each($('#ubind_row_'+data_index+' tr'), function() {
			var row_element = this;
			var config_type_supported = $(row_element).attr('data-row-type');
			
				if (config_type_supported.indexOf(config_type) !== -1) {
					$(row_element).show();
				} else {
					$(row_element).hide();
				}
		});
	}
	
	function toggle_rows() {
		$.each($(ubind_form+' .ubind_config_type'), function() {
			if ($(this).is(':checked')) {
				toggle_config_type(this);
			}
		});
	}
	
	function initialize_form() {
		toggle_default_form();
		toggle_forms();
		toggle_brackets();
		toggle_rows();
	}
	
	initialize_form();
	
	$(document).on('click', ubind_form+' .editinline', function() {
		var index = $(this).attr('data-index');
		toggle_edit(index);
		create_clipboard(index);
	});

	$(document).on('click', ubind_form+' a.row-title', function(e) {
		e.preventDefault();
		var index = $(this).attr('data-index');
		toggle_edit(index);
		create_clipboard(index);
	});

	$(document).on('click', ubind_form+' .ubind-cancel', function() {
		var index = $(this).attr('data-index');
		var form_id = '#ubind_form_'+index;
		var form_environment = $(form_id+' table').attr('data-env');
		var field_environment = false;
		
		$.each($(form_id+' input[type="checkbox"]'), function() {
			var check_element = this;
			if ( typeof $(check_element).attr('data-old') != 'undefined' ) {
				if ( $(check_element).attr('data-old') == 'true' ) {
					$(check_element).prop('checked','checked');
				} else {
					$(check_element).removeAttr('checked');
					$(check_element).removeProp('checked');
				}
			}
		});
		
		$.each($(form_id+' input[type="text"]'), function() {
			var text_element = this;
			field_environment = $(text_element).attr('data-env');
			if ( form_environment == 1 && field_environment != '' ) {
				$(text_element).prop('disabled', 'disabled');
			} else {
				var check_box = $(text_element).next('label').children('input[type="checkbox"]:first');
				if ( check_box.is(':checked') ) {
					$(text_element).prop('disabled', 'disabled');
				} else {
					$(text_element).removeAttr('disabled');
					$(text_element).removeProp('disabled');
				}
			}
			
			if ( $(text_element).attr('data-old') ) {
				$(text_element).val($(text_element).attr('data-old'));
			}
		});

		var select_element = $(form_id+' select');
		field_environment = select_element.attr('data-env');
		if ( form_environment == 1  && field_environment != '') {
			select_element.prop('disabled','disabled');
		} else {
			var check_box = $(select_element).next('label').children('input[type="checkbox"]:first');
			if ( check_box.is(':checked') ) {
				$(select_element).prop('disabled', 'disabled');
			} else {
				$(select_element).removeAttr('disabled');
				$(select_element).removeProp('disabled');
			}
		}
		if ( $(select_element).attr('data-old') ) {
			$(select_element).val($(select_element).attr('data-old'));
		}
		
		toggle_edit(index);
	});

	$(document).on('click', '#add_new', function(e) {
		e.preventDefault();
		var tbody = $("#ubind_table tbody");
		var tr = $(tbody).closest('tr').first();

		var pattern = $(tr).html();
		var index = $(ubind_form+' input[type="hidden"]:last').attr('name').match(/\[(.*?)\]/)[1];
		var new_index = (parseInt(index)+1);

		pattern = pattern
					.replace(/\[(.*?)\]/g, "["+new_index+"]")
					.replace(/_0/g, "_"+new_index)
					.replace(/data-index=\"0\"/g, "data-index=\""+new_index+"\"");

		var new_row = $('<tr id="ubind_row_'+new_index+'" class="iedit author-self type-page hentry entry"></tr>').append(pattern);
		$("#ubind_table").append(new_row);

		$('#ubind_header_'+new_index+' a').html("uBind Form "+new_index);
		$('#ubind_form_'+new_index+' h2').html("uBind Form "+new_index);
		$('#ubind_form_'+new_index+' i').html("");

		$('label[data-index="'+new_index+'"]').show();

		$.each($('label[data-index="'+new_index+'"] input[type="checkbox"]'), function() {
			$(this).prop('checked',false);
		});

		$.each($('#ubind_form_'+new_index+' input[type="text"]'), function() {
			$(this).val('');
			$(this).removeAttr('disabled');
			$(this).removeProp('disabled');
		});

		$('#ubind_form_'+new_index+' select').removeAttr('disabled');
		$('#ubind_form_'+new_index+' select').removeProp('disabled');

		$('button[data-index="'+new_index+'"].ubind-cancel').hide();
		$('button[data-index="'+new_index+'"].ubind-delete').show();

		$('input[name="ubind_option_id['+new_index+']"]').val(new_index);
		$('input[name="ubind_dotenv_id['+new_index+']"]').val('');
		$('input[name="ubind_tenant_id['+new_index+']"]').focus();
		return false;
	});

	$(document).on('input cut copy paste', ubind_form+' input[type="text"]', function() {
		var index = $(this).closest('table').attr('data-index');
		toggle_save_button(index);
	});

	$(document).on('blur', ubind_form+' input[type="text"].shortcode', function() {
		toggle_brackets();
	});

	$(document).on('click', ubind_form+' button.save', function() {
		var table = $(this).parent().prev('table:first');
		var index = table.attr('data-index');
		
		var postData = create_form_data(index);

		$('#ubind_form_'+index+' .spinner').css('visibility', 'visible');
		$('#ubind_form_'+index+' .ubind-notice').html('');
		$('#ubind_form_'+index+' .ubind-notice').hide(100);
		
		$.post(ubind_ajax.ajaxurl+'?action=save_section', postData).done(function (data) {
			var json_data = JSON.parse(data);
			show_xmlhttprequest_success(json_data);
			show_xmlhttprequest_errors(json_data);
			
		});
	});
	
	$(document).on('click', ubind_form+' button.delete', function(e) {
		delete_section(this);
		e.preventDefault();
	});

	$(document).on('click', ubind_form+' a.submitdelete', function(e) {
		e.preventDefault();
		delete_section(this);
	});

	$(document).on('change', ubind_form+' input[type="radio"]', function() {
		if ( $(this).hasClass('ubind_config_type') ) {
			toggle_config_type(this);
		}
		var index = $(this).closest('table').attr('data-index');
		toggle_save_button(index);
	});
	
	$(document).on('change', ubind_form+' input[type="checkbox"]', function() {
		var associate_element = $(this).parent().prev('input[type="text"]:first');
		if (!associate_element.length) {
			associate_element = $(this).parent().prev('select:first');
		}

		if (associate_element.length) {
			toggle_checkbox_associate(associate_element, 1);
		}
		var index = $(this).closest('table').attr('data-index');
		toggle_save_button(index);
	});

	$(document).on('change', ubind_form+' select', function() {
		var index = $(this).closest('table').attr('data-index');
		toggle_save_button(index);
	});
});