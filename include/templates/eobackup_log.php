<?php
/**
* Plugin log utilities
* 
* Allows to log informations about backup
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

if($_SERVER['REMOTE_ADDR'] == $_SERVER['SERVER_ADDR']){
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
			@mail($email, $sujet, __('Des param&egrave;tres sont manquants pour effectuer la sauvegarde (log), merci de vous connecter &agrave; l\'interface d\'administration pour corriger ce probl&egrave;me', 'eobackup'), $headers);
		}
	}
	else{
		if($_REQUEST['action'] == 'remove_old_backup'){
			/*	Redefine the domain name	*/
			$backup_dir = explode('/', $_REQUEST['domain_name']);
			/*	Get the domain identifier	*/
			$query = $wpdb->prepare("SELECT id FROM " . EOBU_DBT_DOMAIN . " WHERE name = %s", $backup_dir[0]);
			$domain_id = $wpdb->get_var($query);

			if($_REQUEST['time'] == 'start'){
				/*	Get the last general backup identifier	*/
				$query = $wpdb->prepare("SELECT id FROM " . EOBU_DBT_DOMAIN_HISTORY . " WHERE status = 'valid' AND domain_id = '0' ORDER BY creation_date DESC LIMIT 1");
				$parent_backup_id = $wpdb->get_var($query);

				$wpdb->insert(EOBU_DBT_DOMAIN_HISTORY, array('status' => 'moderated', 'history_type' => 'cleanup', 'creation_date' => current_time('mysql', 0), 'parent_backup_id' => $parent_backup_id, 'domain_id' => $domain_id, 'start' => current_time('mysql', 0), 'log' => $_REQUEST['top']));
			}
			elseif($_REQUEST['time'] == 'end'){
				/*	Log the end of backup	*/
				$backup_infos['status'] = 'valid';
				$backup_infos['last_update_date'] = current_time('mysql', 0);
				$backup_infos['end'] = current_time('mysql', 0);
				$backup_infos['file_status'] = $_REQUEST['state'];
				$wpdb->update(EOBU_DBT_DOMAIN_HISTORY, $backup_infos, array('status' => 'moderated', 'domain_id' => $domain_id));

				if($_REQUEST['state'] == 'OK'){
					unset($backup_infos);
					$backup_infos['status'] = 'deleted';
					$backup_infos['last_update_date'] = current_time('mysql', 0);
					$wpdb->update(EOBU_DBT_DOMAIN_HISTORY, $backup_infos, array('result' => $_REQUEST['domain_name'], 'domain_id' => $domain_id));
				}
			}
		}
		else{
			/*	Get the domain identifier	*/
			$query = $wpdb->prepare("SELECT id FROM " . EOBU_DBT_DOMAIN . " WHERE name = %s", $_REQUEST['domain_name']);
			$domain_id = $wpdb->get_var($query);
			if($_REQUEST['time'] == 'start'){
				/*	Get the last general backup identifier	*/
				$query = $wpdb->prepare("SELECT id FROM " . EOBU_DBT_DOMAIN_HISTORY . " WHERE status = 'moderated' AND domain_id = '0' ORDER BY creation_date DESC LIMIT 1");
				$parent_backup_id = $wpdb->get_var($query);

				$wpdb->insert(EOBU_DBT_DOMAIN_HISTORY, array('status' => 'moderated', 'history_type' => 'backup', 'creation_date' => current_time('mysql', 0), 'parent_backup_id' => $parent_backup_id, 'domain_id' => $domain_id, 'start' => current_time('mysql', 0), 'result' => $_REQUEST['domain_name'] . '/' . $_REQUEST['date'] . '_' . $_REQUEST['domain_name'], 'log' => $_REQUEST['top']));
			}
			elseif($_REQUEST['time'] == 'end'){
				/*	Get directory to user log file	*/
				$eobu_directories_REPLOG = eobu_options::get_specific_option('eobu_options', 'eobu_directories_REPLOG');

				/*	Get the last backup log	*/
				$log_file = fopen($eobu_directories_REPLOG . '/' . $_REQUEST['domain_name'] . '/backup_log_' . $_REQUEST['domain_name'] . '.txt', 'r');
				$log_content = fread($log_file, filesize($eobu_directories_REPLOG . '/' . $_REQUEST['domain_name'] . '/backup_log_' . $_REQUEST['domain_name'] . '.txt'));
				fclose($log_file);
				$last_log = explode('-*-*-*-*-*-*-*-*-*-*-*-*-*-*-*-', $log_content);

				/*	Get the different directory weight after backup is done	*/
				unset($log_content);
				$log_file = fopen($eobu_directories_REPLOG . '/' . $_REQUEST['domain_name'] . '/backup_weight_log_' . $_REQUEST['domain_name'] . '.txt', 'r');
				$log_content = fread($log_file, filesize($eobu_directories_REPLOG . '/' . $_REQUEST['domain_name'] . '/backup_weight_log_' . $_REQUEST['domain_name'] . '.txt'));
				fclose($log_file);

				/*	Get the last general backup identifier	*/
				$query = $wpdb->prepare("SELECT log FROM " . EOBU_DBT_DOMAIN_HISTORY . " WHERE status = 'moderated' AND domain_id = %d ORDER BY creation_date DESC LIMIT 1", $domain_id);
				$existant_log = $wpdb->get_var($query);

				$backup_infos['log'] = serialize(array($existant_log, $last_log[count($last_log) - 1], $_REQUEST['top'], $log_content));

				/*	Log the end of backup	*/
				$backup_infos['status'] = 'valid';
				$backup_infos['last_update_date'] = current_time('mysql', 0);
				$backup_infos['end'] = current_time('mysql', 0);
				$backup_infos['file_status'] = $_REQUEST['status_script'];
				$backup_infos['db_status'] = $_REQUEST['status_bdd'];
				$wpdb->update(EOBU_DBT_DOMAIN_HISTORY, $backup_infos, array('status' => 'moderated', 'domain_id' => $domain_id));

				$query = $wpdb->prepare("SELECT interval_type, `interval` FROM " . EOBU_DBT_CALENDAR . " WHERE domain_id = %d", $domain_id);
				$domain_interval = $wpdb->get_row($query);
				$query = $wpdb->prepare("UPDATE " . EOBU_DBT_DOMAIN . " SET last_backup = %s, next_backup = DATE_ADD(%s, INTERVAL " . $domain_interval->interval . " " . $domain_interval->interval_type . ") WHERE id = %d", date('Y-m-d'), date('Y-m-d'), $domain_id);
				$wpdb->query($query);
			}
		}
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