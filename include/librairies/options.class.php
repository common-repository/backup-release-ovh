<?php
/**
* Plugin options' management
* 
* Define the settings page, with the different field to output and field's validators
* @author Eoxia <dev@eoxia.com>
* @version 1.0
* @package backup_release_ovh
* @subpackage librairies
*/

/**
* Define the settings page, with the different field to output and field's validators
* @package backup_release_ovh
* @subpackage librairies
*/
class eobu_options
{
	/**
	*	Declare the different options for the plugin
	*
	*	@return void
	*/
	function add_options(){
		register_setting('eobu_db_options', 'eobu_db_options');
		register_setting('eobu_schedule_options', 'eobu_schedule_options');
		register_setting('eobu_options', 'eobu_options', array('eobu_options', 'eobu_options_validate'));

		/* Declare the general options	*/
			add_settings_section('eobu_main_options', __('Informations de bases (Obligatoires)', 'eobackup'), array('eobu_options', 'main_options_output'), 'eobu_options_main');
			/*	Add the different field for current section	*/
				add_settings_field('eobu_server', __('Nom du serveur', 'eobackup'), array('eobu_options', 'eobu_server'), 'eobu_options_main', 'eobu_main_options');

			add_settings_section('eobu_main_options', __('Informations pour les sauvegardes (Obligatoires)', 'eobackup'), array('eobu_options', 'backup_options_output'), 'eobu_options_backup');
			/*	Add the different field for current section	*/
				add_settings_field('eobu_directories_sh_container', __('Dossier contenant les scripts sh (Sauvegarde et Nettoyage)', 'eobackup'), array('eobu_options', 'eobu_directories_sh_container'), 'eobu_options_backup', 'eobu_main_options');
				add_settings_field('eobu_directories_php_container', __('Dossier contenant les scripts php de lancement', 'eobackup'), array('eobu_options', 'eobu_directories_php_container'), 'eobu_options_backup', 'eobu_main_options');
				add_settings_field('eobu_directories_REPBACKUP', __('Dossier de destination des sauvegardes', 'eobackup'), array('eobu_options', 'eobu_directories_REPBACKUP'), 'eobu_options_backup', 'eobu_main_options');
				add_settings_field('eobu_sleep_time', __('Temporisation en secondes entre les domaines (par d&eacute;faut)', 'eobackup'), array('eobu_options', 'eobu_sleep_time'), 'eobu_options_backup', 'eobu_main_options');

			add_settings_section('eobu_main_options', __('Informations pour les logs des sauvegardes (Obligatoires)', 'eobackup'), array('eobu_options', 'backup_log_options_output'), 'eobu_options_backup_log');
			/*	Add the different field for current section	*/
				add_settings_field('eobu_directories_REPLOG', __('Dossier de destination des logs', 'eobackup'), array('eobu_options', 'eobu_directories_REPLOG'), 'eobu_options_backup_log', 'eobu_main_options');
				add_settings_field('eobu_email', __('Emails pour les rapports', 'eobackup'), array('eobu_options', 'eobu_email'), 'eobu_options_backup_log', 'eobu_main_options');

			add_settings_section('eobu_main_options', __('Domaines &agrave; exclure de la liste des sauvegardes', 'eobackup'), array('eobu_options', 'domain_options_output'), 'eobu_options_domain');
			/*	Add the different field for current section	*/
				add_settings_field('eobu_excluded_domain', __('Domaine &agrave; ne pas sauvegarder', 'eobackup'), array('eobu_options', 'eobu_excluded_domain'), 'eobu_options_domain', 'eobu_main_options');

			add_settings_section('eobu_main_options', __('Informations pour les tests de restauration des sauvegardes', 'eobackup'), array('eobu_options', 'restoration_options_output'), 'eobu_options_restoration');
			/*	Add the different field for current section	*/
				add_settings_field('eobu_restoration_domain_name', __('Nom du sous domaine de restauration', 'eobackup'), array('eobu_options', 'eobu_restoration_domain_name'), 'eobu_options_restoration', 'eobu_main_options');
				add_settings_field('eobu_restoration_db_name', __('Nom de la base de donn&eacute;es de restauration', 'eobackup'), array('eobu_options', 'eobu_restoration_db_name'), 'eobu_options_restoration', 'eobu_main_options');
				add_settings_field('eobu_restoration_db_user', __('Nom de l\'utilisateur de la base de donn&eacute;es de restauration', 'eobackup'), array('eobu_options', 'eobu_restoration_db_user'), 'eobu_options_restoration', 'eobu_main_options');
				add_settings_field('eobu_restoration_db_pass', __('Mot de passe de l\'utilisateur de la base de donn&eacute;es de restauration', 'eobackup'), array('eobu_options', 'eobu_restoration_db_pass'), 'eobu_options_restoration', 'eobu_main_options');

			add_settings_section('eobu_main_options', __('R&eacute;glages par d&eacute;faut pour la configuration des sauvegardes', 'eobackup'), array('eobu_options', 'default_options_output'), 'eobu_options_default');
			/*	Add the different field for current section	*/
				add_settings_field('eobu_rotation_nb', __('Nombre de Sauvegardes &agrave; conserver', 'eobackup'), array('eobu_options', 'eobu_rotation_nb'), 'eobu_options_default', 'eobu_main_options');

			add_settings_section('eobu_main_options', __('R&eacute;glages divers', 'eobackup'), array('eobu_options', 'others_options_output'), 'eobu_options_others');
			/*	Add the different field for current section	*/
				add_settings_field('eobu_per_page_element_nb', __('Nombre d\'&eacute;l&eacute;ments &agrave; afficher par page', 'eobackup'), array('eobu_options', 'eobu_per_page_element_nb'), 'eobu_options_others', 'eobu_main_options');
				add_settings_field('eobu_send_mail_for_moderated_unconfigured_domain', __('Inclure les domaines mod&eacute;r&eacute;s dans les mails d\'alerte de configuration', 'eobackup'), array('eobu_options', 'eobu_send_mail_for_moderated_unconfigured_domain'), 'eobu_options_others', 'eobu_main_options');
				add_settings_field('eobu_check_unconfigured_domain_at_global_backup', __('V&eacute;rifier les domaines non configur&eacute;s lors de la sauvegarde g&eacute;n&eacute;rale', 'eobackup'), array('eobu_options', 'eobu_check_unconfigured_domain_at_global_backup'), 'eobu_options_others', 'eobu_main_options');
				add_settings_field('eobu_allow_multiple_backup_one_day', __('Autoriser les sauvegardes multiples pour un domaine dans la m&ecirc;me journ&eacute;e', 'eobackup'), array('eobu_options', 'eobu_allow_multiple_backup_one_day'), 'eobu_options_others', 'eobu_main_options');
				add_settings_field('eobu_log_exec_command_result', __('Enregistrer le r&eacute;sultat des commandes shell lanc&eacute;es par php', 'eobackup'), array('eobu_options', 'eobu_log_exec_command_result'), 'eobu_options_others', 'eobu_main_options');
				add_settings_field('eobu_allowed_ip', __('Liste des adresses IP autoris&eacute;es &agrave; lancer le script (Pour les tests)', 'eobackup'), array('eobu_options', 'eobu_allowed_ip'), 'eobu_options_others', 'eobu_main_options');
	}
	/**
	*	Return an specific option value
	*
	*	@param string $option_name The option name we want to get the value for
	*	@param string $option_name The option we want to get the value for
	*
	*	@return mixed $optionSubValue The value of the option
	*/
	function get_specific_option($option_name, $subOptionName){
		$optionSubValue = -1;

		$optionValue = get_option($option_name);
		if($optionValue != ''){
			if(is_string($optionValue)){
				$optionValue = unserialize($optionValue);
			}
			if(is_array($subOptionName)){
				$optionSubValue = array();
				foreach($subOptionName as $option_name){
					$optionSubValue[$option_name] = $optionValue[$option_name];
				}
			}
			else{
				$optionSubValue = $optionValue[$subOptionName];
			}
		}

		return $optionSubValue;
	}
	/**
	*	Update the database option
	*
	* @param string $option_name The sub option name we want to update
	* @param string $sub_option The sub option name we want to update
	* @param string $value the sub option value we want to put
	*
	*/
	function set_option_value($option_name, $sub_option, $value){
		$option = get_option($option_name);

		if(is_string($option)){
			$optionValue = unserialize($optionValue);
			$optionSubValue = $optionValue[$subOptionName];
			update_option($option_name, serialize($optionValue));
		}
		else{
			$option[$sub_option] = $value;
			update_option($option_name, $option);
		}
	}

