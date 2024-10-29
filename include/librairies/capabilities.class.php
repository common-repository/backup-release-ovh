<?php
/**
* Plugin permissions management
* 
* Define method to manage capabilities for the plugin
* @author Eoxia <dev@eoxia.com>
* @version 0.1
* @package backup_release_ovh
* @subpackage librairies
*/

/**
* Define method to manage capabilities for the plugin
* @package backup_release_ovh
* @subpackage librairies
*/
class eobu_capabilities
{

	/**
	*	Define the different capabilities for users in plugin
	*
	*	@return array $capabilities The entire list of available capabilities
	*/
	function capabilities_list(){
		$capabilities = array();

		$capabilities['eobu_menu_dashboard'] = array('set_by_default' => 'no', 'capability_type' => 'read', 'capability_sub_type' => '', 'capability_module' => 'menu', 'capability_sub_module' => 'dashboard');
		$capabilities['eobu_menu_domain'] = array('set_by_default' => 'no', 'capability_type' => 'read', 'capability_sub_type' => '', 'capability_module' => 'menu', 'capability_sub_module' => 'domain');
		$capabilities['eobu_menu_options'] = array('set_by_default' => 'no', 'capability_type' => 'read', 'capability_sub_type' => '', 'capability_module' => 'menu', 'capability_sub_module' => 'options');
		$capabilities['eobu_menu_history'] = array('set_by_default' => 'no', 'capability_type' => 'read', 'capability_sub_type' => '', 'capability_module' => 'menu', 'capability_sub_module' => 'history');

		$capabilities['eobu_domain_view_list'] = array('set_by_default' => 'no', 'capability_type' => 'read', 'capability_sub_type' => '', 'capability_module' => 'domain', 'capability_sub_module' => '');

		$capabilities['eobu_edit_options'] = array('set_by_default' => 'no', 'capability_type' => 'write', 'capability_sub_type' => 'edit', 'capability_module' => 'options', 'capability_sub_module' => '');

		return $capabilities;
	}

	/**
	*	Initialise default capabilities when plugin is loaded
	*/
	function init_capabilities(){
		global $eobu__wp_role;

		/*	Check if wordpress role is already loaded	*/
		if(!is_object($eobu__wp_role)){
			$eobu__wp_role = new WP_Roles();
		}

		/*	Get existing capabilities	*/
		$caps = self::capabilities_list();

		/*	Get administrator role for capabilities affectation	*/
		$role = get_role('administrator');
		/*	Affect capabilities to role	*/
		foreach($caps as $cap => $cap_definition){
			if(($role != null) && !$role->has_cap($cap)){
				$role->add_cap($cap);
			}
		}
		unset($role);

		/*	Read existing role list in order to affect default role	*/
		foreach($eobu__wp_role->roles as $role => $role_definition){
			/*	Get role for capabilities affectation	*/
			if($role != 'administrator'){
				$role = get_role($role);
				/*	Affect capabilities to role	*/
				foreach($caps as $cap => $cap_definition){
					if(($role != null) && !$role->has_cap($cap) && ($cap_definition['set_by_default'] == 'oui')){
						$role->add_cap($cap);
					}
				}
				unset($role);
			}
		}
	}

}