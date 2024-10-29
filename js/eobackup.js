var eobackup = jQuery.noConflict();

eobackup(document).ready(function(){
	/*	Hide message after a given time	*/
	setTimeout(function(){
		jQuery(".eobackup_page_message").hide();
	}, 5500);


	/*	Add the check/uncheck all button	*/
	jQuery("#checkall_list").click(function(){
		if(jQuery(this).is(":checked")){
			check_all = true;
		}
		else{
			check_all = false;
		}

		jQuery(".domain_list_cb").each(function(){
			jQuery(this).prop('checked', check_all);
		});
	});


	/*	Change the start date field into a date type field	*/
	jQuery("#start_date").datepicker(jQuery.datepicker.regional[EO_BU_LOCALE]);
	jQuery("#start_date").datepicker("option", "dateFormat", "yy-mm-dd");
	jQuery("#start_date").datepicker("option", "changeMonth", true);
	jQuery("#start_date").datepicker("option", "changeYear", true);
	jQuery("#start_date").datepicker("option", "navigationAsDateFormat", true);
	jQuery("#ui-datepicker-div").hide();


	/*	When the user select an action to apply on the entire domain list	*/
	jQuery("#domain_list_selection_action").live("change", function(){
		if(jQuery(this).val() == "plan"){
			jQuery("#eobackup_plan_params").show();
		}
		else{
			jQuery("#eobackup_plan_params").hide();
		}
	});
	/*	Check if user select an action to apply to a domain selection list	*/
	jQuery("#save_main_list").live("click", function(){
		/*	If the selected action is to moderated the list, ask confirmation to user	*/
		if(jQuery("#domain_list_selection_action").val() == "moderated"){
			if(!confirm(eobu_convert_html_accent_for_js(EOBU_DSACTIVATE_BACKUP_TEXT))){
				return false;
			}
		}
	})

	
	jQuery("#domain_details_dialog").dialog({
		autoOpen: false,
		width:800,
		height:400
	});
	jQuery(".backup_log_button").live("click", function(){
		jQuery("#domain_details_dialog").html(jQuery("#loading_picture").html());
		jQuery("#domain_details_dialog").load(EO_BU_AJAX_FILE, {
			"action": "load_history_log",
			"history_id" : jQuery(this).attr("id").replace("view_backup_log", "")
		});
		jQuery("#domain_details_dialog").dialog("option", "title", EOBU_VIEW_LOG_TEXT);
		jQuery("#domain_details_dialog").dialog("open");
	});

	/*	Add support for backup type management	*/
	jQuery("#domain_backup_type").change(function(){
		jQuery(".backup_type").hide();
		jQuery("#" + jQuery(this).val() + "_backup").show();
	})

	/*	Add autocomplete for some options values	*/
	jQuery("#eobu_server").blur(function(){
		if(jQuery("#eobu_directories_php_container").val() == ''){
			jQuery("#eobu_directories_php_container").val(jQuery(this).val());
		}
	});
});

function eobu_convert_html_accent_for_js(text){
	text = text.replace(/&Agrave;/g, "\300");
	text = text.replace(/&Aacute;/g, "\301");
	text = text.replace(/&Acirc;/g, "\302");
	text = text.replace(/&Atilde;/g, "\303");
	text = text.replace(/&Auml;/g, "\304");
	text = text.replace(/&Aring;/g, "\305");
	text = text.replace(/&AElig;/g, "\306");
	text = text.replace(/&Ccedil;/g, "\307");
	text = text.replace(/&Egrave;/g, "\310");
	text = text.replace(/&Eacute;/g, "\311");
	text = text.replace(/&Ecirc;/g, "\312");
	text = text.replace(/&Euml;/g, "\313");
	text = text.replace(/&Igrave;/g, "\314");
	text = text.replace(/&Iacute;/g, "\315");
	text = text.replace(/&Icirc;/g, "\316");
	text = text.replace(/&Iuml;/g, "\317");
	text = text.replace(/&Eth;/g, "\320");
	text = text.replace(/&Ntilde;/g, "\321");
	text = text.replace(/&Ograve;/g, "\322");
	text = text.replace(/&Oacute;/g, "\323");
	text = text.replace(/&Ocirc;/g, "\324");
	text = text.replace(/&Otilde;/g, "\325");
	text = text.replace(/&Ouml;/g, "\326");
	text = text.replace(/&Oslash;/g, "\330");
	text = text.replace(/&Ugrave;/g, "\331");
	text = text.replace(/&Uacute;/g, "\332");
	text = text.replace(/&Ucirc;/g, "\333");
	text = text.replace(/&Uuml;/g, "\334");
	text = text.replace(/&Yacute;/g, "\335");
	text = text.replace(/&THORN;/g, "\336");
	text = text.replace(/&Yuml;/g, "\570");
	text = text.replace(/&szlig;/g, "\337");
	text = text.replace(/&agrave;/g, "\340");
	text = text.replace(/&aacute;/g, "\341");
	text = text.replace(/&acirc;/g, "\342");
	text = text.replace(/&atilde;/g, "\343");
	text = text.replace(/&auml;/g, "\344");
	text = text.replace(/&aring;/g, "\345");
	text = text.replace(/&aelig;/g, "\346");
	text = text.replace(/&ccedil;/g, "\347");
	text = text.replace(/&egrave;/g, "\350");
	text = text.replace(/&eacute;/g, "\351");
	text = text.replace(/&ecirc;/g, "\352");
	text = text.replace(/&euml;/g, "\353");
	text = text.replace(/&igrave;/g, "\354");
	text = text.replace(/&iacute;/g, "\355");
	text = text.replace(/&icirc;/g, "\356");
	text = text.replace(/&iuml;/g, "\357");
	text = text.replace(/&eth;/g, "\360");
	text = text.replace(/&ntilde;/g, "\361");
	text = text.replace(/&ograve;/g, "\362");
	text = text.replace(/&oacute;/g, "\363");
	text = text.replace(/&ocirc;/g, "\364");
	text = text.replace(/&otilde;/g, "\365");
	text = text.replace(/&ouml;/g, "\366");
	text = text.replace(/&oslash;/g, "\370");
	text = text.replace(/&ugrave;/g, "\371");
	text = text.replace(/&uacute;/g, "\372");
	text = text.replace(/&ucirc;/g, "\373");
	text = text.replace(/&uuml;/g, "\374");
	text = text.replace(/&yacute;/g, "\375");
	text = text.replace(/&thorn;/g, "\376");
	text = text.replace(/&yuml;/g, "\377");
	text = text.replace(/&oelig;/g, "\523");
	text = text.replace(/&OElig;/g, "\522");

	return text;
}