	/**	
	*	
	*
	*	@return string A admin notice to alert user that plugin is not configured correctly
	*/
	function missing_parameters_alert(){
    echo '<div class="error">
       <p>' . sprintf(__('Sauvegarde serveur : Certaines configurations sont manquantes. Vous ne pourrez pas utiliser le plugin tant que vous n\'aurez pas %s', 'eobackup'), '<a href="' . admin_url("options-general.php?page=" . EOBU_SLUG_OPTION) . '" >' . __('configur&eacute; ces variables', 'eobackup') . '</a>') . '</p>
    </div>';
	}

	/**
	*	Create the html ouput code for the options page
	*
	*	@return The html code to output for option page
	*/
	function option_management_page(){
		/*	Check if the directories are set correctly before continuing	*/
		$params_are_set = eobu_options::check_mandatory_fields();

		echo eobu_display::start_page(__('R&eacute;glages pour la gestion des sauvegardes', 'eobackup'), '', '', '');
?>
<div id="loading_picture" class="eobackup_hide" ><img src="<?php echo EO_BU_MEDIA_PLUGIN_URL; ?>loading.gif" alt="<?php __('Chargement en cours...', 'eobackup'); ?>" title="<?php __('Chargement en cours...', 'eobackup'); ?>" /></div>
<form action="options.php" method="post" id="option_form" >
	<div id="eobu_options_main" ><?php do_settings_sections('eobu_options_main'); ?></div>
	<div id="eobu_options_backup" ><?php do_settings_sections('eobu_options_backup'); ?></div>
	<div id="eobu_options_backup_log" ><?php do_settings_sections('eobu_options_backup_log'); ?></div>

	<div id="eobu_options_domain" ><?php do_settings_sections('eobu_options_domain'); ?></div>

	<div id="eobu_options_restoration" ><?php do_settings_sections('eobu_options_restoration'); ?></div>

	<div id="eobu_options_default" ><?php do_settings_sections('eobu_options_default'); ?></div>

	<div id="eobu_options_others" ><?php do_settings_sections('eobu_options_others'); ?></div>
<?php
		settings_fields('eobu_options');
if(current_user_can('eobu_edit_options')){
?>
	<input class="button-primary alignright" name="Submit" type="submit" value="<?php esc_attr_e('Save Changes'); ?>" />
<?php
}
?>
</form>
<?php
if(current_user_can('eobu_edit_options')){
?>
<div id="sh_script_generation_container" class="alignright" >
	<form action="<?php echo EO_BU_INC_PLUGIN_URL; ?>ajax.php" method="post" id="backup_sh_script_generator" >
		<input type="hidden" name="action" id="action" value="generate_new_sh_file" />
<?php
	if($params_are_set){
?>
			<input class="secondary-primary" name="generate_cron_script" id="generate_cron_script" type="submit" value="<?php esc_attr_e('G&eacute;n&eacute;rer les fichiers .sh', 'eobackup'); ?>" />
			<div id="script_file_container" >
<?php
		if(is_file(EO_BU_GENERATED_DOC_DIR . sprintf(EOBU_BACKUP_SH, EOBU_BACKUP_SH_VERSION))){
			echo '<a href="' . EO_BU_GENERATED_DOC_URL . sprintf(EOBU_BACKUP_SH, EOBU_BACKUP_SH_VERSION) . '" >' . __('T&eacute;l&eacute;charger le fichier de sauvegarde', 'eobackup') . '</a>';
		}
		if(is_file(EO_BU_GENERATED_DOC_DIR . sprintf(EOBU_BACKUP_REMOVER_SH, EOBU_BACKUP_REMOVER_SH_VERSION))){
			echo '<br/><a href="' . EO_BU_GENERATED_DOC_URL . sprintf(EOBU_BACKUP_REMOVER_SH, EOBU_BACKUP_REMOVER_SH_VERSION) . '" >' . __('T&eacute;l&eacute;charger le fichier de nettoyage', 'eobackup') . '</a>';
		}
			
?>
			</div>
<?php
	}
?>
	</form>
</div>
<?php
}
		echo eobu_display::end_page();
	}

	/**
	*	Validate the different data sent for the option
	*
	*	@param array $input An array which will receive the values sent by the user with the form
	*
	*	@return array $newinput An array with the send values cleaned for more secure usage
	*/
	function eobu_options_validate($sent_option){
		$option_to_save = array();

		foreach($sent_option as $option_name => $option_value){
			switch($option_name){
				case 'eobu_directories_REPBACKUP':
				case 'eobu_directories_sh_container':
				case 'eobu_directories_php_container':
				case 'eobu_directories_REPLOG':{
					$option_value = trim(str_replace(' ', '', str_replace('	', '', (substr($option_value, -1) == '/') ? substr($option_value, 0, -1) : $option_value)));

					if(($option_name == 'eobu_directories_REPBACKUP') && (in_array($option_value . '/', unserialize(EOBU_FORBIDDEN_DIR)))){
						$option_value = '';
					}

					$option_to_save[$option_name] = $option_value;

					/*	Create directory for php file container	*/
					if(($option_name == 'eobu_directories_php_container') && ($option_value != '')){
						if(!is_dir(EO_BU_HOME_DIR . $option_value)){
							mkdir(EO_BU_HOME_DIR . $option_value, 0755, true);
						}
						copy(EO_BU_TPL_PLUGIN_DIR . 'eobackup_launch.php', EO_BU_HOME_DIR . $option_value . '/eobackup_launch.php');
						chmod(EO_BU_HOME_DIR . $option_value . '/eobackup_launch.php', 0744);
						copy(EO_BU_TPL_PLUGIN_DIR . 'eobackup_log.php', EO_BU_HOME_DIR . $option_value . '/eobackup_log.php');
						chmod(EO_BU_HOME_DIR . $option_value . '/eobackup_launch.php', 0744);
					}
				}break;
				case 'eobu_email':{
					foreach($option_value as $email){
						if(is_email($email)){
							$option_to_save[$option_name][] = $email;
						}
					}
				}break;
				case 'eobu_per_page_element_nb':{
					$option_to_save[$option_name] = $option_value;
					if($option_value <= 0){
						$option_to_save[$option_name] = 1;
					}
				}break;
				default:{
					$option_to_save[$option_name] = $option_value;
				}break;
			}
		}

		return $option_to_save;
	}

	/**
	*	Function allowing to set a explication area for the settings section
	*/
	function main_options_output(){

	}
	/**
	*	Function allowing to set a explication area for the settings section
	*/
	function domain_options_output(){

	}
	/**
	*	Function allowing to set a explication area for the settings section
	*/
	function backup_options_output(){

	}
	/**
	*	Function allowing to set a explication area for the settings section
	*/
	function backup_log_options_output(){

	}
	/**
	*	Function allowing to set a explication area for the settings section
	*/
	function restoration_options_output(){

	}
	/**
	*	Function allowing to set a explication area for the settings section
	*/
	function default_options_output(){

	}
	/**
	*	Function allowing to set a explication area for the settings section
	*/
	function others_options_output(){

	}


	/**
	*	Define the output fot the field. Get the option value to put the good value by default
	*/
	function eobu_server(){
		$options = get_option('eobu_options');
		if(current_user_can('eobu_edit_options')){
			echo '<input type="text" value="' . $options['eobu_server'] . '" name="eobu_options[eobu_server]" id="eobu_server" class="eobu_mandatory_field" />';
		}
		else{
			echo $options['eobu_server'];
		}
	}	


	/**
	*	Define the output fot the field. Get the option value to put the good value by default
	*/
	function eobu_excluded_domain(){
		$current_db_version = eobu_options::get_specific_option('eobu_db_options', 'db_version');
		$current_db_version = ($current_db_version > 0) ? $current_db_version : '0';

		$eobu_excluded_domain = '';

		$options = get_option('eobu_options');
		$options['eobu_excluded_domain'] = ($options['eobu_excluded_domain'] != '') ? $options['eobu_excluded_domain'] : (($current_db_version < 1) ? unserialize(EOBU_EXCLUDED_DOMAIN) : '');
		if(is_array($options['eobu_excluded_domain'])){
			foreach($options['eobu_excluded_domain'] as $index => $domain){
				if(current_user_can('eobu_edit_options') && ($domain != '')){
					$eobu_excluded_domain .= '<div id="eobu_excluded_domain_' . $index . '" ><input type="text" value="' . $domain . '" name="eobu_options[eobu_excluded_domain][]" /><img src="' . EO_BU_MEDIA_PLUGIN_URL . 'delete.png" alt="' . __('Ne plus exclure ce domaine', 'eobackup') . '" title="' . __('Ne plus exclure ce domaine', 'eobackup') . '" class="delete_domain_to_exclude" /></div>';
				}
				else{
					$eobu_excluded_domain .= $domain . '<br/>';
				}
			}
		}
		else{
			$eobu_excluded_domain .= '<div id="use_default_list" ><span>' . __('Utiliser la liste par d&eacute;faut', 'eobackup') . '</span><img src="' . EO_BU_MEDIA_PLUGIN_URL . 'add.png" alt="' . __('Ins&eacute;rer les domaines par d&eacute;fault dans la liste des exclusions', 'eobackup') . '" title="' . __('Ins&eacute;rer les domaines par d&eacute;fault dans la liste des exclusions', 'eobackup') . '" class="add_default_domain_to_exclude" /></div>';
		}

		if(current_user_can('eobu_edit_options')){
			$eobu_excluded_domain = '<input type="text" value="" name="new_excluded_domain" id="new_excluded_domain" /><img src="' . EO_BU_MEDIA_PLUGIN_URL . 'add.png" alt="' . __('Ajouter ce domaine dans la liste des exclusions', 'eobackup') . '" title="' . __('Ajouter ce domaine dans la liste des exclusions', 'eobackup') . '" class="add_new_domain_to_exclude" />
<fieldset >
	<legend>' . __('Liste des domaines exclus automatiquement des sauvegardes', 'eobackup') . '</legend>
	<div id="excluded_domain_list" >' . $eobu_excluded_domain . '</div>
</fieldset>';
		}

		echo $eobu_excluded_domain;
	}


	/**
	*	Define the output fot the field. Get the option value to put the good value by default
	*/
	function eobu_directories_REPBACKUP(){
		$options = get_option('eobu_options');
		if(current_user_can('eobu_edit_options')){
			echo '<input type="text" value="' . $options['eobu_directories_REPBACKUP'] . '" name="eobu_options[eobu_directories_REPBACKUP]" id="eobu_directories_REPBACKUP" class="eobu_mandatory_field" />&nbsp;/<br/>' . sprintf(__('Les dossiers %s sont interdits', 'eobackup'), '<span class="eobu_strong" >' . implode(', ', unserialize(EOBU_FORBIDDEN_DIR)) . '</span>');
		}
		else{
			echo $options['eobu_directories_REPBACKUP'] . '&nbsp;/';
		}
	}
	/**
	*	Define the output fot the field. Get the option value to put the good value by default
	*/
	function eobu_directories_sh_container(){
		$options = get_option('eobu_options');
		$output = '<div class="eobu_explanation" >' . __('Si ce dossier n\'existe pas sur le serveur vous devrez le cr&eacute;er et envoyer les fichiers *.sh que vous aurez t&eacute;l&eacute;charg&eacute; apr&egrave;s avoir termin&eacute; la configuration', 'eobackup') . '</div>';
		if(current_user_can('eobu_edit_options')){
			$output .= '<input type="text" value="' . $options['eobu_directories_sh_container'] . '" name="eobu_options[eobu_directories_sh_container]" id="eobu_directories_sh_container" class="eobu_mandatory_field" />&nbsp;/';
		}
		else{
			$output .= $options['eobu_directories_sh_container'] . '&nbsp;/';
		}

		echo $output;
	}
	/**
	*	Define the output fot the field. Get the option value to put the good value by default
	*/
	function eobu_directories_php_container(){
		$options = get_option('eobu_options');
		if(current_user_can('eobu_edit_options')){
			echo EO_BU_HOME_DIR . '<input type="text" value="' . $options['eobu_directories_php_container'] . '" name="eobu_options[eobu_directories_php_container]" id="eobu_directories_php_container" class="eobu_mandatory_field" />&nbsp;/eobackup_*.php';
			if(!empty($options['eobu_directories_php_container'])){
				echo '<br/>' . __('Commande de la t&acirc;che cron pour lancer les sauvegardes', 'eobackup')  . '&nbsp;:&nbsp;<span class="eobu_strong" >' . EO_BU_HOME_DIR . $options['eobu_directories_php_container'] . '/eobackup_launch.php 1> /dev/null 2> /dev/null</span>';
			}
		}
		else{
			echo $options['eobu_directories_php_container'] . '&nbsp;/';
		}
	}
	/**
	*	Define the output fot the field. Get the option value to put the good value by default
	*/
	function eobu_directories_REPLOG(){
		$options = get_option('eobu_options');
		if(current_user_can('eobu_edit_options')){
			echo '<input type="text" value="' . $options['eobu_directories_REPLOG'] . '" name="eobu_options[eobu_directories_REPLOG]" id="eobu_directories_REPLOG" class="eobu_mandatory_field" />&nbsp;/';
		}
		else{
			echo $options['eobu_directories_REPLOG'] . '&nbsp;/';
		}
	}


	/**
	*	Define the output fot the field. Get the option value to put the good value by default
	*/
	function eobu_restoration_domain_name(){
		$options = get_option('eobu_options');
		if(current_user_can('eobu_edit_options')){
			echo '<input type="text" value="' . $options['eobu_restoration_domain_name'] . '" name="eobu_options[eobu_restoration_domain_name]" id="eobu_restoration_domain_name" />';
		}
		else{
			echo $options['eobu_restoration_domain_name'];
		}
	}
	/**
	*	Define the output fot the field. Get the option value to put the good value by default
	*/
	function eobu_restoration_db_name(){
		$options = get_option('eobu_options');
		if(current_user_can('eobu_edit_options')){
			echo '<input type="text" value="' . $options['eobu_restoration_db_name'] . '" name="eobu_options[eobu_restoration_db_name]" id="eobu_restoration_db_name" />';
		}
		else{
			echo $options['eobu_restoration_db_name'];
		}
	}
	/**
	*	Define the output fot the field. Get the option value to put the good value by default
	*/
	function eobu_restoration_db_user(){
		$options = get_option('eobu_options');
		if(current_user_can('eobu_edit_options')){
			echo '<input type="text" value="' . $options['eobu_restoration_db_user'] . '" name="eobu_options[eobu_restoration_db_user]" id="eobu_restoration_db_user" />';
		}
		else{
			echo $options['eobu_restoration_db_user'];
		}
	}


	/**
	*	Define the output fot the field. Get the option value to put the good value by default
	*/
	function eobu_restoration_db_pass(){
		$options = get_option('eobu_options');
		if(current_user_can('eobu_edit_options')){
			echo '<input type="password" value="' . $options['eobu_restoration_db_pass'] . '" name="eobu_options[eobu_restoration_db_pass]" id="eobu_restoration_db_pass" />';
		}
		else{
			echo '*******';
		}
	}


	/**
	*	Define the output fot the field. Get the option value to put the good value by default
	*/
	function eobu_email(){
		$eobu_email = '';

		$options = get_option('eobu_options');

		if(is_array($options['eobu_email'])){
			foreach($options['eobu_email'] as $index => $email){
				if(current_user_can('eobu_edit_options') && ($email != '')){
					$eobu_email .= '<div id="eobu_email_' . $index . '" ><input type="text" value="' . $email . '" name="eobu_options[eobu_email][]" /><img src="' . EO_BU_MEDIA_PLUGIN_URL . 'delete.png" alt="' . __('Ne plus envoyer &agrave; cet email', 'eobackup') . '" title="' . __('Ne plus envoyer &agrave; cet email', 'eobackup') . '" class="delete_email_report" /></div>';
				}
				else{
					$eobu_email .= $email . '<br/>';
				}
			}
		}
		elseif($options['eobu_email'] != ''){
			if(current_user_can('eobu_edit_options')){
				$eobu_email .= '<div id="eobu_email_' . $index . '" ><input type="text" value="' . $options['eobu_email'] . '" name="eobu_options[eobu_email][]" /><img src="' . EO_BU_MEDIA_PLUGIN_URL . 'delete.png" alt="' . __('Ne plus envoyer &agrave; cet email', 'eobackup') . '" title="' . __('Ne plus envoyer &agrave; cet email', 'eobackup') . '" class="delete_email_report" /></div>';
			}
			else{
				$eobu_email .= $options['eobu_email'] . '<br/>';
			}
		}

		if(current_user_can('eobu_edit_options')){
			$eobu_email = '<input type="text" value="" name="new_report_email" id="new_report_email" /><img src="' . EO_BU_MEDIA_PLUGIN_URL . 'add.png" alt="' . __('Ajouter cet email &agrave; la liste', 'eobackup') . '" title="' . __('Ajouter cet email &agrave; la liste', 'eobackup') . '" class="add_new_email_report" />
<fieldset>
	<legend>' . __('Liste des emails recevant un rapport apr&eacute;s les sauvegardes', 'eobackup') . '</legend>
	<div id="email_report_list" >' . $eobu_email . '</div>
</fieldset>';
		}

		echo $eobu_email;
	}	


	/**
	*	Define the output fot the field. Get the option value to put the good value by default
	*/
	function eobu_allowed_ip(){
		$eobu_allowed_ip = '';

		$options = get_option('eobu_options');

		if(is_array($options['eobu_allowed_ip'])){
			foreach($options['eobu_allowed_ip'] as $index => $email){
				if(current_user_can('eobu_edit_options') && ($email != '')){
					$eobu_allowed_ip .= '<div id="eobu_email_' . $index . '" ><input type="text" value="' . $email . '" name="eobu_options[eobu_allowed_ip][]" /><img src="' . EO_BU_MEDIA_PLUGIN_URL . 'delete.png" alt="' . __('Ne plus autoriser cette adresse IP', 'eobackup') . '" title="' . __('Ne plus autoriser cette adresse IP', 'eobackup') . '" class="delete_allowed_ip_address" /></div>';
				}
				else{
					$eobu_allowed_ip .= $email . '<br/>';
				}
			}
		}

		if(current_user_can('eobu_edit_options')){
			$eobu_allowed_ip = '<input type="text" value="" name="new_allowed_ip_address" id="new_allowed_ip_address" /><img src="' . EO_BU_MEDIA_PLUGIN_URL . 'add.png" alt="' . __('Ajouter cet email &agrave; la liste', 'eobackup') . '" title="' . __('Ajouter cet email &agrave; la liste', 'eobackup') . '" class="add_new_ip_address" />
<fieldset>
	<legend>' . __('Liste des adresse IP autoris&eacute;es &agrave; lancer la sauvegarde (Lors des tests)', 'eobackup') . '</legend>
	<div id="allowed_ip_address_list" >' . $eobu_allowed_ip . '</div>
</fieldset>';
		}

		echo $eobu_allowed_ip;
	}	


	/**
	*	Define the output fot the field. Get the option value to put the good value by default
	*/
	function eobu_rotation_nb(){
		$options = get_option('eobu_options');
		if(current_user_can('eobu_edit_options')){
			echo '<input type="text" value="' . $options['eobu_rotation_nb'] . '" name="eobu_options[eobu_rotation_nb]" id="eobu_rotation_nb" />';
		}
		else{
			echo $options['eobu_rotation_nb'] . '<br/>';
		}
	}
	/**
	*	Define the output fot the field. Get the option value to put the good value by default
	*/
	function eobu_sleep_time(){
		$options = get_option('eobu_options');
		if(current_user_can('eobu_edit_options')){
			echo '<input type="text" value="' . $options['eobu_sleep_time'] . '" name="eobu_options[eobu_sleep_time]" id="eobu_sleep_time" class="eobu_mandatory_field" />';
		}
		else{
			echo $options['eobu_sleep_time'] . '<br/>';
		}
	}	
	
	/**
	*	Define the output fot the field. Get the option value to put the good value by default
	*/
	function eobu_per_page_element_nb(){
		$options = get_option('eobu_options');
		if(current_user_can('eobu_edit_options')){
			echo '<input type="text" value="' . $options['eobu_per_page_element_nb'] . '" name="eobu_options[eobu_per_page_element_nb]" id="eobu_per_page_element_nb" />';
		}
		else{
			echo $options['eobu_per_page_element_nb'] . '<br/>';
		}
	}		

	/**
	*	Define the output fot the field. Get the option value to put the good value by default
	*/
	function eobu_send_mail_for_moderated_unconfigured_domain(){
		global $eobu_yes_or_no_option_list;
		$options = get_option('eobu_options');
		if(current_user_can('eobu_edit_options')){
			echo eobu_display::form_input_select('eobu_options[eobu_send_mail_for_moderated_unconfigured_domain]', 'eobu_send_mail_for_moderated_unconfigured_domain', $eobu_yes_or_no_option_list, $options['eobu_send_mail_for_moderated_unconfigured_domain'], '', 'index');
		}
		else{
			echo $options['eobu_send_mail_for_moderated_unconfigured_domain'] . '<br/>';
		}
	}	
	/**
	*	Define the output fot the field. Get the option value to put the good value by default
	*/
	function eobu_check_unconfigured_domain_at_global_backup(){
		global $eobu_yes_or_no_option_list;
		$options = get_option('eobu_options');
		if(current_user_can('eobu_edit_options')){
			echo eobu_display::form_input_select('eobu_options[eobu_check_unconfigured_domain_at_global_backup]', 'eobu_check_unconfigured_domain_at_global_backup', $eobu_yes_or_no_option_list, $options['eobu_check_unconfigured_domain_at_global_backup'], '', 'index');
		}
		else{
			echo $options['eobu_check_unconfigured_domain_at_global_backup'] . '<br/>';
		}
	}	
	/**
	*	Define the output fot the field. Get the option value to put the good value by default
	*/
	function eobu_allow_multiple_backup_one_day(){
		global $eobu_yes_or_no_option_list;
		$options = get_option('eobu_options');
		if(current_user_can('eobu_edit_options')){
			echo eobu_display::form_input_select('eobu_options[eobu_allow_multiple_backup_one_day]', 'eobu_allow_multiple_backup_one_day', $eobu_yes_or_no_option_list, $options['eobu_allow_multiple_backup_one_day'], '', 'index');
		}
		else{
			echo $options['eobu_allow_multiple_backup_one_day'] . '<br/>';
		}
	}	
	/**
	*	Define the output fot the field. Get the option value to put the good value by default
	*/
	function eobu_log_exec_command_result(){
		global $eobu_yes_or_no_option_list;
		$options = get_option('eobu_options');
		if(current_user_can('eobu_edit_options')){
			echo eobu_display::form_input_select('eobu_options[eobu_log_exec_command_result]', 'eobu_log_exec_command_result', $eobu_yes_or_no_option_list, $options['eobu_log_exec_command_result'], '', 'index');
		}
		else{
			echo $options['eobu_log_exec_command_result'] . '<br/>';
		}
	}	


	/**
	*
	*/
	function check_mandatory_fields(){
		$mandatory_parameters_set = true;

		$optionValue = get_option('eobu_options');
		if($optionValue != ''){
			if(is_string($optionValue)){
				$optionValue = unserialize($optionValue);
			}

			$eobu_server = trim($optionValue['eobu_server']);
			$eobu_directories_REPBACKUP = trim($optionValue['eobu_directories_REPBACKUP']);
			$eobu_directories_sh_container = trim($optionValue['eobu_directories_sh_container']);
			$eobu_directories_php_container = trim($optionValue['eobu_directories_php_container']);
			$eobu_directories_REPLOG = trim($optionValue['eobu_directories_REPLOG']);
			$eobu_email = $optionValue['eobu_email'];

			if(empty($eobu_server) || empty($eobu_directories_REPBACKUP) || empty($eobu_directories_sh_container) || empty($eobu_directories_php_container) || empty($eobu_directories_REPLOG) || empty($eobu_email)){
				$mandatory_parameters_set = false;
			}
		}
		else{
			$mandatory_parameters_set = false;
		}


		return $mandatory_parameters_set;
	}


}