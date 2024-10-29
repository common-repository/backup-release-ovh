<?php
/*
Plugin Name: Backup release ovh
Plugin URI: http://www.eoxia.com
Description: Plugin de gestion des sauvegardes pour les domaines cr&eacute;&eacute;s sur les serveurs d'ovh sous Release 2
Version: 0.2
Author: Eoxia
Author URI: http://www.eoxia.com
*/

/**
* Plugin main file.
* 
*	This file is the main file called by wordpress for our plugin use. It define the basic vars and include the different file needed to use the plugin
* @author Eoxia <dev@eoxia.com>
* @version 0.1
* @package backup_release_ovh
*/

/*	Define a var allowing to know current version of plugin	*/
DEFINE('EOBU_PLUGIN_VERSION', '0.2');

/*	First thing we define the main directory for our plugin in a super global var	*/
DEFINE('EO_BU', basename(dirname(__FILE__)));

/*	Include the different config for the plugin	*/
require_once(WP_PLUGIN_DIR . '/' . EO_BU . '/include/config.php' );

/*	Include the file which includes the different files used by all the plugin	*/
require_once(EO_BU_INC_PLUGIN_DIR . 'include.php');

/*	Call function allowing to set plugin default permission	*/
add_action('admin_init', array('eobu_capabilities', 'init_capabilities'));

/*	On plugin loading, call the different element for creation output for our plugin	*/
add_action('plugins_loaded', array('eobu_init', 'plugin_load'));
