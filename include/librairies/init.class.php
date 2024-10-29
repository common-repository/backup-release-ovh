<?php
/**
*	Plugin initialisation
* 
* Define the different configuration for plugin
* @author Eoxia <dev@eoxia.com>
* @version 0.1
* @package backup_release_ovh
* @subpackage include
*/

/**
* Define the different configuration for plugin
* @author Eoxia <dev@eoxia.com>
* @version 0.1
* @package backup_release_ovh
* @subpackage include
*/
class eobu_init
{

	/**
	*	This is the function called when wordpress load all activated plugin. It will load the different element needed for the plugin to be operationnal
	*
	*	@return void
	*/
	function plugin_load(){
		/*	Get the current language to translate the different text in plugin	*/
		$locale = get_locale();
		$moFile = EO_BU_LANGUAGES_PLUGIN_DIR . '/eobackup_' . $locale . '.mo';
		if( !empty($locale) && (is_file($moFile)) ){
			load_textdomain('eobackup', $moFile);
		}

		/*	Update database on plugin load	*/
		eobu_database::update_database_structure();

		/*	Check different date on server to be sure of date saved	*/
		eobu_init::check_dates();
		
		/*	Copy php file into the good directory on each plugin launch	*/
		$eobu_directories_php_container = eobu_options::get_specific_option('eobu_options', 'eobu_directories_php_container');
		if($eobu_directories_php_container != ''){
			if(!is_dir(EO_BU_HOME_DIR . $eobu_directories_php_container)){
				mkdir(EO_BU_HOME_DIR . $eobu_directories_php_container, 0755, true);
			}
			copy(EO_BU_TPL_PLUGIN_DIR . 'eobackup_launch.php', EO_BU_HOME_DIR . $eobu_directories_php_container . '/eobackup_launch.php');
			chmod(EO_BU_HOME_DIR . $eobu_directories_php_container . '/eobackup_launch.php', 0744);
			copy(EO_BU_TPL_PLUGIN_DIR . 'eobackup_log.php', EO_BU_HOME_DIR . $eobu_directories_php_container . '/eobackup_log.php');
			chmod(EO_BU_HOME_DIR . $eobu_directories_php_container . '/eobackup_log.php', 0744);
		}

		/* Declare options	*/
		add_action('admin_init', array('eobu_options', 'add_options'));

		/*	Call function to create the main left menu	*/
		add_action('admin_menu', array('eobu_init', 'plugin_menu'));

		/*	Add css call	*/
		add_action('admin_init', array('eobu_init', 'admin_css') );
		/*	Add js definition call	*/
		add_action('admin_head', array('eobu_init', 'admin_js_script'));
		/*	Add js call	*/
		add_action('admin_init', array('eobu_init', 'admin_js') );
	}

	/**
	*	Define the different menu for the plugin
	*
	*	@return void
	*/
	function plugin_menu(){
		/*	Check if the directories are set correctly before continuing	*/
		$params_are_set = eobu_options::check_mandatory_fields();
		if(!$params_are_set){
			add_menu_page(__('Sauvegarde serveur : ', 'eobackup') . __('Accueil', 'eobackup'), __('Sauvegarde serveur', 'eobackup'), 'eobu_menu_dashboard', EOBU_SLUG_DASHBOARD, array('eobu_display', 'missing_var'));

			add_options_page(__('Sauvegarde serveur : ', 'eobackup') . __('Options', 'eobackup'), __('Sauvegarde serveur', 'eobackup'), 'eobu_menu_options', EOBU_SLUG_OPTION, array('eobu_options', 'option_management_page'));

			add_action('admin_notices', array('eobu_options', 'missing_parameters_alert'));
		}
		else{
			add_menu_page(__('Sauvegarde serveur : ', 'eobackup') . __('Accueil', 'eobackup'), __('Sauvegarde serveur', 'eobackup'), 'eobu_menu_dashboard', EOBU_SLUG_DASHBOARD, array('eobu_display', 'dashboard'));

			add_submenu_page(EOBU_SLUG_DASHBOARD, __('Sauvegarde serveur : ', 'eobackup') . __('R&eacute;sum&eacute;', 'eobackup'), __('R&eacute;sum&eacute;', 'eobackup'), 'eobu_menu_dashboard', EOBU_SLUG_DASHBOARD, array('eobu_display', 'dashboard'));
			add_submenu_page(EOBU_SLUG_DASHBOARD, __('Sauvegarde serveur : ', 'eobackup') . __('Gestion des domaine', 'eobackup'), __('Domaines', 'eobackup'), 'eobu_menu_domain', EOBU_SLUG_DOMAIN, array('eobu_domain', 'domain_main_page'));
			add_submenu_page(EOBU_SLUG_DASHBOARD, __('Sauvegarde serveur : ', 'eobackup') . __('Historique des sauvegardes', 'eobackup'), __('Historique', 'eobackup'), 'eobu_menu_history', EOBU_SLUG_HISTORY, array('eobu_display', 'backup_history'));

			add_options_page(__('Sauvegarde serveur : ', 'eobackup') . __('Options', 'eobackup'), __('Sauvegarde serveur', 'eobackup'), 'eobu_menu_options', EOBU_SLUG_OPTION, array('eobu_options', 'option_management_page'));
		}
	}

