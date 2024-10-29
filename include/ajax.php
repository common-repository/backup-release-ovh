<?php
/**
* Plugin ajax utilities
* 
* Allows to make some ajax request
* @author Eoxia <dev@eoxia.com>
* @version 0.1
* @package backup_release_ovh
* @subpackage job_maker
*/

define('DOING_AJAX', true);
define('WP_ADMIN', true);
require_once('../../../../wp-load.php');
require_once(ABSPATH . 'wp-admin/includes/admin.php');

@header('Content-Type: text/html; charset=' . get_option('blog_charset'));

/*	First thing we define the main directory for our plugin in a super global var	*/
DEFINE('EO_BU', basename(dirname(__FILE__)));

/*	Include the different config for the plugin	*/
require_once(WP_PLUGIN_DIR . '/' . EO_BU . '/include/config.php' );

/*	Include the file which includes the different files used by all the plugin	*/
require_once(EO_BU_INC_PLUGIN_DIR . 'include.php');

$action = $_REQUEST['action'];

/*	First look at the request method Could be post or get	*/
switch($action)
{
	case 'generate_new_sh_file':{
		/*	Check if the directories are set correctly before continuing	*/
		$params_are_set = eobu_options::check_mandatory_fields();
		if($params_are_set){
			$eobu_options = eobu_options::get_specific_option('eobu_options', array('eobu_server', 'eobu_directories_REPBACKUP', 'eobu_directories_sh_container', 'eobu_directories_php_container', 'eobu_directories_REPLOG', 'eobu_sleep_time', 'eobu_email', 'eobu_excluded_domain'));

			/*	Get script template file	*/
			ob_start();
			include(EO_BU_TPL_PLUGIN_DIR . sprintf(EOBU_BACKUP_SH, EOBU_BACKUP_SH_VERSION));
			$script_file_template = ob_get_contents();
			ob_end_clean();

			/*	Replace defined vars in template by configuration var	*/
			$script_file_template = str_replace('#EOBU_NOMSERVEUR_EOBU#', $eobu_options['eobu_server'], $script_file_template);
			$script_file_template = str_replace('#EOBU_BDDUSERNAME_EOBU#', DB_USER, $script_file_template);
			$script_file_template = str_replace('#EOBU_BDDUSERPASS_EOBU#', DB_PASSWORD, $script_file_template);
			$script_file_template = str_replace('#EOBU_REPBACKUP_EOBU#', $eobu_options['eobu_directories_REPBACKUP'], $script_file_template);
			$script_file_template = str_replace('#EOBU_REPLOG_EOBU#', $eobu_options['eobu_directories_REPLOG'], $script_file_template);
			$script_file_template = str_replace('#EOBU_REPCLIENT_EOBU#', $eobu_options['eobu_directories_REPLOG'], $script_file_template);
			$script_file_template = str_replace('#EOBU_LISTEUSER_EOBU#', $eobu_options['eobu_directories_sh_container'] . '/liste_domain_to_backup.txt', $script_file_template);
			$script_file_template = str_replace('#EOBU_REPORT_EMAIL_EOBU#', implode(" ", $eobu_options['eobu_email']), $script_file_template);
			$script_file_template = str_replace('#EOBU_DIR_TO_EXEPHP#', EO_BU_HOME_URL . $eobu_options['eobu_directories_php_container'], $script_file_template);
			$script_file_template = str_replace('#EOBU_INTERVAL_BETWEEN_DOMAIN#', $eobu_options['eobu_sleep_time'], $script_file_template);
			$eobu_excluded_domain = $eobu_options['eobu_excluded_domain'];

			$script_file_template = str_replace('#EOBU_EXCLUDEDDIR_EOBU#', ((($eobu_excluded_domain != '')) ? " -I " . implode(" -I ", $eobu_excluded_domain) . " " : " "), $script_file_template);

			if(!is_dir(EO_BU_GENERATED_DOC_DIR)){
				mkdir(EO_BU_GENERATED_DOC_DIR, 0755, true);
			}
			if(!is_dir(EO_BU_GENERATED_DOC_DIR . 'script_histo')){
				mkdir(EO_BU_GENERATED_DOC_DIR . 'script_histo', 0755, true);
			}
			$f_script = fopen(EO_BU_GENERATED_DOC_DIR .sprintf(EOBU_BACKUP_SH, EOBU_BACKUP_SH_VERSION), 'w');
			fwrite($f_script, $script_file_template);
			fclose($f_script);

			if(is_file(EO_BU_GENERATED_DOC_DIR . sprintf(EOBU_BACKUP_SH, EOBU_BACKUP_SH_VERSION))){
				copy(EO_BU_GENERATED_DOC_DIR . sprintf(EOBU_BACKUP_SH, EOBU_BACKUP_SH_VERSION), EO_BU_GENERATED_DOC_DIR . 'script_histo/' . date('Ymd_His')  . '_' . sprintf(EOBU_BACKUP_SH, EOBU_BACKUP_SH_VERSION));
				echo '<a href="' . EO_BU_GENERATED_DOC_URL . sprintf(EOBU_BACKUP_SH, EOBU_BACKUP_SH_VERSION) . '" >' . __('T&eacute;l&eacute;charger le fichier de sauvegarde', 'eobackup') . '</a>';
			}

			/*	Get script template file	*/
			ob_start();
			include(EO_BU_TPL_PLUGIN_DIR . sprintf(EOBU_BACKUP_REMOVER_SH, EOBU_BACKUP_REMOVER_SH_VERSION));
			$script_file_template = ob_get_contents();
			ob_end_clean();

			/*	Replace defined vars in template by configuration var	*/
			$script_file_template = str_replace('#EOBU_NOMSERVEUR_EOBU#', $eobu_options['eobu_server'], $script_file_template);
			$script_file_template = str_replace('#EOBU_REPBACKUP_EOBU#', $eobu_options['eobu_directories_REPBACKUP'], $script_file_template);
			$script_file_template = str_replace('#EOBU_REPLOG_EOBU#', $eobu_options['eobu_directories_REPLOG'], $script_file_template);
			$script_file_template = str_replace('#EOBU_REPORT_EMAIL_EOBU#', implode(" ", $eobu_options['eobu_email']), $script_file_template);
			$script_file_template = str_replace('#EOBU_DIR_TO_EXEPHP#', EO_BU_HOME_URL . $eobu_options['eobu_directories_php_container'], $script_file_template);
			$script_file_template = str_replace('#EOBU_INTERVAL_BETWEEN_DOMAIN#', $eobu_options['eobu_sleep_time'], $script_file_template);

			if(!is_dir(EO_BU_GENERATED_DOC_DIR)){
				mkdir(EO_BU_GENERATED_DOC_DIR, 0755, true);
			}
			if(!is_dir(EO_BU_GENERATED_DOC_DIR . 'script_histo')){
				mkdir(EO_BU_GENERATED_DOC_DIR . 'script_histo', 0755, true);
			}
			$f_script = fopen(EO_BU_GENERATED_DOC_DIR . sprintf(EOBU_BACKUP_REMOVER_SH, EOBU_BACKUP_REMOVER_SH_VERSION), 'w');
			fwrite($f_script, $script_file_template);
			fclose($f_script);

			if(is_file(EO_BU_GENERATED_DOC_DIR . sprintf(EOBU_BACKUP_REMOVER_SH, EOBU_BACKUP_REMOVER_SH_VERSION))){
				copy(EO_BU_GENERATED_DOC_DIR . sprintf(EOBU_BACKUP_REMOVER_SH, EOBU_BACKUP_REMOVER_SH_VERSION), EO_BU_GENERATED_DOC_DIR . 'script_histo/' . date('Ymd_His')  . '_' . sprintf(EOBU_BACKUP_REMOVER_SH, EOBU_BACKUP_REMOVER_SH_VERSION));
				echo '<br/><a href="' . EO_BU_GENERATED_DOC_URL . sprintf(EOBU_BACKUP_REMOVER_SH, EOBU_BACKUP_REMOVER_SH_VERSION) . '" >' . __('T&eacute;l&eacute;charger le fichier de nettoyage', 'eobackup') . '</a>';
			}
			
			
		}
		else{
			echo __('Des param&egrave;tres sont manquants pour la g&eacute;n&eacute;ration du fichier de sauvegarde', 'eobackup');
		}
	}
	break;

	case 'restore_domain':{
		global $current_user;

		$restore_required_option = eobu_options::get_specific_option('eobu_options', array('eobu_server', 'eobu_directories_REPBACKUP', 'eobu_restoration_domain_name', 'eobu_restoration_db_name', 'eobu_restoration_db_user', 'eobu_restoration_db_pass'));
		$backup_identifier = (isset($_POST['backup_identifier']) && (trim($_POST['backup_identifier']) != '')) ? trim($_POST['backup_identifier']) : '';
		$restoration_message_state = 'updated';

		if(!empty($restore_required_option['eobu_restoration_domain_name']) && !empty($restore_required_option['eobu_restoration_db_name']) && !empty($restore_required_option['eobu_restoration_db_user']) && !empty($restore_required_option['eobu_restoration_db_pass'])){
			$restoration_start_date = current_time('mysql', 0);

			/*	Get the different informations about the current backup to restore	*/
			$query = $wpdb->prepare("
SELECT HISTORY.parent_backup_id, HISTORY.result, HISTORY.domain_id, HISTORY.creation_date, DOMAIN.name
FROM " . EOBU_DBT_DOMAIN_HISTORY . " AS HISTORY
	INNER JOIN " . EOBU_DBT_DOMAIN . " AS DOMAIN ON ((DOMAIN.id = HISTORY.domain_id) AND (DOMAIN.status = 'valid'))
WHERE HISTORY.status = 'valid' 
	AND HISTORY.id = %d", 
				$backup_identifier);
			$backup_informations = $wpdb->get_row($query);

			$restoration_log = sprintf(__('%s D&eacute;but de la restauration de la sauvegarde du domaine %s effectu&eacute;e le %s', 'eobackup'), $restoration_start_date, $backup_informations->name, $backup_informations->creation_date) . "
---------------------------------------------------------";

			/*	Start by cleaning restoration domain	*/
			$restoration_log .= "
"	. sprintf(__('%s D&eacute;but du nettoyage du domaine de restauration', 'eobackup'), current_time('mysql', 0));
			unset($cmd);
			$cmd = 'rm -rv /home/' . DB_USER . '/sd/' . $restore_required_option['eobu_restoration_domain_name'] . '/www/*';
			$restoration_log .= "
"	. sprintf(__('Commande de suppression des fichiers %s', 'eobackup'), $cmd);
			unset($exec_result);exec($cmd, $exec_result);
			if(eobu_options::get_specific_option('eobu_options', 'eobu_log_exec_command_result') == 'oui'){
				$restoration_log .= "
" . serialize($exec_result);
			}

			$restoration_log .= "
"	. sprintf(__('%s Fin du nettoyage du domaine de restauration', 'eobackup'), current_time('mysql', 0)) . "
---------------------------------------------------------";

			/*	Copy the script file into the directory	*/
			unset($cmd);
			$cmd = 'cp -pRv ' . $restore_required_option['eobu_directories_REPBACKUP'] . '/' . $backup_informations->result . '/script/' . $backup_informations->name . '/www/* /home/' . DB_USER . '/sd/' . $restore_required_option['eobu_restoration_domain_name'] . '/www';
			$restoration_log .= "
"	. sprintf(__('%s D&eacute;but de la restauration des fichiers', 'eobackup'), current_time('mysql', 0)) . "
"	. sprintf(__('Commande de restauration %s', 'eobackup'), $cmd);
			unset($exec_result);exec($cmd, $exec_result);
			if(eobu_options::get_specific_option('eobu_options', 'eobu_log_exec_command_result') == 'oui'){
				$restoration_log .= "
" . serialize($exec_result);
			}
			$restoration_log .= "
"	. sprintf(__('%s Fin de la restauration des fichiers', 'eobackup'), current_time('mysql', 0)) . "
---------------------------------------------------------";

			/*	Database restoration	*/
			$restoration_log .= "
"	. sprintf(__('%s D&eacute;but mise en forme fichier dump (suppression nom de la base de d&eacute;part)', 'eobackup'), current_time('mysql', 0));
			$sql_file = str_replace($backup_informations->name . '/', '', $backup_informations->result);
			$dump_file = $restore_required_option['eobu_directories_REPBACKUP'] . '/' . $backup_informations->result . '/dump/' . $sql_file . '.sql';
			$dump_file_content = file($dump_file);
			foreach($dump_file_content as $line_num => $line){
				switch(substr($line,0,18)){
					case "CREATE DATABASE /*":{
						$restoration_table = $wpdb->get_results("SHOW TABLES FROM " . $restore_required_option['eobu_restoration_db_name']);
						$restoration_db_table_list = '  ';
						foreach($restoration_table as $table){
							foreach($table as $table_name){
								$restoration_db_table_list .= '`' . $table_name . '`, ';
							}
						}
						$restoration_db_table_list = substr($restoration_db_table_list, 0, -2);
						if($restoration_db_table_list != ""){
							$dump_file_content[$line_num] = "DROP TABLE " . $restoration_db_table_list . ";
";
						}
						else{
							$dump_file_content[$line_num] = "
";
						}
					}break;
				}
				switch(substr($line,0,5)){
					case "USE `":{
						$dump_file_content[$line_num] = "
";
					}break;
				}
			}
			$dump_to_import = $restore_required_option['eobu_directories_REPBACKUP'] . '/' . $backup_informations->result . '/dump/' . $current_date . '_import_' . $sql_file . '.sql';
			$handle = fopen($dump_to_import, 'w');
			foreach($dump_file_content as $line ){
				fwrite($handle, $line);
			}
			fclose($handle);
			$restoration_log .= "
"	. sprintf(__('%s Fin mise en forme fichier dump (suppression nom de la base de d&eacute;part)', 'eobackup'), current_time('mysql', 0)) . "
---------------------------------------------------------";

			unset($cmd);
			$cmd = 'mysql --user=' . $restore_required_option['eobu_restoration_db_user'] . ' --password=' . $restore_required_option['eobu_restoration_db_pass'] . ' --host=localhost -D ' . $restore_required_option['eobu_restoration_db_name'] . ' < ' . $dump_to_import;
			$restoration_log .= "
"	. sprintf(__('%s D&eacute;but de la restauration de la base de donn&eacutes', 'eobackup'), current_time('mysql', 0)) . "
"	. sprintf(__('Commande de restauration %s', 'eobackup'), $cmd);
			unset($exec_result);exec($cmd, $exec_result);
			if(eobu_options::get_specific_option('eobu_options', 'eobu_log_exec_command_result') == 'oui'){
				$restoration_log .= "
" . serialize($exec_result);
			}
			$restoration_log .= "
"	. sprintf(__('%s Fin de la restauration de la base de donn&eacutes', 'eobackup'), current_time('mysql', 0)) . "
	---------------------------------------------------------";

			/*	Backup the original config file in case that it exists	*/
			$current_date = date('Ymd_His') . '_';
			$mage_config_file = str_replace('#EOBU_BACKUP_DOMAIN#', DB_USER, str_replace('#EOBU_RESTORATION_SUB_DOMAIN#', $restore_required_option['eobu_restoration_domain_name'], str_replace('#EOBU_TIME_FILE#', '', EOBU_MAGE_CONFIG_FILE)));
			$wp_config_file = str_replace('#EOBU_BACKUP_DOMAIN#', DB_USER, str_replace('#EOBU_RESTORATION_SUB_DOMAIN#', $restore_required_option['eobu_restoration_domain_name'], str_replace('#EOBU_TIME_FILE#', '', EOBU_WP_CONFIG_FILE)));

			if(is_file($wp_config_file)){
				/*	Change site access url into database	*/
				$restoration_log .= "
"	. sprintf(__('%s D&eacute;but du changement des urls dans la table options', 'eobackup'), current_time('mysql', 0));
				$query = $wpdb->query(
"UPDATE " . $restore_required_option['eobu_restoration_db_name'] . "." . $wpdb->options . " SET option_value = 'http://" . $restore_required_option['eobu_restoration_domain_name'] . ".b" . $restore_required_option['eobu_server'] . ".com'
WHERE option_name = 'siteurl' OR option_name = 'home'"
);
				$restoration_log .= "
"	. sprintf(__('%s Fin du changement des urls dans la table options', 'eobackup'), current_time('mysql', 0)) . "
	---------------------------------------------------------";

				$wp_config_backuped_file = str_replace('#EOBU_BACKUP_DOMAIN#', DB_USER, str_replace('#EOBU_RESTORATION_SUB_DOMAIN#', $restore_required_option['eobu_restoration_domain_name'], str_replace('#EOBU_TIME_FILE#', $current_date, EOBU_WP_CONFIG_FILE)));

				$restoration_log .= "
"	. sprintf(__('%s Sauvegarde du fichier de configuration original', 'eobackup'), current_time('mysql', 0));
				unset($exec_result);exec('mv -v ' . $wp_config_file . ' ' . $wp_config_backuped_file, $exec_result);
				if(eobu_options::get_specific_option('eobu_options', 'eobu_log_exec_command_result') == 'oui'){
					$restoration_log .= "
" . serialize($exec_result);
				}

				/*	Change the config file	*/
				if(is_file($wp_config_backuped_file)){
					$restoration_log .= "
"	. sprintf(__('%s D&eacute;but de la r&eacute;&eacute;criture du fichier de configuration pour le domaine de restauration', 'eobackup'), current_time('mysql', 0));
					$config_file = file($wp_config_backuped_file);
					foreach($config_file as $line_num => $line) {
						switch (substr($line,0,16)) {
							case "define('DB_NAME'":
								$config_file[$line_num] = "define('DB_NAME', '" . $restore_required_option['eobu_restoration_db_name'] . "');
";
							break;
							case "define('DB_USER'":
								$config_file[$line_num] = "define('DB_USER', '" . $restore_required_option['eobu_restoration_db_user'] . "');
";
							break;
							case "define('DB_PASSW":
								$config_file[$line_num] = "define('DB_PASSWORD', '" . $restore_required_option['eobu_restoration_db_pass'] . "');
";
							break;
						}
					}
					$handle = fopen($wp_config_file, 'w');
					foreach( $config_file as $line ){
						fwrite($handle, $line);
					}
					fclose($handle);
					chmod($wp_config_file, 0644);
					$restoration_log .= "
"	. sprintf(__('%s Fin de la r&eacute;&eacute;criture du fichier de configuration pour le domaine de restauration', 'eobackup'), current_time('mysql', 0)) . "
---------------------------------------------------------";

					$restoration_result .= __('Fin de la restauration', 'eobackup');
				}
				else{
					$restoration_log .= "
"	. sprintf(__('%s Erreur lors de la restauration - Fichier de configuration original introuvable', 'eobackup'), current_time('mysql', 0));
					$restoration_result = __('Le fichier original de configuration est introuvable', 'eobakcup');
					$restoration_message_state = 'error';
				}
			}
			elseif(is_file($magento_config_file)){
				/*	Change site access url into database	*/
				$restoration_log .= "
"	. sprintf(__('%s D&eacute;but du changement des urls dans la table configurations', 'eobackup'), current_time('mysql', 0));
				$query = $wpdb->query(
"UPDATE " . $restore_required_option['eobu_restoration_db_name'] . ".core_config_data SET value = 'http://" . $restore_required_option['eobu_restoration_domain_name'] . ".b" . $restore_required_option['eobu_server'] . ".com'
WHERE path = 'web/unsecure/base_url 	' OR path = 'web/unsecure/base_url 	'"
);
				$restoration_log .= "
"	. sprintf(__('%s Fin du changement des urls dans la table configurations', 'eobackup'), current_time('mysql', 0)) . "
	---------------------------------------------------------";


				$mage_config_backuped_file = str_replace('#EOBU_BACKUP_DOMAIN#', DB_USER, str_replace('#EOBU_RESTORATION_SUB_DOMAIN#', $restore_required_option['eobu_restoration_domain_name'], str_replace('#EOBU_TIME_FILE#', '', EOBU_MAGE_CONFIG_FILE)));

				$restoration_log .= "
"	. sprintf(__('%s Sauvegarde du fichier de configuration original', 'eobackup'), current_time('mysql', 0));
				unset($exec_result);exec('mv -v ' . $mage_config_file . ' ' . $mage_config_backuped_file, $exec_result);
				if(eobu_options::get_specific_option('eobu_options', 'eobu_log_exec_command_result') == 'oui'){
					$restoration_log .= "
" . serialize($exec_result);
				}

				/*	Change the config file	*/
				if(is_file($mage_config_backuped_file)){
					$restoration_log .= "
"	. sprintf(__('%s D&eacute;but de la r&eacute;&eacute;criture du fichier de configuration pour le domaine de restauration', 'eobackup'), current_time('mysql', 0));
					$config_file = file($mage_config_backuped_file);
					foreach($config_file as $line_num => $line) {
						switch (substr($line,0,16)) {
							case "define('DB_NAME'":
								$config_file[$line_num] = "define('DB_NAME', '" . $restore_required_option['eobu_restoration_db_name'] . "');
";
							break;
							case "define('DB_USER'":
								$config_file[$line_num] = "define('DB_USER', '" . $restore_required_option['eobu_restoration_db_user'] . "');
";
							break;
							case "define('DB_PASSW":
								$config_file[$line_num] = "define('DB_PASSWORD', '" . $restore_required_option['eobu_restoration_db_pass'] . "');
";
							break;
						}
					}
					$handle = fopen($magento_config_file, 'w');
					foreach( $config_file as $line ){
						fwrite($handle, $line);
					}
					fclose($handle);
					chmod($magento_config_file, 0644);
					$restoration_log .= "
"	. sprintf(__('%s Fin de la r&eacute;&eacute;criture du fichier de configuration pour le domaine de restauration', 'eobackup'), current_time('mysql', 0)) . "
---------------------------------------------------------";

					$restoration_result .= __('Fin de la restauration', 'eobackup');
				}
				else{
					$restoration_log .= "
"	. sprintf(__('%s Erreur lors de la restauration - Fichier de configuration original introuvable', 'eobackup'), current_time('mysql', 0));
					$restoration_result = __('Le fichier original de configuration est introuvable', 'eobakcup');
					$restoration_message_state = 'error';
				}
			}
			else{
				$restoration_log .= "
"	. sprintf(__('%s Erreur lors de la restauration - Fichier de configuration introuvable', 'eobackup'), current_time('mysql', 0));
				$restoration_result = __('Le fichier de configuration est introuvable', 'eobakcup');
				$restoration_message_state = 'error';
			}

			$restoration_log .= "
"	. sprintf(__('%s Fin de la restauration', 'eobackup'), current_time('mysql', 0));

			/*	Log restoration action	*/
			$wpdb->insert(EOBU_DBT_DOMAIN_HISTORY, array('status' => 'valid', 'history_type' => 'restore', 'creation_date' => current_time('mysql', 0), 'parent_backup_id' => $backup_informations->parent_backup_id, 'domain_id' => $backup_informations->domain_id, 'start' => $restoration_start_date, 'end' => current_time('mysql', 0), 'log' => serialize(array($current_user->ID, $restoration_log))));
		}
		else{
			$restoration_message_state = 'error';
			$restoration_result = sprintf(__('Sauvegarde serveur : Certaines configurations sont manquantes pour effectuer les restaurations. %s', 'eobackup'), '<a href="' . admin_url("options-general.php?page=" . EOBU_SLUG_OPTION) . '#eobu_options_restoration" >' . __('configurer ces variables (Section restauration)', 'eobackup') . '</a>');
		}

		echo '<div id="eobu_restoration_message_' . $backup_identifier . '" class="eobu_message ' . $restoration_message_state . '" >' . $restoration_result . '</div><div><a href="http://' . $restore_required_option['eobu_restoration_domain_name'] . '.' . str_replace('http://', '', str_replace('www.', '', home_url())) . '" target="restoration_viewer" >' . __('Voir le site restaur&eacute;', 'eobackup') . '</a></div><input type="button" class="button-primary backup_restoration_button" value="' . __('Restaurer cette sauvegarde', 'eobackup') . '" id="restore_backup_' . $backup_identifier . '" name="restore_backup" />
<script type="text/javascript" >
	eobackup(document).ready(function(){
		setTimeout(function(){
			jQuery("#eobu_restoration_message_' . $backup_identifier . '").html("");
			jQuery("#eobu_restoration_message_' . $backup_identifier . '").hide();
			jQuery("#eobu_restoration_message_' . $backup_identifier . '").removeClass("' . $restoration_message_state . '");
		}, 6500);

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
	});
</script>';
	}
	break;

	case 'general_backup_history':{
		$backup_date = (isset($_REQUEST['backup_date']) && ($_REQUEST['backup_date'] != '')) ? $_REQUEST['backup_date'] : '';
		$backup_date_operator = (isset($_REQUEST['backup_date_operator']) && ($_REQUEST['backup_date_operator'] != '')) ? $_REQUEST['backup_date_operator'] : '=';
		$not_empty = (isset($_REQUEST['not_empty']) && ($_REQUEST['not_empty'] != '')) ? $_REQUEST['not_empty'] : true;
		$page = (isset($_REQUEST['page']) && ($_REQUEST['page'] != '')) ? $_REQUEST['page'] : 0;
		echo eobu_backup::get_backup_history($backup_date, $backup_date_operator, $not_empty, $page);
	}
	break;

	case 'domain_global_sub_history':{
		$current_page = (isset($_REQUEST['page']) && ($_REQUEST['page'] != '')) ? $_REQUEST['page'] : 0;
		$domain_id = (isset($_REQUEST['domain_id']) && ($_REQUEST['domain_id'] != '')) ? $_REQUEST['domain_id'] : 0;
		echo eobu_backup::get_domain_history($domain_id, 'HISTORY.parent_backup_id', 'backup', $current_page);
	}break;
	case 'domain_config_form':
	case 'domain_history_backup':
	case 'domain_history_restore':{
		$current_page = (isset($_REQUEST['page']) && ($_REQUEST['page'] != '')) ? $_REQUEST['page'] : 0;
		$domain_id = (isset($_REQUEST['domain_id']) && ($_REQUEST['domain_id'] != '')) ? $_REQUEST['domain_id'] : 0;
		$domain_identifier_type = (isset($domain_id) && ($domain_id != '')) ? 'DOMAIN.id' : 'DOMAIN.name';
		$domain_identifier = (isset($_REQUEST['domain_identifier']) && ($_REQUEST['domain_identifier'] != '')) ? $_REQUEST['domain_identifier'] : ((isset($domain_id) && ($domain_id != '')) ? $domain_id : '');
		/*	Get domain information from database in case that it exist	*/
		$query = $wpdb->prepare("
SELECT DOMAIN.id, 
	DOMAIN_CALENDAR.id AS calendar_id, DOMAIN_CALENDAR.db_backup_type AS db_backup_type, DOMAIN_CALENDAR.backup_type AS domain_backup_type, DOMAIN_CALENDAR.interval_type, DOMAIN_CALENDAR.interval, DOMAIN_CALENDAR.start_date, DOMAIN_CALENDAR.backup_total_nb
FROM " . EOBU_DBT_DOMAIN . " AS DOMAIN
	LEFT JOIN " . EOBU_DBT_CALENDAR . " AS DOMAIN_CALENDAR ON ((DOMAIN_CALENDAR.domain_id = DOMAIN.id) AND (DOMAIN_CALENDAR.status = 'valid'))
WHERE " . $domain_identifier_type . " = %s
	AND DOMAIN.status != 'deleted'", $domain_identifier);
		$domain_informations = $wpdb->get_row($query);
		$db_backup_type = 'rsync';
		$domain_backup_type = 'standard';
		$interval = '1';
		$interval_type = 'day';
		$start_date = date('Y-m-d');
		$backup_total_nb = eobu_options::get_specific_option('eobu_options', 'eobu_rotation_nb');
		$calendar_id = 0;
		$backup_type_style['standard'] = $backup_type_style['special'] = 'eobackup_hide';
		if(is_object($domain_informations)){
			$db_backup_type = $domain_informations->db_backup_type;
			$domain_backup_type = $domain_informations->domain_backup_type;
			$domain_id = $domain_informations->id;
			$calendar_id = $domain_informations->calendar_id;
			$interval_type = $domain_informations->interval_type;
			$interval = $domain_informations->interval;
			$start_date = $domain_informations->start_date;
			$backup_total_nb = $domain_informations->backup_total_nb;
			if($interval == 1){
				$interval_type = $interval_type . 'ly';
			}
			elseif($interval >= 1){
				$interval_type = 'special_simple_type';
			}
		}
		$domain_name = $domain_identifier;
		$domain_backup_type = $domain_backup_type != '' ? $domain_backup_type : 'standard';
		$backup_type_style[$domain_backup_type] = '';

		if($action == 'domain_history_backup'){
			echo eobu_backup::get_domain_history($domain_id, 'HISTORY.domain_id', 'backup', $current_page);
		}
		elseif($action == 'domain_history_restore'){
			echo eobu_backup::get_domain_history($domain_id, 'HISTORY.domain_id', 'restore', $current_page);
		}
		else{
			echo eobu_domain::domain_planning_form($domain_id, $calendar_id, $domain_name, $db_backup_type, $backup_type_style, $domain_backup_type, $interval_type, $interval, $start_date, $backup_total_nb);
		}
	}
	break;

	case 'load_history_log':{
		$history_id_line = (isset($_REQUEST['history_id_line']) && ($_REQUEST['history_id_line'] != '')) ? $_REQUEST['history_id_line'] : '';
		if($history_id_line != ''){
			$histo_infos = explode('_', $history_id_line);
			$history_id = $histo_infos[1];
		}
		$history_id = (isset($_REQUEST['history_id']) && ($_REQUEST['history_id'] != '')) ? $_REQUEST['history_id'] : (($history_id != '') ? $history_id : '');

		$query = $wpdb->prepare("
SELECT log
FROM " . EOBU_DBT_DOMAIN_HISTORY . " AS HISTORY
WHERE id = %d", $history_id);
		$backup_log = $wpdb->get_var($query);
		$bu_log = unserialize($backup_log);
		if(is_array($bu_log)){
			$backup_log = $bu_log[1];
		}
		$backup_log = nl2br($backup_log);
		$start_substr_length = (substr($backup_log, 0, 4) == '<br>') ? 4 : ((substr($backup_log, 0, 5) == '<br/>') || (substr($backup_log, 0, 5) == '<br >') ? 5 :((substr($backup_log, 0, 6) == '<br />') ? 6 : 0));
		$backup_log = substr($backup_log, $start_substr_length);
		echo $backup_log;
	}
	break;
}

?>