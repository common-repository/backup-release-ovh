<?php
/**
*	Plugin configuration
* 
* Define the different configuration for plugin
* @author Eoxia <dev@eoxia.com>
* @version 0.1
* @package backup_release_ovh
* @subpackage include
*/


/*	Plugin directories definition	*/
DEFINE('EO_BU_HOME_URL', WP_PLUGIN_URL . '/' . EO_BU . '/');
DEFINE('EO_BU_HOME_DIR', WP_PLUGIN_DIR . '/' . EO_BU . '/');

DEFINE('EO_BU_INC_PLUGIN_DIR' , EO_BU_HOME_DIR . 'include/');
DEFINE('EO_BU_INC_PLUGIN_URL' , EO_BU_HOME_URL . 'include/');
DEFINE('EO_BU_LANGUAGES_PLUGIN_DIR' , EO_BU_HOME_DIR . 'languages/');
DEFINE('EO_BU_MEDIA_PLUGIN_DIR' , EO_BU_HOME_DIR . 'medias/');
DEFINE('EO_BU_MEDIA_PLUGIN_URL' , EO_BU_HOME_URL . 'medias/');

DEFINE('EO_BU_LIB_PLUGIN_DIR' , EO_BU_INC_PLUGIN_DIR . 'librairies/');
DEFINE('EO_BU_TPL_PLUGIN_DIR' , EO_BU_INC_PLUGIN_DIR . 'templates/');

DEFINE('EO_BU_GENERATED_DOC_DIR', WP_CONTENT_DIR . '/uploads/' . EO_BU . '/');
DEFINE('EO_BU_GENERATED_DOC_URL', WP_CONTENT_URL . '/uploads/' . EO_BU . '/');


/*	Get the last version file into templates directory	*/
$last_script_version = $last_script_remover_version = 6;
$replacement_patterns = array('/backup/', '/_/', '/remover/', '/V/', '/.sh/');
$replacement = array('', '', '', '');
$template_dir = opendir(EO_BU_TPL_PLUGIN_DIR);
while(false !== ($template_content = readdir($template_dir))){
	$file = EO_BU_TPL_PLUGIN_DIR . $template_content;
	if(is_file($file) && preg_match("/\.(sh){1}$/i", $file)){
		$current_version = preg_replace($replacement_patterns, $replacement, $template_content);
		if(strpos($file, "backup_V") && ($current_version > $last_script_version)){
			$last_script_version = $current_version;
		}
		elseif(strpos($file, "backup_remover_V") && ($current_version > $last_script_remover_version)){
			$last_script_remover_version = $current_version;
		}
	}
}
closedir($template_dir);

/*	Define the last 	*/
DEFINE('EOBU_BACKUP_SH_VERSION', $last_script_version);
DEFINE('EOBU_BACKUP_REMOVER_SH_VERSION', $last_script_remover_version);

/*	Define the path to access backup script	*/
DEFINE('EOBU_BACKUP_SH', 'backup_V%d.sh');
DEFINE('EOBU_BACKUP_REMOVER_SH', 'backup_remover_V%d.sh');

/*	Define the different config file regarding the domain site type	*/
DEFINE('EOBU_WP_CONFIG_FILE', '/home/#EOBU_BACKUP_DOMAIN#/sd/#EOBU_RESTORATION_SUB_DOMAIN#/www/#EOBU_TIME_FILE#wp-config.php');
DEFINE('EOBU_MAGE_CONFIG_FILE', '/home/#EOBU_BACKUP_DOMAIN#/sd/#EOBU_RESTORATION_SUB_DOMAIN#/www/app/etc/#EOBU_TIME_FILE#local.xml');


/*	Slug definition	*/
DEFINE('EOBU_SLUG_DASHBOARD', 'eobu_dashboard');
DEFINE('EOBU_SLUG_DOMAIN', 'eobu_domain');
DEFINE('EOBU_SLUG_HISTORY', 'eobu_history');
DEFINE('EOBU_SLUG_OPTION', 'eobu_options');


/*	Database table definition	*/
global $wpdb;
DEFINE('EOBU_DBT_DOMAIN', $wpdb->prefix . 'eobu__domain');
DEFINE('EOBU_DBT_CALENDAR', $wpdb->prefix . 'eobu__calendar');
DEFINE('EOBU_DBT_DOMAIN_HISTORY', $wpdb->prefix . 'eobu__history');

/*	Define the list of rsync errors	*/
DEFINE('EOBU_RSYNC_RESULT_CORRESPONDANCE', serialize(array('0' => 'Success', '1' => 'Syntax or usage error', '2' => 'Protocol incompatibility', '3' => 'Errors selecting input/output files, dirs', '4' => 'Requested action not supported: an attempt was made  to  manipulate  64-bit files on a platform that cannot support them; or an option was specified that is supported by the client and not by the server.', '5' => 'Error starting client-server protocol', '6' => 'Daemon unable to append to log-file', '10' => 'Error in socket I/O', '11' => 'Error in file I/O', '12' => 'Error in rsync protocol data stream', '13' => 'Errors with program diagnostics', '14' => 'Error in IPC code', '20' => 'Received SIGUSR1 or SIGINT', '21' => 'Some error returned by waitpid()', '22' => 'Error allocating core memory buffers', '23' => 'Partial transfer due to error', '24' => 'Partial transfer due to vanished source files', '25' => 'The --max-delete limit stopped deletions', '30' => 'Timeout in data send/receive'))); 

/*	Define the different default element for plugin usage	*/
DEFINE('EOBU_EXCLUDED_DOMAIN', serialize(array('aquota.user', 'ftp', 'log', 'lost+found', 'ovh', 'ovhm', 'mysql', 'vpopmail')));
DEFINE('EOBU_FORBIDDEN_DELETE', serialize(array('/', 'home')));
DEFINE('EOBU_DB_BACKUP_TYPE', serialize(array('dump' => __('Dump mysql', 'eobackup')/* , 'dumpandsync' => __('Dump mysql + Rsync', 'eobackup'), 'none' => __('Aucune', 'eobackup') */)));
DEFINE('EOBU_BACKUP_TYPE', serialize(array('standard' => __('Simple', 'eobackup')/*, 'special' => __('Avanc&eacute;e', 'eobackup')*/)));
DEFINE('EOBU_DAY_OF_WEEK', serialize(array(__('Lundi', 'eobackup'), __('Mardi', 'eobackup'), __('Mercredi', 'eobackup'), __('Jeudi', 'eobackup'), __('Vendredi', 'eobackup'), __('Samedi', 'eobackup'), __('Dimanche', 'eobackup'))));

exec("ls -d /*/", $root_directory_list);
DEFINE('EOBU_FORBIDDEN_DIR', serialize($root_directory_list));

/**
*	Define the option possible value
*/
$eobu_yes_or_no_option_list = array('oui' => __('Oui', 'eobackup'), 'non' => __('Non', 'eobackup'));

// DEFINE('EOBU_ALLOW_ADMIN_CONFIG_DOMAIN', true);
// DEFINE('EOBU_ALLOW_ADMIN_RESTORE_BACKUP', true);

