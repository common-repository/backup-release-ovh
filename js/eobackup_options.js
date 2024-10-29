eobackup(document).ready(function(){
	/*	Add support for option excluded domain deletion	*/
	jQuery(".delete_domain_to_exclude").live('click', function(){
		jQuery(this).closest("div").remove();
	});
	/*	Add support for option excluded domain addition	*/
	jQuery(".add_new_domain_to_exclude").click(function(){
		if(jQuery("#new_excluded_domain").val() != ""){
			jQuery("#excluded_domain_list").append("<div><input type='text' value='" + jQuery("#new_excluded_domain").val() + "' name='eobu_options[eobu_excluded_domain][]' /><img src='" + EO_BU_MEDIA_PLUGIN_URL + "delete.png' alt='" + EOBU_DELETE_EXCLUDED_DOMAIN_TEXT + "' title='" + EOBU_DELETE_EXCLUDED_DOMAIN_TEXT + "' class='delete_domain_to_exclude' /></div>");
			jQuery("#new_excluded_domain").val("");
		}
		else{
			alert(eobu_convert_html_accent_for_js(EOBU_DELETE_EMPTY_NEWDOMAIN_TEXT));
		}
	});
	/*	Add support for option default excluded domain usage	*/
	jQuery(".add_default_domain_to_exclude").live("click", function(){
		var output = "";
		EOBU_EXCLUDED_DOMAIN_LIST = EOBU_EXCLUDED_DOMAIN.split(', ');
		for (var i = 0; i < EOBU_EXCLUDED_DOMAIN_LIST.length; i++){
			output += "<div><input type='text' value='" + EOBU_EXCLUDED_DOMAIN_LIST[i] + "' name='eobu_options[eobu_excluded_domain][]' /><img src='" + EO_BU_MEDIA_PLUGIN_URL + "delete.png' alt='" + EOBU_DELETE_EXCLUDED_DOMAIN_TEXT + "' title='" + EOBU_DELETE_EXCLUDED_DOMAIN_TEXT + "' class='delete_domain_to_exclude' /></div>";
		}
		jQuery("#excluded_domain_list").append(output);
		jQuery("#use_default_list").remove();
	});

	/*	Add support for option excluded domain deletion	*/
	jQuery(".delete_email_report").live('click', function(){
		jQuery(this).closest("div").remove();
	});
	/*	Add support for option excluded domain addition	*/
	jQuery(".add_new_email_report").click(function(){
		if(jQuery("#new_report_email").val() != ""){
			jQuery("#email_report_list").append("<div class='clear' ><input type='text' value='" + jQuery("#new_report_email").val() + "' name='eobu_options[eobu_email][]' /><img src='" + EO_BU_MEDIA_PLUGIN_URL + "delete.png' alt='" + EOBU_DELETE_EMAIL_REPORT_TEXT + "' title='" + EOBU_DELETE_EMAIL_REPORT_TEXT + "' class='delete_email_report' /></div>");
			jQuery("#new_report_email").val("");
		}
		else{
			alert(eobu_convert_html_accent_for_js(EOBU_DELETE_EMPTY_NEWEMAIL_REPORT_TEXT));
		}
	});

	/*	Add support for option excluded domain deletion	*/
	jQuery(".delete_allowed_ip_address").live('click', function(){
		jQuery(this).closest("div").remove();
	});
	/*	Add support for option excluded domain addition	*/
	jQuery(".add_new_ip_address").click(function(){
		if(jQuery("#new_allowed_ip_address").val() != ""){
			jQuery("#allowed_ip_address_list").append("<div class='clear' ><input type='text' value='" + jQuery("#new_allowed_ip_address").val() + "' name='eobu_options[eobu_allowed_ip][]' /><img src='" + EO_BU_MEDIA_PLUGIN_URL + "delete.png' alt='" + EOBU_DELETE_EMAIL_REPORT_TEXT + "' title='" + EOBU_DELETE_EMAIL_REPORT_TEXT + "' class='delete_allowed_ip_address' /></div>");
			jQuery("#new_allowed_ip_address").val("");
		}
		else{
			alert(eobu_convert_html_accent_for_js(EOBU_DELETE_EMPTY_NEWIP_REPORT_TEXT));
		}
	});

	/*	Check if mandatory field are empty or not	*/
	jQuery(".eobu_mandatory_field").each(function(){
		if(jQuery.trim(jQuery(this).val()) != ""){
			jQuery(this).parent("td").parent("tr").children("th").css("color", "black");
		}
	});
	if(jQuery("#email_report_list").html() != ""){
		jQuery("#email_report_list").parent("fieldset").parent("td").parent("tr").children("th").css("color", "black");
	}

	/*	Add support for sh file generation	*/
	jQuery("#backup_sh_script_generator").ajaxForm({
		target : "#script_file_container",
		beforeSubmit:  before_script_generation
	});
});

function before_script_generation(){
	eobackup("#script_file_container").html(eobackup("#loading_picture").html());
}