<?php
/**
*	Domain management
* 
* Define the different method to manage domain
* @author Eoxia <dev@eoxia.com>
* @version 0.1
* @package backup_release_ovh
* @subpackage librairies
*/


/**
* Define the different method to manage domain
* @author Eoxia <dev@eoxia.com>
* @version 0.1
* @package backup_release_ovh
* @subpackage librairies
*/
class eobu_domain{

	/**
	*
	*/
	function get_unconfigured_domain(){
		/* Get existing element into home dir	*/
		if($_SERVER['SERVER_ADDR'] != '127.0.0.1'){
			$eobu_excluded_domain = eobu_options::get_specific_option('eobu_options', 'eobu_excluded_domain');
			exec("ls " . ((($eobu_excluded_domain != '')) ? " -I " . implode(" -I ", $eobu_excluded_domain) . " " : " ") . "/home", $domain_list);
		}
		else{
			$domain_list = unserialize('a:4:{i:0;s:4:"test";i:1;s:5:"test1";i:2;s:5:"test2";i:3;s:5:"test3";}');
		}

		/*	Get existing domain	*/
		$existing_domain = eobu_domain::get_saved_domain_list();
		$stored_domain = array();
		foreach($existing_domain as $domain_definition){
			$stored_domain[$domain_definition->name]['status'] = $domain_definition->DOM_STATUS;
		}

		$unconfigured_domain = $moderated_configured_domain = '';
		foreach($domain_list as $index => $domain){
			if(!array_key_exists($domain, $stored_domain)){
				$unconfigured_domain .= $domain . "<br/>";
			}
			elseif($stored_domain[$domain]['status'] == 'moderated'){
				$moderated_configured_domain .= $domain . "<br/>";
			}
		}


		$send_email = false;
		$email_content = __('La liste ci-dessous contient les domaines pr&eacute;sents sur le serveur %s mais qui n\'ont pas encore &eacute;t&eacute; configur&eacute;s pour les sauvegardes', 'eobackup') . "<br/><br/>";
		if($unconfigured_domain != ''){
			$email_content .= $unconfigured_domain . "<br/>";
			$send_email = true;
		}
		if((eobu_options::get_specific_option('eobu_options', 'eobu_send_mail_for_moderated_unconfigured_domain') == 'oui') && ($moderated_configured_domain != '')){
			$email_content .= html_entity_decode(sprintf(__('Liste des domaines qui sont mod&eacute;r&eacute;s', 'eobackup'), $eobu_server)) . '<br/>' . $moderated_configured_domain . "<br/>";
			$send_email = true;
		}

		if($send_email){
			$eobu_server = eobu_options::get_specific_option('eobu_options', 'eobu_server');
			$eobu_email = eobu_options::get_specific_option('eobu_options', 'eobu_email');
			/*	Check if the directories are set correctly before continuing	*/
			if(!is_array($eobu_email)){
				$eobu_email[] = get_option('admin_email');
			}
			$headers ="From: \"" . __('Service de sauvegarde', 'eobackup') . " " . $eobu_server . " - " . html_entity_decode(__('Domaine non configur&eacute;', 'eobackup')) . "\" <" . get_option('admin_email') . ">\n"; 
			$headers.="Reply-to: \"" . __('Service de sauvegarde', 'eobackup') . " " . $eobu_server . " - " . html_entity_decode(__('Domaine non configur&eacute;', 'eobackup')) . "\" <" . get_option('admin_email') . ">\n";
			$headers.="MIME-Version: 1.0\n";
			$headers.="Content-type: text/html; charset= iso-8859-1\n";

			$sujet = html_entity_decode(sprintf(__('Des domaines pr&eacute;sents sur le serveur %s, n\'ont pas &eacute;t&eacute; configur&eacute;s pour les sauvegardes', 'eobackup'), $eobu_server));
			foreach($eobu_email as $email){
				@mail($email, $sujet, sprintf($email_content, $eobu_server), $headers);
			}
		}

	}

	/**
	*	Define content for domain management main page
	*/
	function domain_main_page(){
		$page_content = '';

		/*	If main save button has been pressed launch save action	*/
		if(isset($_POST['save_main_list']) && ($_POST['save_main_list'] != '')){
			$save_domain_result = eobu_domain::save_domain_list();
		}
		/*	If main save button has been pressed launch save action	*/
		if(isset($_POST['save_domain_backup_config']) && ($_POST['save_domain_backup_config'] != '')){
			$save_domain_result = eobu_domain::save_domain_config($_POST);
		}

		/*	Define if the page output must be the domain list or a domain eidt form	*/
		if(!isset($_REQUEST['domain_to_edit']) || ($_REQUEST['domain_to_edit'] == '')){
			$page_content = eobu_domain::domain_list();
		}
		elseif($_REQUEST['domain_to_edit'] != ''){
			$page_content = eobu_domain::domain_edit($_REQUEST['domain_to_edit']);
		}

		echo $page_content;
	}

