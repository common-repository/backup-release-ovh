<?php
/**
*	Plugin include file
* 
* Define the different file including into plugin
* @author Eoxia <dev@eoxia.com>
* @version 0.1
* @package backup_release_ovh
* @subpackage include
*/

/**
*	Include tools that will launch different action when plugin will be loaded
*/
require_once(EO_BU_LIB_PLUGIN_DIR . 'init.class.php' );

/**
*	Include tools that will launch different action when plugin will be loaded
*/
require_once(EO_BU_LIB_PLUGIN_DIR . 'db/db.class.php' );

/**
*	Include capabilities file definition
*/
require_once(EO_BU_LIB_PLUGIN_DIR . 'capabilities.class.php' );

/**
*	Include options functionnalities
*/
require_once(EO_BU_LIB_PLUGIN_DIR . 'options.class.php' );
/**
*	Include display functionnalities
*/
require_once(EO_BU_LIB_PLUGIN_DIR . 'display.class.php' );

/**
*	Include domain functionnalities
*/
require_once(EO_BU_LIB_PLUGIN_DIR . 'domain.class.php' );

/**
*	Include backup functionnalities
*/
require_once(EO_BU_LIB_PLUGIN_DIR . 'backup.class.php' );