	/**
	*	Define the different style for admin
	*
	*	@return void
	*/
	function admin_css(){
		wp_register_style('eobu_main_css', EO_BU_HOME_URL . 'css/eobackup.css', '', EOBU_PLUGIN_VERSION);
		wp_enqueue_style('eobu_main_css');

		wp_register_style('eobu_jq_custom', EO_BU_HOME_URL . 'css/jquery-ui-1.7.2.custom.css', '', EVA_PLUGIN_VERSION);
		wp_enqueue_style('eobu_jq_custom');
	}
	/**
	*	Define javascript file for admin
	*
	*	@return void
	*/
	function admin_js(){
		wp_enqueue_script('jquery');
		wp_enqueue_script('jquery-form');
		wp_enqueue_script('	jquery-ui-tabs ');
		wp_enqueue_script('eobu_jq-ui_min', EO_BU_HOME_URL . 'js/jquery-ui-min.js', '', EOBU_PLUGIN_VERSION);

		wp_enqueue_script('eobu_main_js', EO_BU_HOME_URL . 'js/eobackup.js', '', EOBU_PLUGIN_VERSION);

		/*	Include option script only on option page	*/
		if(isset($_GET['page']) && ($_GET['page'] == EOBU_SLUG_OPTION)){
			wp_enqueue_script('eobu_option_js', EO_BU_HOME_URL . 'js/eobackup_options.js', '', EOBU_PLUGIN_VERSION);
		}
	}
	/**
	*	
	*
	*	@return void
	*/
	function admin_js_script(){

		$locale = preg_replace('/([^_]+).+/', '$1', get_locale());
		$locale = ($locale == 'en') ? '' : $locale;

		echo '
<script type="text/javascript" >
	var EO_BU_LOCALE = "' . $locale . '";
	var EO_BU_AJAX_FILE = "' . EO_BU_INC_PLUGIN_URL . 'ajax.php";
	var EO_BU_MEDIA_PLUGIN_URL = "' . EO_BU_MEDIA_PLUGIN_URL . '";
	var EOBU_EXCLUDED_DOMAIN = "' . implode(', ', unserialize(EOBU_EXCLUDED_DOMAIN)) . '";
	var EOBU_DELETE_EXCLUDED_DOMAIN_TEXT = "' . __('Ne plus exclure ce domaine', 'eobackup') . '";
	var EOBU_DELETE_EMPTY_NEWDOMAIN_TEXT = "' . __('Vous n\'avez pas donn&eacute; de nom de domaine &agrave; exclure', 'eobackup') . '";
	var EOBU_DELETE_EMAIL_REPORT_TEXT = "' . __('Ne plus envoyer de rapport &agrave; cet email', 'eobackup') . '";
	var EOBU_DELETE_EMPTY_NEWEMAIL_REPORT_TEXT = "' . __('Vous n\'avez pas rempli le champs email', 'eobackup') . '";
	var EOBU_DELETE_EMPTY_NEWIP_REPORT_TEXT = "' . __('Vous n\'avez pas rempli le champs adresse', 'eobackup') . '";
	var EOBU_DSACTIVATE_BACKUP_TEXT = "' . __('&Ecirc;tes vous s&ucirc;r de vouloir d&eacute;sactiver les sauvegardes des domaines s&eacute;lectionn&eacute;s?', 'eobackup') . '";
	var EOBU_VIEW_LOG_TEXT = "' . __('Logs de la sauvegarde', 'eobackup') . '";
</script>';
	}

	/**
	*	Check the different date available on server and display an admin notice in case that all date are not equivalent
	*/
	function check_dates(){
		global $wpdb;

		/*	Php date	*/
		DEFINE('PHP_DATE', current_time('mysql', 0));
		/*	Mysql date	*/
		$query = $wpdb->prepare("SELECT NOW() as NOW");
		DEFINE('MYSQL_DATE', $wpdb->get_var($query));
		/*	Server date	*/
		exec("date '+%Y-%m-%d %X'", $result);
		DEFINE('SERVER_DATE', $result[0]);

		if((PHP_DATE != MYSQL_DATE) || (PHP_DATE != SERVER_DATE) || (MYSQL_DATE != SERVER_DATE)){
			add_action('admin_notices', array('eobu_init', 'notice_dates'));
		}
	}

	function notice_dates(){
		echo '<div class="error">
       <p>' . sprintf(__('Une diff&eacute;rence entre les diff&eacute;rentes dates du serveur a &eacute;t&eacute; d&eacute;tect&eacute;e<br/>Date php : %s<br/>Date mysql : %s<br/>Date serveur : %s', 'eobackup'), PHP_DATE, MYSQL_DATE, SERVER_DATE) . '</p>
    </div>';
	}
}