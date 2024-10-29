<?php
/**
* Plugin database datas
* 
* Define database datas
* @author Eoxia <dev@eoxia.com>
* @version 0.1
* @package backup_release_ovh
* @subpackage librairies
*/

	global $wpdb;

	$eobackup_datas = array();
	$eobackup_options_add = array();
	$eobackup_options_update = array();
	$eobackup_data_link = array();

{/*	Version 0	*/
	$eobackup_db_version = 0;

	$eobackup_options_add[$eobackup_db_version]['eobu_options']['eobu_server'] = '';
	$eobackup_options_add[$eobackup_db_version]['eobu_options']['eobu_directories_sh_container'] = '/home/' . DB_USER . '_cron';
	$eobackup_options_add[$eobackup_db_version]['eobu_options']['eobu_directories_php_container'] = '';
	$eobackup_options_add[$eobackup_db_version]['eobu_options']['eobu_directories_REPBACKUP'] = '';
	$eobackup_options_add[$eobackup_db_version]['eobu_options']['eobu_directories_REPLOG'] = '/home/' . DB_USER . '_cron/log';
	$eobackup_options_add[$eobackup_db_version]['eobu_options']['eobu_email'] = '';
	$eobackup_options_add[$eobackup_db_version]['eobu_options']['eobu_excluded_domain'] = array_merge(unserialize(EOBU_EXCLUDED_DOMAIN), array(DB_USER . '_cron'));
	$eobackup_options_add[$eobackup_db_version]['eobu_options']['eobu_rotation_nb'] = 5;
	$eobackup_options_add[$eobackup_db_version]['eobu_options']['eobu_sleep_time'] = 10;
}

{/*	Version 2	*/
	$eobackup_db_version = 2;

	$eobackup_options_update[$eobackup_db_version]['eobu_options']['eobu_per_page_element_nb'] = '5';
	$eobackup_options_update[$eobackup_db_version]['eobu_options']['eobu_send_mail_for_moderated_unconfigured_domain'] = 'yes';
	$eobackup_options_update[$eobackup_db_version]['eobu_options']['eobu_check_unconfigured_domain_at_global_backup'] = 'yes';
	$eobackup_options_update[$eobackup_db_version]['eobu_options']['eobu_allow_multiple_backup_one_day'] = 'yes';
	$eobackup_options_update[$eobackup_db_version]['eobu_options']['eobu_log_exec_command_result'] = 'yes';
}