	/**
	*	Define the domain edit interface
	*
	*	@param integer $domain_id The domain identifier we want to update
	*
	*	@return string $domain_edit_form The form allowing to modifiy information about a domain
	*/
	function domain_edit($domain_identifier){
		global $wpdb;
		$domain_edit_form = '';
		$domain_id = 0;

		$query = $wpdb->prepare("
SELECT id, status
FROM " . EOBU_DBT_DOMAIN . " AS DOMAIN
WHERE DOMAIN.name = %s", $domain_identifier);
		$domain_infos = $wpdb->get_row($query);

		/*	Get domain history	*/
		$more_tabs = '';
		if($domain_infos->id > 0){
			$more_tabs = '
		<li ><a href="' . EO_BU_INC_PLUGIN_URL . 'ajax.php?action=domain_history_backup&amp;domain_identifier=' . $domain_identifier . '" title="domain_details_content">' . __('Historique des sauvegardes', 'eobackup') . '</a></li>
		<li ><a href="' . EO_BU_INC_PLUGIN_URL . 'ajax.php?action=domain_history_restore&amp;domain_identifier=' . $domain_identifier . '"title="domain_details_content" >' . __('Historique des restaurations', 'eobackup') . '</a></li>';
		}

		$domain_edit_form = 
eobu_display::start_page(sprintf(__('&Eacute;dition du domaine "%s"', 'eobackup'), $domain_identifier), '', '', '') . '
<div id="domain_details_dialog" class="eobackup_hide" >&nbsp;</div>
<div id="domain_detail_element" >
	<ul>
		<li ><a href="' . EO_BU_INC_PLUGIN_URL . 'ajax.php?action=domain_config_form&amp;domain_identifier=' . $domain_identifier . '" title="domain_details_content">' . __('Configuration', 'eobackup') . '</a></li>' . $more_tabs . '
	</ul>
	<div id="domain_details_content" >&nbsp;
	</div>
</div>
' . eobu_display::end_page() . '
<script type="text/javascript" >
	eobackup(document).ready(function(){
		jQuery("#domain_details_content").html(jQuery("#loading_picture").html());
		jQuery("#domain_detail_element").tabs({
      select: function(event, ui){
				jQuery("#domain_details_content").html(jQuery("#loading_picture").html());
				var url = jQuery.data(ui.tab, "load.tabs");
				jQuery("#domain_details_content").load(url);
				jQuery("#domain_detail_element ul li").each(function(){
					jQuery(this).removeClass("ui-tabs-selected ui-state-active");
				});
				jQuery("#domain_detail_element ul li:eq(" + ui.index + ")").addClass("ui-tabs-selected ui-state-active");
				return false;
      }
		});
	});
</script>';

		return $domain_edit_form;
	}

	/**
	*	Define the main interface allowing to view existing domain list with some informations
	*
	*	@return string $domain_list_output An complete html output with existing domain
	*/
	function domain_list(){
		global $domain_list, $wpdb;
		$domain_list_output = '';

		$domain_list = array();
		/*	Get domain to exclude from configuration	*/
		$eobu_excluded_domain = eobu_options::get_specific_option('eobu_options', 'eobu_excluded_domain');

		/* Get existing element into home dir	*/
		if($_SERVER['SERVER_ADDR'] != '127.0.0.1'){
			exec("ls " . ((($eobu_excluded_domain != '')) ? " -I " . implode(" -I ", $eobu_excluded_domain) . " " : " ") . "/home", $domain_list);
		}
		else{
			$domain_list = unserialize('a:4:{i:0;s:4:"test";i:1;s:5:"test1";i:2;s:5:"test2";i:3;s:5:"test3";}');
		}

		/*	Get existing domain	*/
		$existing_domain = eobu_domain::get_saved_domain_list();
		$stored_domain = array();
		foreach($existing_domain as $domain_definition){
			$stored_domain[$domain_definition->name]['id'] = $domain_definition->DOM_ID;
			$stored_domain[$domain_definition->name]['status'] = $domain_definition->DOM_STATUS;
			$stored_domain[$domain_definition->name]['last_backup'] = $domain_definition->last_backup;
			$stored_domain[$domain_definition->name]['next_backup'] = $domain_definition->next_backup;
			$stored_domain[$domain_definition->name]['status'] = $domain_definition->DOM_STATUS;
			$stored_domain[$domain_definition->name]['calendar_id'] = $domain_definition->id;
			$stored_domain[$domain_definition->name]['db_backup_type'] = $domain_definition->db_backup_type;
			$stored_domain[$domain_definition->name]['backup_type'] = $domain_definition->backup_type;
			$stored_domain[$domain_definition->name]['creation_date'] = $domain_definition->creation_date;
			$stored_domain[$domain_definition->name]['interval'] = $domain_definition->interval;
			$stored_domain[$domain_definition->name]['interval_type'] = $domain_definition->interval_type;
			$stored_domain[$domain_definition->name]['start_date'] = $domain_definition->start_date;
			$stored_domain[$domain_definition->name]['backup_total_nb'] = $domain_definition->backup_total_nb;
			$stored_domain[$domain_definition->name]['special_interval'] = $domain_definition->special_interval;
			$stored_domain[$domain_definition->name]['LAST_START'] = $domain_definition->LAST_START;
			$stored_domain[$domain_definition->name]['LAST_END'] = $domain_definition->LAST_END;
			$stored_domain[$domain_definition->name]['file_status'] = $domain_definition->file_status;
			$stored_domain[$domain_definition->name]['db_status'] = $domain_definition->db_status;
		}

		/*	Display table with existint domain on current server	*/
		$tableId = 'current_server_list';
		$tableSummary = __('Liste des domaines existants sur le serveur courant', 'eobackup');
		$tableTitles = array();
		$tableTitles[] = '<input type="checkbox" name="checkall_list" id="checkall_list" value="" title="' . __('Cocher / d&eacute;cocher tout', 'eobackup') . '" />';
		$tableTitles[] = __('Domaine', 'eobackup');
		$tableTitles[] = __('Planification', 'eobackup');
		$tableTitles[] = __('Remarques sur le domaine', 'eobackup');
		$tableClasses = array();
		$tableClasses[] = 'eobu_save_domain_label_column';
		$tableClasses[] = 'eobu_domain_label_column';
		$tableClasses[] = 'eobu_save_domain_planification_label_column';
		$tableClasses[] = 'eobu_save_domain_remark_label_column';
		$tableRowClass = array();
		$line = 0;
		foreach($domain_list as $index => $domain){
			$tableRowsId[$line] = 'domain_' . $index . '_' . sanitize_title($domain);

			unset($tableRowValue);

			$save_domain = '<input class="domain_list_cb" type="checkbox" name="domain_to_save[' . $domain . '][name]" id="domain_to_save_' . $domain . '" value="' . $domain . '" ' . $checked . ' />';

			$checked = '';
			$domain_extra_class = '';
			if(array_key_exists($domain, $stored_domain)){
				if($stored_domain[$domain]['status'] == 'valid'){
					$domain_extra_class = 'domain_to_save';
				}
				if(isset($stored_domain[$domain]['id']) && ($stored_domain[$domain]['id'] > 0)){
					$save_domain .= '<input type="hidden" name="domain_to_save[' . $domain . '][id]" id="domain_to_save_id_' . $domain . '" value="' . $stored_domain[$domain]['id'] . '" />';
				}
				if(isset($stored_domain[$domain]['calendar_id']) && ($stored_domain[$domain]['calendar_id'] > 0)){
					$save_domain .= '<input type="hidden" name="domain_to_save[' . $domain . '][calendar_id]" id="domain_to_save_id_' . $domain . '" value="' . $stored_domain[$domain]['calendar_id'] . '" />';
				}
			}
			else{
				$domain_extra_class = 'domain_not_configure';
			}

			$domain_remark = '';
			$magento_db_url = '';
			$query = $wpdb->prepare("SELECT value FROM " . $domain . ".core_config_data WHERE path = 'web/unsecure/base_url'");
			$magento_db_url = $wpdb->get_var($query);
			if($magento_db_url != ''){
				$domain_remark .= '<img class="propulsed_icon" src="' . EO_BU_MEDIA_PLUGIN_URL . 'logo_magento.gif" alt="' . __('Ce site est propuls&eacute; par magento', 'eobackup') . '" title="' . __('Ce site est propuls&eacute; par magento', 'eobackup') . '" />&nbsp;<a href="' . $magento_db_url . '" target="magento_site" >' . $magento_db_url . '</a>';
			}
			else{
				$wp_db_url = '';
				$query = $wpdb->prepare("SELECT option_value FROM " . $domain . ".wp_options WHERE option_name = 'siteurl'");
				$wp_db_url = $wpdb->get_var($query);
				if($wp_db_url != ''){
					$domain_remark .= '<img class="propulsed_icon" src="' . EO_BU_MEDIA_PLUGIN_URL . 'logo_wp.gif" alt="' . __('Ce site est propuls&eacute; par wordpress', 'eobackup') . '" title="' . __('Ce site est propuls&eacute; par wordpress', 'eobackup') . '" />&nbsp;<a href="' . $magento_db_url . '" target="wordpress_site" >' . $wp_db_url . '</a>';
				}
			}

			$domain_label = '<a href="' . admin_url('admin.php?page=' . EOBU_SLUG_DOMAIN . '&amp;domain_to_edit=' . $domain) . '" >' . $domain . '</a>';
			if(in_array($domain, $eobu_excluded_domain)){
				$domain_extra_class = 'excluded_domain_from_configuration';
				$save_domain = '';
				$domain_remark = __('Ce domaine est exclus de la sauvegarde par la configuration g&eacute;n&eacute;rale du plugin', 'eobackup');
				$domain_label = $domain;
			}
			elseif(array_key_exists($domain, $stored_domain) && ($stored_domain[$domain]['status'] == 'excluded')){
				$domain_extra_class = 'excluded_domain';
			}
			elseif(array_key_exists($domain, $stored_domain) && ($stored_domain[$domain]['status'] == 'moderated')){
				$domain_extra_class = 'moderated_domain';
			}
			$tableRowValue[] = array('class' => 'save_domain_label_cell', 'value' => $save_domain);
			$tableRowValue[] = array('class' => 'domain_label_cell', 'value' => $domain_label);
			$domain_calendar = '';
			if(($stored_domain[$domain]['status'] != 'moderated') && ($stored_domain[$domain]['status'] != 'excluded')){
				if($stored_domain[$domain]['interval'] == 1){
					$domain_calendar = __('backup_interval_simple_type_' . $stored_domain[$domain]['interval_type'] . 'ly', 'eobackup');
				}
				elseif($stored_domain[$domain]['interval'] > 1){
					$domain_calendar = sprintf(__('Tous les %s %s', 'eobackup'), $stored_domain[$domain]['interval'], __($stored_domain[$domain]['interval_type'], 'eobackup'));
				}
				if($domain_calendar != ''){
					$domain_calendar .= '&nbsp;-&nbsp;' . sprintf(__('&Agrave; partir du %s', 'eobackup'), mysql2date('d F Y', $stored_domain[$domain]['start_date'], true));
				}
				else{
					$domain_calendar = __('Domaine en attente de configuration', 'eobackup');
				}
			}
			elseif($stored_domain[$domain]['status'] == 'excluded'){
				$domain_calendar = __('Ce domain est exclus des sauvegardes', 'eobackup');
			}
			elseif($stored_domain[$domain]['status'] == 'moderated'){
				$domain_calendar = __('Ce domain est mod&eacute;r&eacute;', 'eobackup');
			}
			$tableRowValue[] = array('class' => 'planification_label_cell ' . $domain_extra_class, 'value' => $domain_calendar);
			$tableRowValue[] = array('class' => 'remarks_label_cell', 'value' => $domain_remark);
			$tableRows[] = $tableRowValue;

			$line++;
		}

		$backup_type_style['standard'] = $backup_type_style['special'] = 'eobackup_hide';
		$stored_domain[$domain_definition->name]['backup_type'] = $stored_domain[$domain_definition->name]['backup_type'] != '' ? $stored_domain[$domain_definition->name]['backup_type'] : 'standard';
		$backup_type_style[$stored_domain[$domain_definition->name]['backup_type']] = '';

		return 
eobu_display::start_page(__('Liste des domaines pr&eacute;sents sur le serveur', 'eobackup'), '', '', '') . '
<form action="" method="post" >
	' . eobu_display::output_table($tableId, $tableTitles, $tableRows, $tableClasses, $tableRowsId, $tableSummary, false, '', $tableRowClass) . '
	<div class="domain_save_button_container" >
		' . __('Pour la s&eacute;lection', 'eobackup') . '&nbsp;:&nbsp;' . eobu_display::form_input_select('domain_list_selection_action', 'domain_list_selection_action', array('' => __('S&eacute;lectionner l\'action &agrave; effectuer', 'eobackup'),'plan' => __('Configurer', 'eobackup'), 'moderated' => __('D&eacute;sactiver la sauvegarde', 'eobackup'), 'excluded' => __('Exclure de la sauvegarde', 'eobackup'), 'valid' => __('Activer la sauvegarde', 'eobackup')), '', '', 'index') . '
		<div class="eobackup_option_container" >
			<div class="eobackup_hide" id="eobackup_plan_params" >
				<div class="domain_entry" >
					<div class="entry_label" ><label for="db_backup_type" >' . __('Type de sauvegarde de la base de donn&eacute;es', 'eobackup') . '</label></div>
					<div class="entry_input" >' . eobu_display::form_input_select('db_backup_type', 'db_backup_type', unserialize(EOBU_DB_BACKUP_TYPE), $stored_domain[$domain_definition->name]['db_backup_type'], '', 'index') . '</div>
				</div>
				' . eobu_domain::backup_plan($backup_type_style, $stored_domain[$domain_definition->name]['backup_type'], $stored_domain[$domain_definition->name]['interval_type'], $stored_domain[$domain_definition->name]['interval'], $stored_domain[$domain_definition->name]['start_date'], $stored_domain[$domain_definition->name]['backup_total_nb'], '_list') . '
			</div>
		</div>
		<br/><input type="submit" class="button-primary" name="save_main_list" id="save_main_list" value="' . __('Enregistrer', 'eobackup') . '" />
	</div>
