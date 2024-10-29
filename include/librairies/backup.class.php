<?php
/**
*	Backup access
* 
* Define the different method allowing to acces backup
* @author Eoxia <dev@eoxia.com>
* @version 0.1
* @package backup_release_ovh
* @subpackage librairies
*/


/**
* Define the different method allowing to acces backup
* @author Eoxia <dev@eoxia.com>
* @version 0.1
* @package backup_release_ovh
* @subpackage librairies
*/
class eobu_backup
{

	/**
	*	Get history for the general backup
	*
	*	@param integer $domain_identifier Define the identifier to get. Could be a domain id or a general backup id
	*	@param string $domain_identifier_type Define the type of identifier to restrict result. Could be a domain id or a general backup id
	*
	*	@return string $domain_history An html output for element history
	*/
	function get_backup_history($backup_date = '', $backup_date_operator = '=', $not_empty = true, $current_page = 0){
		global $wpdb;
		$eobu_per_page_element_nb = eobu_options::get_specific_option('eobu_options', 'eobu_per_page_element_nb');

		$conditions = array();
		$backup_conditions = '';
		if($backup_date != ''){
			$conditions[] = '%Y-%m-%d';
			$conditions[] = $backup_date;
			$backup_conditions .= "
	AND DATE_FORMAT(HISTORY.start, %s) " . $backup_date_operator . " %s";
		}

		if($not_empty){
			$conditions[] = 'empty';
			$conditions[] = 'empty';
			$conditions[] = 'empty';
			$backup_conditions .= "
	AND HISTORY.file_status != %s
	AND HISTORY.db_status != %s
	AND HISTORY.log != %s";
		}

		$query = $wpdb->prepare("
SELECT COUNT(HISTORY.id)
FROM " . EOBU_DBT_DOMAIN_HISTORY . " AS HISTORY
WHERE HISTORY.domain_id = 0
	AND HISTORY.parent_backup_id = 0 
	AND HISTORY.status = 'valid '" . $backup_conditions . "
ORDER BY HISTORY.creation_date DESC", $conditions);
		$backup_number = $wpdb->get_var($query);
		$pagination = '';
		if($backup_number > $eobu_per_page_element_nb){
			$pagination = '<div class="general_backup_pagination" >';
			for($i = 0; $i < $backup_number / $eobu_per_page_element_nb ; $i++){
				$is_current_page = ($i == $current_page) ? 'class="current"' : '';
				$pagination .= '<span ' . $is_current_page . ' id="page_number_' . $i . '" >' . ($i + 1) . '</span>&nbsp;';
			}
			$pagination .= '</div>';
		}

		$query = $wpdb->prepare("
SELECT HISTORY.*
FROM " . EOBU_DBT_DOMAIN_HISTORY . " AS HISTORY
WHERE HISTORY.domain_id = 0
	AND HISTORY.parent_backup_id = 0 
	AND HISTORY.status = 'valid '" . $backup_conditions . "
ORDER BY HISTORY.creation_date DESC
LIMIT " . ($current_page * $eobu_per_page_element_nb) . ", " . $eobu_per_page_element_nb, $conditions);
		$backup_list = $wpdb->get_results($query);
		if(count($backup_list) > 0){
			/*	Display table with existing domain on current server	*/
			$tableId = 'general_backup_history';
			$tableSummary = __('Sauvegardes ant&eacute;rieures', 'eobackup');
			$tableTitles = array();
			$tableTitles[] = '';
			$tableTitles[] = __('Date', 'eobackup');
			$tableTitles[] = __('Informations sur la sauvegard&eacute;', 'eobackup');
			$tableClasses = array();
			$tableClasses[] = 'eobu_domain_column';
			$tableClasses[] = 'eobu_general_backup_date_column';
			$tableClasses[] = 'eobu_backup_informations_column';
			$tableRowClass = array();
			$line = 0;
			/*	Generate output for domain list to backup for the current day	*/
			foreach($backup_list as $backup){
				$tableRowsId[$line] = 'general_backup_' . $backup->id;
				unset($tableRowValue);
				if(($backup->file_status == 'empty') && ($backup->db_status == 'empty') && ($backup->log == 'empty')){
					$tableRowValue[] = array('class' => 'eobu_domain_cell', 'value' => '&nbsp;');
					$tableRowValue[] = array('class' => 'eobu_general_backup_date_cell', 'value' => __('D&eacute;but', 'eobackup') . '<br/>&nbsp;' . mysql2date(__('\L\e d F Y \&\a\g\r\a\v\e\; H:i:s', 'eobackup'), $backup->start, true) . '<br/>' . __('Fin', 'eobackup') . '<br/>&nbsp;' . mysql2date(__('\L\e d F Y \&\a\g\r\a\v\e\; H:i:s', 'eobackup'), $backup->end, true));
					$tableRowValue[] = array('class' => 'eobu_domain_list_cell', 'value' => $backup->result);

					$tableRows[] = $tableRowValue;
					$line++;
				}
				else{
					$tableRowValue[] = array('class' => 'eobu_domain_cell', 'value' => '<img src="' . EO_BU_MEDIA_PLUGIN_URL . 'add.png" alt="' . __('Voir les d&eacute;tails de la sauvegarde', 'eobackup') . '" title="' . __('Voir les d&eacute;tails de la sauvegarde', 'eobackup') . '" />');
					$tableRowValue[] = array('class' => 'eobu_general_backup_date_cell', 'value' => __('D&eacute;but', 'eobackup') . '<br/>&nbsp;' . mysql2date(__('\L\e d F Y \&\a\g\r\a\v\e\; H:i:s', 'eobackup'), $backup->start, true) . '<br/>' . __('Fin', 'eobackup') . '<br/>&nbsp;' . mysql2date(__('\L\e d F Y \&\a\g\r\a\v\e\; H:i:s', 'eobackup'), $backup->end, true));
					$list_content = '';
					if(is_file($backup->result)){
						$list_file = fopen($backup->result, 'r');
						$list_content = fread($list_file, filesize($backup->result));
						fclose($list_file);
					}
					$tableRowValue[] = array('class' => 'eobu_domain_list_cell', 'value' => '<input type="button" class="button-secondary backup_log_button alignleft" value="' . __('Voir les logs', 'eobackup') . '" id="view_backup_log' . $backup->id . '" name="view_backup_log" /><br class="clear" /><span class="ui-icon view_details open_details" >&nbsp;</span>' . $backup->result . '<div class="detail_viewer eobackup_hide" >' . trim(nl2br($list_content)) . '</div>');

					$tableRows[] = $tableRowValue;
					$line++;

					/*	Generate output for domain list to backup for the current day	*/
					$tableRowsId[$line] = 'general_backup__details' . $backup->id;
					unset($tableRowValue);
					$tableRowValue[] = array('class' => 'eobu_domain_details_td eobackup_hide', 'value' => eobu_backup::get_domain_history($backup->id, 'HISTORY.parent_backup_id', 'backup'), 'option' => ' colspan="3" ');
					$tableRows[] = $tableRowValue;
					$line++;
				}
			}

			return $pagination . eobu_display::output_table($tableId, $tableTitles, $tableRows, $tableClasses, $tableRowsId, $tableSummary, false, '', $tableRowClass) . $pagination . '
<script type="text/javascript" >
	eobackup(document).ready(function(){
		jQuery(".general_backup_pagination span").click(function(){
			jQuery("#history_div").html(jQuery("#loading_picture").html());
			jQuery("#history_div").load(EO_BU_AJAX_FILE,{
				"action": "general_backup_history",
				"backup_date" : "' . $backup_date . '",
				"backup_date_operator" : "' . $backup_date_operator . '",
				"not_empty" : "' . $not_empty . '",
				"page": jQuery(this).attr("id").replace("page_number_", "")
			});
		});

		jQuery(".view_details").click(function(){
			if(jQuery(this).hasClass("open_details")){
				jQuery(this).next("div .detail_viewer").show();
				jQuery(this).removeClass("open_details");
				jQuery(this).addClass("close_details");
			}
			else if(jQuery(this).hasClass("close_details")){
				jQuery(this).next("div .detail_viewer").hide();
				jQuery(this).removeClass("close_details");
				jQuery(this).addClass("open_details");
			}
		});

		jQuery(".eobu_domain_cell img").click(function(){
			var backup_id = jQuery(this).parent("td").parent("tr").attr("id").replace("general_backup_", "");
			if(jQuery(this).attr("src") == "' . EO_BU_MEDIA_PLUGIN_URL . 'add.png"){
				jQuery("#general_backup__details" + backup_id + " .eobu_domain_details_td").show();
				jQuery(this).attr("src", "' . EO_BU_MEDIA_PLUGIN_URL . 'delete.png");
			}
			else if(jQuery(this).attr("src") == "' . EO_BU_MEDIA_PLUGIN_URL . 'delete.png"){
				jQuery("#general_backup__details" + backup_id + " .eobu_domain_details_td").hide();
				jQuery(this).attr("src", "' . EO_BU_MEDIA_PLUGIN_URL . 'add.png");
			}
		});
	});
</script>';
		}
		else{
			return __('Il n\'y a aucune sauvegarde pour le moment', 'eobackup');
		}
	}

	/**
	*	Get history for a given domain id or a general backup
	*
	*	@param integer $domain_identifier Define the identifier to get. Could be a domain id or a general backup id
	*	@param string $domain_identifier_type Define the type of identifier to restrict result. Could be a domain id or a general backup id
	*
	*	@return string $domain_history An html output for element history
	*/
	function get_domain_history($domain_identifier, $domain_identifier_type = 'HISTORY.domain_id', $history_type = 'backup', $current_page = 0){
		global $wpdb;
		$domain_history = '';
		$eobu_per_page_element_nb = eobu_options::get_specific_option('eobu_options', 'eobu_per_page_element_nb');

		$query = $wpdb->prepare("
SELECT COUNT(HISTORY.id)
FROM " . EOBU_DBT_DOMAIN_HISTORY . " AS HISTORY
	INNER JOIN " . EOBU_DBT_DOMAIN . " AS DOMAIN ON ((DOMAIN.id = HISTORY.domain_id))
WHERE " . $domain_identifier_type . " = %d
	AND HISTORY.history_type = %s", $domain_identifier, $history_type);
		$backup_number = $wpdb->get_var($query);
		$pagination = '';
		if($backup_number > $eobu_per_page_element_nb){
			$pagination = '<div class="backup_pagination" >';
			for($i = 0; $i < $backup_number / $eobu_per_page_element_nb ; $i++){
				$is_current_page = ($i == $current_page) ? 'class="current"' : '';
				$pagination .= '<span ' . $is_current_page . ' id="page_number_' . $i . '" >' . ($i + 1) . '</span>&nbsp;';
			}
			$pagination .= '</div>';
		}

		$query = $wpdb->prepare("
SELECT HISTORY.*, DOMAIN.name
FROM " . EOBU_DBT_DOMAIN_HISTORY . " AS HISTORY
	INNER JOIN " . EOBU_DBT_DOMAIN . " AS DOMAIN ON ((DOMAIN.id = HISTORY.domain_id))
WHERE " . $domain_identifier_type . " = %d
	AND HISTORY.history_type = %s
ORDER BY start DESC
LIMIT %d, %d", $domain_identifier, $history_type, ($current_page * $eobu_per_page_element_nb), $eobu_per_page_element_nb);
		$domain_backup_list = $wpdb->get_results($query);
		if(count($domain_backup_list) > 0){
			/*	Display table with existint domain on current server	*/
			$tableId = 'domain_backup_history';
			$tableSummary = __('Liste des sauvegardes pour le domaine en cours d\'&eacute;dition', 'eobackup');
			$tableTitles = array();
			$tableClasses = array();
			$tableRowClass = array();
			if($domain_identifier_type == 'HISTORY.parent_backup_id'){
				$tableTitles[] = __('Domaine', 'eobackup');
				$tableClasses[] = 'eobu_domain_backup_name_column';
			}
			$tableTitles[] = __('Date', 'eobackup');
			if($history_type == 'restore'){
				$tableTitles[] = __('Utilisateur', 'eobackup');
				$tableTitles[] = __('Logs', 'eobackup');
			}
			if($history_type == 'backup'){
				$tableTitles[] = __('Fichiers', 'eobackup');
				$tableTitles[] = __('Base de donn&eacute;es', 'eobackup');
				$tableTitles[] = __('Informations', 'eobackup');
			}

			if($history_type == 'backup'){
				$tableTitles[] = __('Actions', 'eobackup');
			}
			if($history_type == 'restore'){
				$tableClasses[] = 'eobu_domain_backup_restoration_date_column';
				$tableClasses[] = 'eobu_domain_backup_restoration_user_column';
			}
			if($history_type == 'backup'){
				$tableClasses[] = 'eobu_domain_backup_date_column';
				$tableClasses[] = 'eobu_domain_backup_file_status_column';
				$tableClasses[] = 'eobu_domain_backup_db_status_column';
				$tableClasses[] = 'eobu_domain_backup_dir_column';
			}

			if($history_type == 'backup'){
				$tableClasses[] = 'eobu_domain_backup_action_column';
			}

			$line = 0;
			foreach($domain_backup_list as $domain_history_infos){
				$tableRowsId[$line] = 'domain_' . $domain_history_infos->id . '_' . sanitize_title($domain_identifier);
				/*	Get log informations	*/
				$complete_domain_log = unserialize($domain_history_infos->log);

				unset($tableRowValue);
				if($domain_identifier_type == 'HISTORY.parent_backup_id'){
					$tableRowValue[] = array('class' => 'eobu_domain_backup_name_cell', 'value' => $domain_history_infos->name);
				}
				$tableRowValue[] = array('class' => 'eobu_domain_backup_date_cell', 'value' => __('D&eacute;but', 'eobackup') . '<br/>&nbsp;' . mysql2date('d F Y \&\a\g\r\a\v\e\; H:i:s', $domain_history_infos->start, true) . '<br/>' . __('Fin', 'eobackup') . '<br/>&nbsp;' . mysql2date('d F Y \&\a\g\r\a\v\e\; H:i:s', $domain_history_infos->end, true));
				if($history_type == 'restore'){
					$user_info = get_userdata($complete_domain_log[0]);
					$user_lastname = (isset($user_info->user_lastname) && ($user_info->user_lastname != '')) ? $user_info->user_lastname : '';
					$user_firstname = (isset($user_info->user_firstname) && ($user_info->user_firstname != '')) ? $user_info->user_firstname : $user_info->user_nicename;
					$tableRowValue[] = array('class' => 'eobu_domain_backup_restoration_user_cell', 'value' => 'U' . $complete_domain_log[0] . '&nbsp;-&nbsp;' . $user_lastname  . ' ' . $user_firstname);
				}

				$eobu_directories_REPBACKUP = eobu_options::get_specific_option('eobu_options', 'eobu_directories_REPBACKUP');
				if(($domain_history_infos->status == 'deleted') && !is_dir($eobu_directories_REPBACKUP . '/' . $domain_history_infos->result)){
					$tableRowValue[] = array('option' => ' colspan="4" ', 'class' => 'eobu_domain_backup_deleted_cell', 'value' => __('Cette sauvegarde a &eacute;t&eacute; supprim&eacute;e selon les r&eacute;glages de rotation des sauvegardes', 'eobackup'));
				}
				else{
					if($history_type == 'backup'){
						$tableRowValue[] = array('class' => 'eobu_domain_backup_file_status_cell eobu_domain_backup_file_status_' . strtolower($domain_history_infos->file_status) . '_cell', 'value' => $domain_history_infos->file_status);
						$tableRowValue[] = array('class' => 'eobu_domain_backup_db_status_cell eobu_domain_backup_db_status_' . strtolower($domain_history_infos->db_status) . '_cell', 'value' => $domain_history_infos->db_status);

						/*	Format result output	*/
						$backup_dir = '';
						$result_more_class = '';
						if(is_dir($eobu_directories_REPBACKUP . '/' . $domain_history_infos->result)){
							unset($exec_result);
							exec('du -hs ' . $eobu_directories_REPBACKUP . '/' . $domain_history_infos->result . '/*', $exec_result);
							$current_script_weight = explode('	', $exec_result[1]);
							$current_db_weight = explode('	', $exec_result[0]);

							$weight_log = explode("
", $complete_domain_log[3]);
							$ori_script_dir = explode(' ', $weight_log[0]);
							$bu_script_dir = explode(' ', $weight_log[1]);
							$ori_db_dir = explode(' ', $weight_log[2]);
							$bu_db_dir = explode(' ', $weight_log[3]);
							$backup_dir .= '
<br class="clear" />
<table summary="' . __('Tailles des diff&eacute;rents dossiers', 'eobackup') . '" class="alignright eobu_table_no_border" >
	<tr>
		<th>&nbsp;</th>
		<th>' . __('Original', 'eobackup') . '</th>
		<th>' . __('Sauvegarde', 'eobackup') . '</th>
	</tr>
	<tr>
		<td>' . __('Fichiers', 'eobackup') . '</td>
		<td>' . $ori_script_dir[0] . '</td>
		<td>' . $bu_script_dir[0] . (($current_script_weight[0] != $bu_script_dir[0]) ? '(' . $current_script_weight[0] . ')' : '') . '</td>
	</tr>
	<tr>
		<td>' . __('Base de donn&eacute;es', 'eobackup') . '</td>
		<td>' . $ori_db_dir[0] . '</td>
		<td>' . $bu_db_dir[0] . (($current_db_weight[0] != $bu_db_dir[0]) ? '(' . $current_db_weight[0] . ')' : '') . '</td>
	</tr>
</table>';

							$eobu_restoration_infos = eobu_options::get_specific_option('eobu_options', array('eobu_restoration_domain_name', 'eobu_restoration_db_name', 'eobu_restoration_db_user', 'eobu_restoration_db_pass'));
							if(!empty($eobu_restoration_infos['eobu_restoration_domain_name']) && !empty($eobu_restoration_infos['eobu_restoration_db_name']) && !empty($eobu_restoration_infos['eobu_restoration_db_user']) && !empty($eobu_restoration_infos['eobu_restoration_db_pass'])){
								$wp_config_file = $eobu_directories_REPBACKUP . '/' . $domain_history_infos->result . '/script/' . $domain_history_infos->name . '/www/wp-config.php';
								if(!is_file($wp_config_file)){
									$backup_restoration = __('La restauration pour ce type de site est indisponible pour le moment', 'eobackup');
								}
								else{
									$backup_restoration = '<div id="restoration_button_container_' . $domain_history_infos->id . '" ><input type="button" class="button-primary backup_restoration_button" value="' . __('Restaurer cette sauvegarde', 'eobackup') . '" id="restore_backup_' . $domain_history_infos->id . '" name="restore_backup" /></div>';
								}
							}
							else{
								$backup_restoration = sprintf(__('Sauvegarde serveur : Certaines configurations sont manquantes pour effectuer les restaurations. %s', 'eobackup'), '<a href="' . admin_url("options-general.php?page=" . EOBU_SLUG_OPTION) . '#eobu_options_restoration" >' . __('configurer ces variables (Section restauration)', 'eobackup') . '</a>');
							}
							if($domain_history_infos->status == 'deleted'){
								$restoration_more_class = 'eobu_domain_backup_already_existing_dir_cell';
								$backup_restoration = __('Cette sauvegarde aurait du &ecirc;tre supprim&eacute;e selon les r&eacute;glages de rotation des sauvegardes mais le dossier est toujours pr&eacute;sent', 'eobackup');
							}
						}
						else{
							$backup_dir .= '<br/>' . __('Le dossier n\'existe plus sur le serveur', 'eobackup');
							$result_more_class = 'eobu_domain_backup_unexisting_dir_cell';
							$backup_restoration = __('Cette sauvegarde ne peut &ecirc;tre restaur&eacute;e', 'eobackup');
						}
						$start_average_load = explode(" ", $complete_domain_log[0]);
						$end_average_load = explode(" ", $complete_domain_log[2]);
						$backup_dir .= '
<hr class="clear" />
<input type="button" class="button-secondary backup_log_button alignleft" value="' . __('Voir les logs', 'eobackup') . '" id="view_backup_log' . $domain_history_infos->id . '" name="view_backup_log" />
<table summary="' . __('Charges du serveur lors de la sauvegarde', 'eobackup') . '" class="alignright eobu_table_no_border" >
	<tr>
		<th colspan="4" >' . __('Charges du serveur', 'eobackup') . '</th>
	</tr>
	<tr>
		<th>&nbsp;</th>
		<th>' . __('1 minutes', 'eobackup') . '</th>
		<th>' . __('5 minutes', 'eobackup') . '</th>
		<th>' . __('15 minutes', 'eobackup') . '</th>
	</tr>
	<tr>
		<td>' . __('Avant', 'eobackup') . '</td>
		<td>' . $start_average_load[0] . '</td>
		<td>' . $start_average_load[1] . '</td>
		<td>' . $start_average_load[2] . '</td>
	</tr>
	<tr>
		<td>' . __('Apr&egrave;s', 'eobackup') . '</td>
		<td>' . $end_average_load[0] . '</td>
		<td>' . $end_average_load[1] . '</td>
		<td>' . $end_average_load[2] . '</td>
	</tr>
</table>';
						$tableRowValue[] = array('class' => 'eobu_domain_backup_dir_cell ' . $result_more_class, 'value' => $eobu_directories_REPBACKUP . '/<span class="eobu_strong" >' . $domain_history_infos->result . '</span>' . $backup_dir);
					}
					
					/*	Format log output	*/
					$domain_log = nl2br($complete_domain_log[1]);
					$start_substr_length = (substr($domain_log, 0, 4) == '<br>') ? 4 : ((substr($domain_log, 0, 5) == '<br/>') || (substr($domain_log, 0, 5) == '<br >') ? 5 :((substr($domain_log, 0, 6) == '<br />') ? 6 : 0));
					$domain_log = substr($domain_log, $start_substr_length);
					if($history_type == 'restore'){
						$tableRowValue[] = array('class' => 'eobu_domain_backup_log_cell', 'value' => '<div class="domain_backup_log" >' . $domain_log. '</div>');
					}

					if($history_type == 'backup'){
						$tableRowValue[] = array('class' => 'eobu_domain_backup_action_cell ' . $restoration_more_class, 'value' => $backup_restoration);
					}
				}

				$tableRows[] = $tableRowValue;

				$line++;
			}

			$domain_history .= $pagination . eobu_display::output_table($tableId, $tableTitles, $tableRows, $tableClasses, $tableRowsId, $tableSummary, false, '', $tableRowClass) . '
<script type="text/javascript" >
	eobackup(document).ready(function(){
		jQuery(".backup_restoration_button").click(function(){
			if(confirm(eobu_convert_html_accent_for_js("' . __('Si vous restaurer cette sauvegarde, les informations pr&eacute;sentes sur le domaine de restauration seront supprim&eacute;es', 'eobackup') . '"))){
				var backup_id = jQuery(this).attr("id").replace("restore_backup_", "");
				jQuery("#restoration_button_container_" + backup_id).html(jQuery("#loading_picture").html());
				jQuery("#restoration_button_container_" + backup_id).load(EO_BU_AJAX_FILE,{
					"action": "restore_domain",
					"backup_identifier": backup_id
				});
			}
		});

		jQuery(".domain_backup_log").click(function(){
			jQuery("#domain_details_dialog").html(jQuery("#loading_picture").html());
			jQuery("#domain_details_dialog").load(EO_BU_AJAX_FILE, {
				"action": "load_history_log",
				"history_id_line" : jQuery(this).parent("td").parent("tr").attr("id").replace("view_backup_log", "")
			});
			jQuery("#domain_details_dialog").dialog("option", "title", "' . __('Logs de la sauvegarde', 'eobackup') . '");
			jQuery("#domain_details_dialog").dialog("open");
		});';
		if($domain_identifier_type == 'HISTORY.parent_backup_id'){
			$domain_history .= '
		jQuery(".backup_pagination span").click(function(){
			jQuery("#general_backup__details' . $domain_identifier . ' td").html(jQuery("#loading_picture").html());
			jQuery("#general_backup__details' . $domain_identifier . ' td").load(EO_BU_AJAX_FILE,{
				"action": "domain_global_sub_history",
				"domain_id" : "' . $domain_identifier . '",
				"page": jQuery(this).attr("id").replace("page_number_", "")
			});
		});';
		}
		else{
			$domain_history .= '
		jQuery(".backup_pagination span").click(function(){
			jQuery("#domain_details_content").html(jQuery("#loading_picture").html());
			jQuery("#domain_details_content").load(EO_BU_AJAX_FILE,{
				"action": "domain_history_' . $history_type . '",
				"domain_id" : "' . $domain_identifier . '",
				"page": jQuery(this).attr("id").replace("page_number_", "")
			});
		});';
		}
		$domain_history .= '
	});
</script>';
		}
		else{
			switch($history_type){
				case 'backup':
					$domain_history = __('Ce domaine ne poss&egrave;de pas de sauvegarde', 'eobackup');
				break;
				case 'restore':
					$domain_history = __('Ce domaine n\'a jamais &eacute;t&eacute; restaur&eacute;', 'eobackup');
				break;
				case 'cleanup':
					$domain_history = __('Aucune sauvegarde de ce domaine n\'a &eacute;t&eacute; supprim&eacute;e pour le moment', 'eobackup');
				break;
			}
		}

		return $domain_history;
	}

}