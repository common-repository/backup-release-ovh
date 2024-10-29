#!/usr/local/bin/php5
<?php
/**
* Plugin backup launch utilities
* 
* Allows to launch the different backup
* @author Eoxia <dev@eoxia.com>
* @version 0.1
* @package backup_release_ovh
* @subpackage job_maker
*/

$current_dir = getcwd();
$current_dir_component = explode('/', $current_dir);
$root_dir = '';
foreach($current_dir_component as $element){
	$root_dir .= '/' . $element;
	if($element == 'www'){
		break;
	}
}

define('WP_ADMIN', true);
require_once($root_dir . '/wp-load.php');
require_once(ABSPATH . 'wp-admin/includes/admin.php');

$eobu_allowed_ip = eobu_options::get_specific_option('eobu_options', 'eobu_allowed_ip');
if(!isset($_SERVER['REMOTE_ADDR']) || empty($_SERVER['REMOTE_ADDR']) || (in_array($_SERVER['REMOTE_ADDR'], $eobu_allowed_ip))){
	$eobu_server = eobu_options::get_specific_option('eobu_options', 'eobu_server');
	$eobu_email = eobu_options::get_specific_option('eobu_options', 'eobu_email');
	/*	Check if the directories are set correctly before continuing	*/
	$params_are_set = eobu_options::check_mandatory_fields();
	if(!$params_are_set){
		if(!is_array($eobu_email)){
			$eobu_email[] = get_option('admin_email');
		}
		/*		*/
		$headers ="From: \"" . __('Service de sauvegarde', 'eobackup') . " " . $eobu_server . "\" <" . get_option('admin_email') . ">\n"; 
		$headers.="Reply-to: \"" . __('Service de sauvegarde', 'eobackup') . " " . $eobu_server . "\" <" . get_option('admin_email') . ">\n";
		$headers.="MIME-Version: 1.0\n";
		$headers.="Content-type: text/html; charset= iso-8859-1\n";
		/*		*/
		$sujet = sprintf(__('une erreur est survenue lors du lancement du script de sauvegarde sur le serveur %s', 'eobackup'), $eobu_server);
		foreach($eobu_email as $email){
			@mail($email, $sujet, __('Des param&egrave;tres sont manquants pour effectuer la sauvegarde, merci de vous connecter &agrave; l\'interface d\'administration pour corriger ce probl&egrave;me', 'eobackup'), $headers);
		}
	}
	else{
		/*	Check the domain present on the server and not configured for backup	*/
		if(eobu_options::get_specific_option('eobu_options', 'eobu_check_unconfigured_domain_at_global_backup') == 'oui'){
			eobu_domain::get_unconfigured_domain();
		}

		/*	Log start of backup	*/
		$wpdb->insert(EOBU_DBT_DOMAIN_HISTORY, array('status' => 'moderated', 'creation_date' => current_time('mysql', 0), 'domain_id' => 0, 'start' => current_time('mysql', 0)));
		$last_global_backup_id = $wpdb->insert_id;

		$file_for_day = '';
		$domain_to_check_for_deletion = array();
		$get_domain_to_backup = eobu_domain::get_domain_to_backup();
		foreach($get_domain_to_backup as $domain_infos){
			if(isset($domain_infos->backup_to_do) && ($domain_infos->backup_to_do = 'yes')){
				$file_for_day .= $domain_infos->name . "
";
			}
			if($domain_infos->backup_total_nb != 0){
				$domain_to_check_for_deletion[$domain_infos->domain_id][$domain_infos->name] = $domain_infos->backup_total_nb;
			}
		}

		if((count($get_domain_to_backup) > 0) && ($file_for_day != '')){
			$eobu_directories_sh_container = eobu_options::get_specific_option('eobu_options', 'eobu_directories_sh_container');
			$eobu_directories_REPLOG = eobu_options::get_specific_option('eobu_options', 'eobu_directories_REPLOG');

			/*	Create the file containing the list of domain to backup for the current session	*/
			$final_dir = EO_BU_GENERATED_DOC_DIR . 'day_list';
			$final_file = date('YmdHis') . 'domain_list.txt';
			if(!is_dir($final_dir)){
				mkdir($final_dir, 0755, true);
			}
			$day_file = fopen($final_dir . '/' . $final_file, 'w');
			fwrite($day_file, $file_for_day);
			fclose($day_file);

			/*	Launch backup	*/
			exec($eobu_directories_sh_container . '/' . sprintf(EOBU_BACKUP_SH, EOBU_BACKUP_SH_VERSION) . ' ' . $final_dir . '/' . $final_file);

			/*	Log backup result	*/
			$log_file = fopen($eobu_directories_REPLOG . '/backup_log.txt', 'r');
			$log_content = fread($log_file, filesize($eobu_directories_REPLOG . '/backup_log.txt'));
			fclose($log_file);
			$last_log = explode('-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-', $log_content);
			$backup_infos['log'] = $last_log[count($last_log) - 1];
			$backup_infos['result'] = $final_dir . '/' . $final_file;

			@unlink($eobu_directories_REPLOG . '/' . $_REQUEST['domain_name'] . '/backup_weight_log_' . $_REQUEST['domain_name'] . '.txt');

			/*	Prepare the domain backup deletion for domain with limited backup number 	*/
			if(is_array($domain_to_check_for_deletion) && (count($domain_to_check_for_deletion) > 0)){
				$eobu_rotation_nb = eobu_options::get_specific_option('eobu_options', 'eobu_rotation_nb');
				$list_to_delete = '';
				foreach($domain_to_check_for_deletion as $domain_id => $domain_infos){
					$query = $wpdb->prepare("
SELECT result
FROM " . EOBU_DBT_DOMAIN_HISTORY . " 
WHERE domain_id = %d 
	AND status = 'valid'
	AND history_type = 'backup'
ORDER BY creation_date DESC", $domain_id);
					$backup_list = $wpdb->get_results($query);
					$domain_name = $domain_backup_nb = '';
					foreach($domain_infos as $domainName => $domainBackupNb){
						$domain_name = $domainName;
						$domain_backup_nb = $domainBackupNb;
					}
					if($domain_backup_nb <= 0){
						$domain_backup_nb = $eobu_rotation_nb;
					}
					$i = 0;
					foreach($backup_list as $domain_result){
						if($i >= $domain_backup_nb){
							$list_to_delete .= $domain_result->result . "
";
						}
						$i++;
					}
				}

				if(trim($list_to_delete) != ''){
					/*	Create the file containing the list of domain to backup for the current session	*/
					$final_dir = EO_BU_GENERATED_DOC_DIR . 'day_remove_list';
					$final_file = date('YmdHis') . 'domain_list.txt';
					if(!is_dir($final_dir)){
						mkdir($final_dir, 0755, true);
					}
					$day_file = fopen($final_dir . '/' . $final_file, 'w');
					fwrite($day_file, $list_to_delete);
					fclose($day_file);

					/*	Launch backup	*/
					exec($eobu_directories_sh_container . '/' . sprintf(EOBU_BACKUP_REMOVER_SH, EOBU_BACKUP_REMOVER_SH_VERSION) . ' ' . $final_dir . '/' . $final_file);
				}
			}

		}
		else{
			$backup_infos['result'] = __('Aucune sauvegarde n\'a &eacute;t&eacute configur&eacute;e pour cette session de sauvegarde', 'eobackup');
			$backup_infos['log'] = 'empty';
			$backup_infos['file_status'] = 'empty';
			$backup_infos['db_status'] = 'empty';
		}

		/*	Log the end of backup	*/
		$backup_infos['status'] = 'valid';
		$backup_infos['last_update_date'] = current_time('mysql', 0);
		$backup_infos['end'] = current_time('mysql', 0);
		$wpdb->update(EOBU_DBT_DOMAIN_HISTORY, $backup_infos, array('id' => $last_global_backup_id, 'domain_id' => 0));
	}
}
else{
	$eobu_email = eobu_options::get_specific_option('eobu_options', 'eobu_email');
	if(!is_array($eobu_email)){
		$eobu_email[] = get_option('admin_email');
	}
	/*		*/
	$headers ="From: \"" . __('Service de sauvegarde', 'eobackup') . " " . $eobu_server . "\" <" . get_option('admin_email') . ">\n"; 
	$headers.="Reply-to: \"" . __('Service de sauvegarde', 'eobackup') . " " . $eobu_server . "\" <" . get_option('admin_email') . ">\n";
	$headers.="MIME-Version: 1.0\n";
	$headers.="Content-type: text/html; charset= iso-8859-1\n";
	/*		*/
	$sujet = sprintf(__('une erreur est survenue lors du lancement du script de sauvegarde sur le serveur %s', 'eobackup'), $eobu_server);
	foreach($eobu_email as $email){
		@mail($email, $sujet, __('Erreur : mauvaise IP', 'eobackup') . serialize($_SERVER), $headers);
	}
	die('Forbidden Access');
}