</form>
' . eobu_display::end_page();
	}

	/**
	*	Define a template for the domain configuration
	*
	*	@param string $backup_type_style An array defining the style to apply to different configuration type
	*	@param string $domain_backup_type Allow to define what type of bakcup to launch for the current domain
	*	@param string $interval_type Allow to define the main interval type
	*	@param integer $interval The interval to associate with the interval_type
	*	@param string $start_date The date of the first backup
	*	@param integer $backup_total_nb The number of backup to save on disk before deletion
	*	@param string $place An random var allowing to set a single identifier for the domain planning
	*
	*	@return string The html output for domain planning
	*/
	function backup_plan($backup_type_style, $domain_backup_type = 'standard', $interval_type = 'day', $interval = '1', $start_date, $backup_total_nb, $place = ''){
		$start_date = ($start_date == '') ? current_time('mysql', 0) : $start_date;
		$backup_total_nb = ($backup_total_nb == '') ? eobu_options::get_specific_option('eobu_options', 'eobu_rotation_nb') : $backup_total_nb;

		return '<div class="domain_entry" >
			<div class="entry_label' . $place . '" ><label for="domain_backup_type" >' . __('Planification', 'eobackup') . '</label></div>
			<div class="entry_input' . $place . '" ><input type="hidden" name="domain_backup_type" id="domain_backup_type" value="' . $domain_backup_type . '" /></div>
			<div id="standard_backup" class="backup_type' . $place . ' ' . $backup_type_style['standard'] . '" >
				<div class="domain_simple_planification_params' . $place . '" >
					' . eobu_display::form_input_check('backup_interval_simple_type', 'backup_interval_simple_type', array('dayly', 'weekly', 'monthly', 'yearly'), $interval_type, 'radio') . '
				</div>
				<div class="domain_simple_planification_params' . $place . '" >
					' . eobu_display::form_input_check('backup_interval_simple_type', 'backup_interval_simple_type_special', 'special_simple_type', $interval_type, 'radio') . '<label for="backup_interval_simple_type_special" >' . __('Tous les', 'eobackup') . '</label><input type="text" name="interval" id="interval" value="' . $interval . '" />' . eobu_display::form_input_select('interval_type', 'interval_type', array('day' => __('jour(s)', 'eobackup'), 'week' => __('semaine(s)', 'eobackup'), 'month' => __('mois', 'eobackup'), 'year' => __('ann&eacute;e(s)', 'eobackup')), $interval_type, '', 'index') . '
				</div>
				<div class="domain_simple_planification_params' . $place . '" >
					<label for="start_date" >' . __('&Agrave; partir du', 'eobackup') . '</label><input type="text" name="start_date" id="start_date" value="' . $start_date . '" />
				</div>
				<div class="domain_simple_planification_params' . $place . '" >
					<label for="backup_total_nb" >' . __('Nombre de sauvegarde &agrave; conserver', 'eobackup') . '</label><input type="text" name="backup_total_nb" id="backup_total_nb" value="' . $backup_total_nb . '" /><br/>
					<span class="eobu_explanation" >' . __('0 pour conserver toutes les sauvegardes', 'eobackup') . '</span>
				</div>
			</div>
			<div id="special_backup" class="backup_type' . $place . ' ' . $backup_type_style['special'] . '" >
				&nbsp;
			</div>
		</div>';
	}

	/**
	*	Define a template for the form allowing to configure a domain planning
	*/
	function domain_planning_form($domain_id, $calendar_id, $domain_name, $db_backup_type, $backup_type_style, $domain_backup_type, $interval_type, $interval, $start_date, $backup_total_nb){
		return '
		<form action="" method="post" class="domain_form" >
			<input type="hidden" name="domain_id" id="domain_id" value="' . $domain_id . '" /> 
			<input type="hidden" name="calendar_id" id="calendar_id" value="' . $calendar_id . '" /> 
			<div class="domain_entry" >
				<div class="entry_label" ><label for="domain_name" >' . __('Identifiant du domaine sur le serveur', 'eobackup') . '</label></div>
				<div class="entry_input" ><input type="text" readonly="readonly" name="domain_name" id="domain_name" value="' . $domain_name . '" /></div>
			</div>
			<div class="domain_entry" >
				<div class="entry_label" ><label for="db_backup_type" >' . __('Type de sauvegarde de la base de donn&eacute;es', 'eobackup') . '</label></div>
				<div class="entry_input" >' . eobu_display::form_input_select('db_backup_type', 'db_backup_type', unserialize(EOBU_DB_BACKUP_TYPE), $db_backup_type, '', 'index') . '</div>
			</div>
			' . eobu_domain::backup_plan($backup_type_style, $domain_backup_type, $interval_type, $interval, $start_date, $backup_total_nb) . '
			<div class="clear" >
				<input type="submit" name="save_domain_backup_config" id="save_domain_backup_config" value="' . __('Sauvegarder la configuration du domaine', 'eobackup') . '" 	class="button-primary" />
			</div>
			<script type="text/javascript" >
				eobackup(document).ready(function(){
					jQuery("#start_date").val("' . $start_date . '");
				});
			</script>
		</form>';
	}

	/**
	*	Return the list of existing domain saved into database
	*
	*	@return object $domain_list_saved The list of domain saved into database from the existing list present on server
	*/
	function get_saved_domain_list($query_argument = ""){
		global $wpdb;
		// $query = $wpdb->prepare("
// SELECT DOMAIN.id as DOM_ID, DOMAIN.status AS DOM_STATUS, DOMAIN.name, DOMAIN.last_backup, DOMAIN.next_backup,
	// DOMAIN_CALENDAR.*,
	// HISTORY.start AS LAST_START, HISTORY.end AS LAST_END, HISTORY.file_status, HISTORY.db_status
// FROM " . EOBU_DBT_DOMAIN . " AS DOMAIN
	// LEFT JOIN " . EOBU_DBT_CALENDAR . " AS DOMAIN_CALENDAR ON ((DOMAIN_CALENDAR.domain_id = DOMAIN.id) AND (DOMAIN_CALENDAR.status = 'valid'))
	// LEFT JOIN " . EOBU_DBT_DOMAIN_HISTORY . " AS HISTORY ON ((HISTORY.domain_id = DOMAIN.id) AND (HISTORY.status = 'valid'))
// WHERE 1 " . $query_argument . "
// ORDER BY HISTORY.creation_date DESC");
		$query = $wpdb->prepare("
SELECT DOMAIN.id as DOM_ID, DOMAIN.status AS DOM_STATUS, DOMAIN.name, DOMAIN.last_backup, DOMAIN.next_backup,
	DOMAIN_CALENDAR.*
FROM " . EOBU_DBT_DOMAIN . " AS DOMAIN
	LEFT JOIN " . EOBU_DBT_CALENDAR . " AS DOMAIN_CALENDAR ON ((DOMAIN_CALENDAR.domain_id = DOMAIN.id) AND (DOMAIN_CALENDAR.status = 'valid'))
WHERE 1 " . $query_argument);
		$domain_list_saved = $wpdb->get_results($query);

		return $domain_list_saved;
	}

	/**
	*	Allows to get the list of domain to backup for the current day
	*
	* @return array $domain_list_saved The domain list to backup for the current day
	*/
	function get_domain_to_backup(){
		global $wpdb;
		$domain_list_saved = '';

		$domain_list_to_do = eobu_domain::get_saved_domain_list("
	AND DOMAIN.status = 'valid'");

		foreach($domain_list_to_do as $domain_infos){
			if($domain_infos->start_date <= date('Y-m-d')){
				$query = $wpdb->prepare("
SELECT HISTORY.start, DATE_FORMAT(DATE_ADD(HISTORY.start, INTERVAL " . $domain_infos->interval . " " . strtoupper($domain_infos->interval_type) . ") , %s) AS NEXT_START_DATE,
HISTORY.end, DATE_FORMAT(DATE_ADD(HISTORY.end, INTERVAL " . $domain_infos->interval . " " . strtoupper($domain_infos->interval_type) . ") , %s) AS NEXT_END_DATE, HISTORY.file_status, HISTORY.db_status, HISTORY.parent_backup_id
FROM " . EOBU_DBT_DOMAIN_HISTORY . " AS HISTORY
WHERE HISTORY.domain_id = %d
	AND HISTORY.status = 'valid'
ORDER BY HISTORY.start DESC
LIMIT 1", '%Y-%m-%d', '%Y-%m-%d', $domain_infos->DOM_ID);
				$domain_last_infos = $wpdb->get_row($query);

				if(!isset($domain_last_infos->NEXT_START_DATE) || ($domain_last_infos->NEXT_START_DATE == date('Y-m-d')) || (eobu_options::get_specific_option('eobu_options', 'eobu_allow_multiple_backup_one_day') == 'oui')){
					$domain_infos->backup_to_do = 'yes';
				}
				$domain_list_saved[] = $domain_infos;
			}
		}

		return $domain_list_saved;
	}

	/**
	*
	*/
	function backup_domain_list(){
		/*	Display table with existint domain on current server	*/
		$tableId = 'domain_to_backup_today';
		$tableSummary = __('Liste des domaines &agrave; sauvegarder aujourd\'hui', 'eobackup');
		$tableTitles = array();
		$tableTitles[] = __('Domaine', 'eobackup');
		$tableTitles[] = __('Remarque.', 'eobackup');
		$tableClasses = array();
		$tableClasses[] = 'eobu_domain_column';
		$tableClasses[] = 'eobu_domain_remark_column';
		$tableRowClass = array();
		$line = 0;
		/*	Generate output for domain list to backup for the current day	*/
		$get_domain_list_to_backup_today = eobu_domain::get_domain_to_backup();
		if(count($get_domain_list_to_backup_today) > 0){
			foreach($get_domain_list_to_backup_today as $domain){
				if(isset($domain->backup_to_do) && ($domain->backup_to_do = 'yes')){
					$tableRowsId[$line] = 'domain_' . $index . '_' . sanitize_title($domain->name);

					unset($tableRowValue);
					$tableRowValue[] = array('class' => 'eobu_domain_label_cell', 'value' => $domain->name);
					$deletion_list_for_domain_today = $domain_remark = '';
					if($domain->last_backup != '0000-00-00'){
						$domain_remark = sprintf(__('Derni&egrave;re sauvegarde le %s. <a href="%s" >Voir le d&eacute;tail</a>', 'eobackup'), mysql2date('d F Y', $domain->last_backup, true), admin_url('admin.php?page=' . EOBU_SLUG_DOMAIN . '&amp;domain_to_edit=' . $domain->name));
					}
					// $deletion_list_for_domain_today = '<br/>' . sprintf(__('', 'eobackup'));
					$tableRowValue[] = array('class' => 'eobu_domain_remark_cell', 'value' => $domain_remark . $deletion_list_for_domain_today);
					$tableRows[] = $tableRowValue;

					$line++;
				}
				else{
					$tableRowsId[$line] = 'domain_' . $index . '_' . sanitize_title($domain->name);

					unset($tableRowValue);
					$tableRowValue[] = array('class' => 'eobu_domain_label_cell', 'value' => $domain->name);
					$deletion_list_for_domain_today = $domain_remark = '';
					if(($domain->last_backup != '0000-00-00') && ($domain->last_backup == date_i18n('Y-m-d', time()))){
						$domain_remark = sprintf(__('Sauvegarde effectu&eacute;e. <a href="%s" >Voir le d&eacute;tail</a>', 'eobackup'), admin_url('admin.php?page=' . EOBU_SLUG_DOMAIN . '&amp;domain_to_edit=' . $domain->name));
					}
					$tableRowValue[] = array('class' => 'eobu_domain_remark_cell', 'value' => $domain_remark . $deletion_list_for_domain_today);
					$tableRows[] = $tableRowValue;

					$line++;
				}
			}
			$page_content .= eobu_display::output_table($tableId, $tableTitles, $tableRows, $tableClasses, $tableRowsId, $tableSummary, false, '', $tableRowClass);
		}
		else{
			$page_content .= __('Il n\'y a aucun domaine &agrave; sauvegarder aujourd\'hui', 'eobackup');
		}

		return $page_content;
	}


	/**
	*	Save planning information for a domain
	*
	*	@param array $domain_informations_to_save Informations to set for the domain
	*	@param string $method Allows to define the method return the result. Could be empty for outputting a string or "return" in order to return the result for future use
	*
	*	@return mixed Output a result or return a wordpress query result
	*/
	function save_domain_config($domain_informations_to_save, $method = ''){
		global $wpdb;

		$domain_informations = array();

		/*	In case that the domain has not been created into database	*/
		if($domain_informations_to_save['domain_id'] <= 0){
			$new_domain_informations = array();
			$new_domain_informations['status'] = 'valid';
			$new_domain_informations['creation_date'] = current_time('mysql', 0);
			$new_domain_informations['name'] = $domain_informations_to_save['domain_name'];
			$wpdb->insert(EOBU_DBT_DOMAIN, $new_domain_informations, array('%s', '%s', '%s'));
			$domain_informations['domain_id'] = $wpdb->insert_id;

			unset($new_domain_informations);
		}
		else{
			$domain_informations['domain_id'] = $domain_informations_to_save['domain_id'];
		}

		if($domain_informations_to_save['calendar_id'] <= 0){
			$new_calendar_informations = array();
			$new_calendar_informations['status'] = 'valid';
			$new_calendar_informations['creation_date'] = current_time('mysql', 0);
			$new_calendar_informations['domain_id'] = $domain_informations['domain_id'];
			$wpdb->insert(EOBU_DBT_CALENDAR, $new_calendar_informations, array('%s', '%s', '%d'));
			unset($new_calendar_informations);

			$calendar_id = $wpdb->insert_id;
		}
		else{
			$calendar_id = $domain_informations_to_save['calendar_id'];
		}

		/*	Update informations about domain planification	*/
		$domain_informations['backup_type'] = $domain_informations_to_save['domain_backup_type'];
		$domain_informations['db_backup_type'] = $domain_informations_to_save['db_backup_type'];
		$domain_informations['backup_total_nb'] = $domain_informations_to_save['backup_total_nb'];
		/*	Calcul the planification	*/
		if($domain_informations['backup_type'] == 'standard'){
			if(substr($domain_informations_to_save['backup_interval_simple_type'], -2) == 'ly'){
				$domain_informations['interval_type'] = substr($domain_informations_to_save['backup_interval_simple_type'], 0, -2);
				$domain_informations['interval'] = 1;
			}
			else{
				$domain_informations['interval_type'] = $domain_informations_to_save['interval_type'];
				$domain_informations['interval'] = $domain_informations_to_save['interval'];
			}
		}
		$domain_informations['start_date'] = ($domain_informations_to_save['start_date'] != '') ? $domain_informations_to_save['start_date'] : current_time('mysql', 0);

		$result = $wpdb->update(EOBU_DBT_CALENDAR, $domain_informations, array('id' => $calendar_id), '%s', '%d');

		if($method == 'return'){
			return $result;
		}
		if(($result == 1) || ($result == 0)){
			echo '<div class="eobackup_page_message updated" >' . __('La configuration du domaine a &eacute;t&eacute; enregistr&eacute;e avec succ&eacute;s', 'eobackup') . '</div>';
		}
		elseif(!$result){
			echo '<div class="eobackup_page_message error" >' . __('Une erreur est survenue lors de l\'enregistrement de la configuration du domaine', 'eobackup') . '</div>';
		}
	}
	/**
	*	Save the domain list to into database
	*
	*	@return string An output message for user to know if save has been successfully done
	*/
	function save_domain_list(){
		global $wpdb;

		if(isset($_POST['domain_to_save']) && is_array($_POST['domain_to_save'])){

			$domain_to_update_status = $domain_updated_status = 0;
			foreach($_POST['domain_to_save'] as $domain_name => $domain_infos){
				if(isset($domain_infos['name'])){
					$domain_to_update_status++;
					if(isset($_POST['domain_list_selection_action']) && (in_array($_POST['domain_list_selection_action'], array('valid', 'moderated', 'deleted', 'excluded')))){
						if(isset($domain_infos['id']) && ($domain_infos['id'] > 0)){
							$domain_updated_status += $wpdb->update(EOBU_DBT_DOMAIN, array('status' => $_POST['domain_list_selection_action'], 'last_update_date' => current_time('mysql', 0)), array('name' => $domain_name), array('%s', '%s'), array('%s'));
						}
						else{
							$domain_updated_status += $wpdb->insert(EOBU_DBT_DOMAIN, array('status' => $_POST['domain_list_selection_action'], 'creation_date' => current_time('mysql', 0), 'name' => $domain_name));
						}
					}
					elseif(isset($_POST['domain_list_selection_action']) && ($_POST['domain_list_selection_action'] == 'plan')){
						$domain_informations_to_save['domain_id'] = (isset($domain_infos['id']) && ($domain_infos['id'] > 0)) ? $domain_infos['id'] : 0;
						$domain_informations_to_save['domain_name'] = $domain_name;

						$domain_informations_to_save['calendar_id'] = (isset($domain_infos['calendar_id']) && ($domain_infos['calendar_id'] > 0)) ? $domain_infos['calendar_id'] : 0;

						$domain_informations_to_save['domain_backup_type'] = $_POST['domain_backup_type'];
						$domain_informations_to_save['db_backup_type'] = 'dump';

						$domain_informations_to_save['backup_interval_simple_type'] = $_POST['backup_interval_simple_type'];
						$domain_informations_to_save['interval_type'] = $_POST['interval_type'];
						$domain_informations_to_save['interval'] = $_POST['interval'];
						$domain_informations_to_save['start_date'] = $_POST['start_date'];
						$domain_informations_to_save['backup_total_nb'] = $_POST['backup_total_nb'];

						$domain_updated_status +=  eobu_domain::save_domain_config($domain_informations_to_save, 'return');
					}
					else{
						echo '<div class="eobackup_page_message error" >' . __('Vous n\'avez s&eacute;lectionnez aucune action pour les domaines s&eacute;lectionn&eacute;s', 'eobackup') . '</div>';
					}
				}
			}

			if(($domain_to_update_status > 0) && ($domain_updated_status > 0)){
				if($domain_to_update_status == $domain_updated_status){
					echo '<div class="eobackup_page_message updated" >' . __('Les sauvegardes des domaines s&eacute;lectionn&eacute; ont bien &eacute;t&eacute; mis &agrave; jour', 'eobackup') . '</div>';
				}
				else{
					echo '<div class="eobackup_page_message error" >' . __('Des erreurs sont survenues lors de la mise &agrave; jour des sauvegardes des domaines s&eacute;lectionn&eacute;s', 'eobackup') . 'Code.S-' . $domain_to_update_status . 'R-' . $domain_updated_status . '</div>';
				}
			}
			elseif($domain_to_update_status <= 0){
				echo '<div class="eobackup_page_message error" >' . __('Vous n\'avez s&eacute;lectionnez aucun domaine &agrave; mettre &agrave; jour', 'eobackup') . '</div>';
			}

		}
		else{
			echo '<div class="eobackup_page_message error" >' . __('Vous n\'avez s&eacute;lectionnez aucun domaine &agrave; mettre &agrave; jour', 'eobackup') . '</div>';
		}
	}

}