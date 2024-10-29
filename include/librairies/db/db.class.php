<?php
/**
* Plugin database management
* 
* Define method to manage plugin database
* @author Eoxia <dev@eoxia.com>
* @version 0.1
* @package backup_release_ovh
* @subpackage librairies
*/

/**
* Define method to manage plugin database
* @package backup_release_ovh
* @subpackage librairies_database
*/
class eobu_database
{

	/**
	*
	*/
	function update_database_structure(){
		global $wpdb;
		require_once(EO_BU_LIB_PLUGIN_DIR . 'db/db_structure.php');
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

		$current_db_version = eobu_options::get_specific_option('eobu_db_options', 'db_version');
		$current_db_version = ($current_db_version > 0) ? $current_db_version : '0';
		$last_db_update_date = eobu_options::get_specific_option('eobu_db_options', 'last_update_date');
		$last_db_update_time = eobu_options::get_specific_option('eobu_db_options', 'last_update_time');

		ksort($eobackup_table);											$max_table = count($eobackup_table) > 0 ? max(array_keys($eobackup_table)) : 0;
		ksort($eobackup_table_change);							$max_table_change = count($eobackup_table_change) > 0 ? max(array_keys($eobackup_table_change)) : 0;
		ksort($eobackup_table_structure_change);		$max_table_structure_change = count($eobackup_table_structure_change) > 0 ? max(array_keys($eobackup_table_structure_change)) : 0;
		ksort($eobackup_update_way);								$max_eobackup_update_way = count($eobackup_update_way) > 0 ? max(array_keys($eobackup_update_way)) : 0;

		$current_def_max_version = max(array($max_table, $max_table_change, $max_table_structure_change, $max_eobackup_update_way));
		$new_version = $current_def_max_version + 1;
		$version_nb_delta = $current_def_max_version - $current_db_version;

		$do_changes = false;

		/*	Check if there are modification to do	*/
		if($current_def_max_version >= $current_db_version){
			/*		*/
			$lowest_version_to_execute = $current_def_max_version - $version_nb_delta;

			/*	If there is a complete update to do	*/
			if($eobackup_update_way[$current_def_max_version] == 'full'){
				/*	Change table informations. First check if table exists, then make action	*/
				foreach($eobackup_table_change as $version_number => $table){
					foreach($table as $table_name => $table_informations){
						self::table_operation($table_name, $table_informations);
					}
				}

				/*	Check and make update (Just add action) on entire table structure	*/
				foreach($eobackup_table as $version_number => $table){
					foreach($table as $table_name => $table_structure){
						dbDelta($table_structure);
					}
				}

				/*	Change table structure. First check if field exists, then make action	*/
				foreach($eobackup_table_structure_change as $version_number => $table){
					foreach($table as $table_name => $table_structure_informations){
						foreach($table_structure_informations as $action_to_do){
							self::table_field_operation($table_name, $action_to_do);
						}
					}
				}

				$do_changes = true;
			}
			/*	If there is only the current version to update	*/
			else{
				for($i = $lowest_version_to_execute; $i <= $current_def_max_version; $i++){
					/*	Check if there are modification to do	*/
					if(isset($eobackup_update_way[$i])){
						/*	Check if there are modification to make on table structure	*/
						if(isset($eobackup_table_change[$i])){
							foreach($eobackup_table_change[$i] as $table_name => $table_informations){
								self::table_operation($table_name, $table_informations);
							}
							$do_changes = true;
						}

						/*	Check if there are modification to make on table	*/
						if(isset($eobackup_table[$i])){
							foreach($eobackup_table[$i] as $table_name => $table_informations){
								dbDelta($table_informations);
							}
							$do_changes = true;
						}

						/*	Check if there are modification to make on table	*/
						if(isset($eobackup_table_structure_change[$i])){
							foreach($eobackup_table_structure_change[$i] as $table_name => $table_structure_informations){
								foreach($table_structure_informations as $action_to_do){
									self::table_field_operation($table_name, $action_to_do);
								}
							}
							$do_changes = true;
						}
					}
				}
			}

			/*	Insert / udpdate datas and options	*/
			require_once(EO_BU_LIB_PLUGIN_DIR . 'db/db_data.php');
			for($i = $lowest_version_to_execute; $i <= $current_def_max_version; $i++){
				if(isset($eobackup_update_way[$i])){
					/*	Add datas / options for the current version defined in file	*/
					if(is_array($eobackup_datas) && is_array($eobackup_datas[$i]) && (count($eobackup_datas[$i]) > 0)){
						foreach($eobackup_datas[$i] as $query){
							$wpdb->query($query);
							$do_changes = true;
						}
					}
					if(is_array($eobackup_datas_add) && is_array($eobackup_datas_add[$i]) && (count($eobackup_datas_add[$i]) > 0)){
						foreach($eobackup_datas_add[$i] as $table_name => $def){
							foreach($def as $information_index => $table_information){
								$wpdb->insert($table_name, $table_information, '%s');
								$do_changes = true;
							}
						}
					}
					if(is_array($eobackup_options_add) && is_array($eobackup_options_add[$i]) && (count($eobackup_options_add[$i]) > 0)){
						foreach($eobackup_options_add[$i] as $option_name => $options){
							$option_to_add = '';
							foreach($options as $sub_option_name => $sub_option_value){
								$option_to_add[$sub_option_name] = $sub_option_value;
							}
							if(is_array($option_to_add)){
								add_option($option_name, $option_to_add, '', 'yes');
								$do_changes = true;
							}
						}
					}
					if(is_array($eobackup_options_update) && is_array($eobackup_options_update[$i]) && (count($eobackup_options_update[$i]) > 0)){
						foreach($eobackup_options_update[$i] as $option_name => $options){
							$options_to_update = get_option($option_name);
							foreach($options as $sub_option_name => $sub_option_value){
								$options_to_update[$sub_option_name] = $sub_option_value;
							}
							if(is_array($options_to_update)){
								update_option($option_name, $options_to_update);
								$do_changes = true;
							}
						}
					}

					if(is_array($eobackup_data_link) && is_array($eobackup_data_link[$i]) && (count($eobackup_data_link[$i]) > 0)){
						foreach($eobackup_data_link[$i] as $link_table => $link_type_information){
							foreach($link_type_information as $link_index => $link_table_type_information){
								if($link_table == TABLE_PHOTO_LIAISON){
									$pic_id = $menu_id = 0;
									foreach($link_table_type_information as $table => $informations){
										if($table == TABLE_PHOTO){
											$query = $wpdb->prepare("SELECT id FROM " . $table . " WHERE photo = %s", $informations['pic']);
											$pic_id = $wpdb->get_var($query);
										}
										if($table == TABLE_MENU){
											$query = $wpdb->prepare("SELECT id FROM " . $table . " WHERE slug = %s", $informations);
											$menu_id = $wpdb->get_var($query);
										}
									}
									if(($pic_id > 0) && ($menu_id > 0)){
										$add_link = $wpdb->insert($link_table, array('status' => 'valid', 'isMainPicture' => $link_table_type_information[TABLE_PHOTO]['main'], 'idPhoto' => $pic_id, 'idElement' => $menu_id, 'tableElement' => TABLE_MENU));
									}
								}
								elseif($link_table == TABLE_LIAISON_MENU_BOX){
									$box_id = $menu_id = $hook_id = 0;
									foreach($link_table_type_information as $table => $informations){
										if($table == TABLE_BOX){
											$query = $wpdb->prepare("SELECT id FROM " . $table . " WHERE box_identifier = %s", $informations['name']);
											$box_id = $wpdb->get_var($query);
										}
										if($table == TABLE_MENU){
											$query = $wpdb->prepare("SELECT id FROM " . $table . " WHERE slug = %s", $informations);
											$menu_id = $wpdb->get_var($query);
										}
										if($table == TABLE_HOOK){
											$query = $wpdb->prepare("SELECT id FROM " . $table . " WHERE slug = %s", $informations);
											$hook_id = $wpdb->get_var($query);
										}
									}
									if(($box_id > 0) && ($menu_id > 0) && ($hook_id > 0)){
										$add_link = $wpdb->insert($link_table, array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'menu_id' => $menu_id, 'box_id' => $box_id, 'box_position' => $link_table_type_information[TABLE_BOX]['position'], 'table_element' => $link_table_type_information[TABLE_BOX]['table'], 'hook_id' => $hook_id, 'box_title' => (isset($link_table_type_information[TABLE_BOX]['title']) ? $link_table_type_information[TABLE_BOX]['title'] : ''), 'box_callback_args' => (isset($link_table_type_information[TABLE_BOX]['args']) ? serialize($link_table_type_information[TABLE_BOX]['args']) : '')));
									}
								}
							}
						}
					}

				}
			}
		}
		
		/*	Update database version	*/
		// $do_changes = false;
		if($do_changes){
			eobu_options::set_option_value('eobu_db_options', 'last_update_date', date('Y-m-d'));
			eobu_options::set_option_value('eobu_db_options', 'last_db_update_time', date('H:i:s'));
			eobu_options::set_option_value('eobu_db_options', 'db_version', $new_version);
		}
	}

	/**
	*
	*/
	function check_database(){
		global $wpdb;

		$database_state_content = '';
		require_once(EVA_INC_PLUGIN_DIR . 'db/database_structure_definition.php');

		/*	Get the database version into options	*/
		$current_db_version = eobu_options::get_specific_option('db_version');
		$last_db_update_date = eobu_options::get_specific_option('last_update_date');
		$last_db_update_time = eobu_options::get_specific_option('last_db_update_time');

		/*	Get the different version from definition 	*/
		ksort($eobackup_table);											$max_table = max(array_keys($eobackup_table));
		ksort($eobackup_table_change);							$max_table_change = max(array_keys($eobackup_table_change));
		ksort($eobackup_table_structure_change);		$max_table_structure_change = max(array_keys($eobackup_table_structure_change));
		ksort($eobackup_update_way);								$max_eobackup_update_way = max(array_keys($eobackup_update_way));
		$current_def_max_version = max(array($max_table, $max_table_change, $max_table_structure_change, $max_eobackup_update_way));
		$definition_current_version = $current_def_max_version + 1;

		$database_state_content.= '
<div class="tools_db_main_info_container" >
	<div class="tools_db_lastupdate_date" >' . sprintf(__('Derni&egrave;re mise &agrave; jour le %s'), '<span class="bold" >' . mysql2date('d F Y', $last_db_update_date, true)) . '</span></div>
	<div class="tools_db_theoretical_version" >' . sprintf(__('Version th&eacute;orique %s'), '<span class="bold" >' . $definition_current_version) . '</span></div>
	<div class="tools_db_current_version" >' . sprintf(__('Version courante %s'), '<span class="bold" >' . $current_db_version) . '</span></div>
</div>
<div class="tools_db_table_details_container" >';

		/*	Check if all table exist	*/
		$existing_table = $not_existing_table = array();
		$existing_table_nb = $not_existing_table_nb = 0;
		foreach($eobackup_table as $version_number => $table){
			foreach($table as $table_name => $table_structure){
				$query = $wpdb->prepare("SHOW TABLES FROM " . DB_NAME . " LIKE %s", $table_name);
				$table_result = $wpdb->query($query);

				if($table_result == 1){
					$existing_table[$version_number][] = $table_name;
					$existing_table_nb++;
				}
				else{
					$not_existing_table[$version_number][] = $table_name;
					$not_existing_table_nb++;
				}
			}
		}
		/*	Generate output for the table checker	*/
		if($not_existing_table_nb > 0){
			$database_state_content .= '
	<div class="tools_db_not_ok_table_details_container" >
		<div class="tools_db_check_unexisting_table" >
			<img class="tools_db_check_img_table" src="' . admin_url('images/no.png') . '" alt="' . __('Table inexistante' ,'eobackup') . '" title="' . __('Table inexistante' ,'eobackup') . '" />&nbsp;' . __('Tables inexistantes', 'eobackup') . '&nbsp;:&nbsp;' . $not_existing_table_nb . '
		</div>';
			foreach($not_existing_table as $version_number => $version_detail){
				foreach($version_detail as $table_name){
					$database_state_content .= '
		<div class="tools_db_check_version_number" >
			' . __('Version', 'eobackup') . '&nbsp;' . $version_number . '&nbsp;:
		</div>
		<div class="tools_db_check_version_details" >
			' . $table_name . '
			<div class="alignright" id="correct_table_' . $table_name . '_version_' . $version_number . '_loader" >
				<input type="button" name="correct_table_' . $table_name . '_version_' . $version_number . '" value="' . __('Corriger', 'eobackup') . '" id="correct_table_' . $table_name . '_version_' . $version_number . '" class="button-secondary correct_table" />
			</div>
		</div>';
				}
			}
			$database_state_content .= '
	</div>';
		}
		if($existing_table_nb > 0){
			$database_state_content .= '
	<div class="tools_db_ok_table_details_container" >
		<div class="tools_db_check_existing_table" ><img class="tools_db_check_img_table" src="' . admin_url('images/yes.png') . '" alt="' . __('Table existante' ,'eobackup') . '" title="' . __('Table existante' ,'eobackup') . '" />&nbsp;' . __('Tables existantes', 'eobackup') . '&nbsp;:&nbsp;' . $existing_table_nb . '</div>';
			foreach($existing_table as $version_number => $version_detail){
				$database_state_content .= '<div class="tools_db_check_version_number" >' . __('Version', 'eobackup') . '&nbsp;' . $version_number . '&nbsp;:&nbsp;(' . count($version_detail) . ')</div><div class="tools_db_check_version_details" >' . implode(', ', $version_detail) . '</div>';
			}
			$database_state_content .= '
	</div>';
		}
	

		/*	Check changes on table	*/
		$moved_table = $unmoved_table = $duplicate_table = $un_deleted_table = $deleted_table = array();
		$moved_table_nb = $unmoved_table_nb = $duplicate_table_nb = $un_deleted_table_nb = $deleted_table_nb = 0;
		foreach($eobackup_table_change as $version_number => $table){
			$i = 1;
			foreach($table as $table_name => $operations){
				switch($operations['ACTION']){
					case 'RENAME':
						$query = $wpdb->prepare("SHOW TABLES FROM " . DB_NAME . " LIKE %s", $table_name);
						$table_result = $wpdb->query($query);

						$query = $wpdb->prepare("SHOW TABLES FROM " . DB_NAME . " LIKE %s", $operations['NEWNAME']);
						$new_table_result = $wpdb->query($query);

						if(($table_result == 0) && ($new_table_result == 1)){
							$moved_table[$version_number][$i]['oldname'] = $table_name;
							$moved_table[$version_number][$i]['newname'] = $operations['NEWNAME'];
							$moved_table_nb++;
						}
						elseif(($table_result == 1) && ($new_table_result == 1)){
							$duplicate_table[$version_number][$i]['oldname'] = $table_name;
							$duplicate_table[$version_number][$i]['newname'] = $operations['NEWNAME'];
							$duplicate_table_nb++;
						}
						elseif(($table_result == 0) && ($new_table_result == 0)){
							$unmoved_table[$version_number][$i]['oldname'] = $table_name;
							$unmoved_table[$version_number][$i]['newname'] = $operations['NEWNAME'];
							$unmoved_table_nb++;
						}
					break;
					case 'DROP':
						$query = $wpdb->prepare("SHOW TABLES FROM " . DB_NAME . " LIKE %s", $table_name);
						$table_result = $wpdb->query($query);

						if($table_result == 1){
							$un_deleted_table[$version_number][] = $table_name;
							$un_deleted_table_nb++;
						}
						else{
							$deleted_table[$version_number][] = $table_name;
							$deleted_table_nb++;
						}
					break;
				}
				$i++;
			}
		}
		/*	Generate output for the table checker	*/
		if($moved_table_nb > 0){
			$database_state_content .= '
	<div class="tools_db_not_ok_table_details_container" >
		<div class="tools_db_check_unexisting_table" >
			<img class="tools_db_check_img_table" src="' . admin_url('images/yes.png') . '" alt="' . __('Table renom&eacute;e' ,'eobackup') . '" title="' . __('Table renom&eacute;e' ,'eobackup') . '" />&nbsp;' . __('Tables renom&eacute;e', 'eobackup') . '&nbsp;:&nbsp;' . $moved_table_nb . '
		</div>';
			foreach($moved_table as $version_number => $version_detail){
				$database_state_content .= '
		<div class="tools_db_check_version_number" >
			' . __('Version', 'eobackup') . '&nbsp;' . $version_number . '&nbsp;:
		</div>';
				foreach($version_detail as $table_name){
					$database_state_content .= '
		<div class="tools_db_check_version_details" >
			' . $table_name['oldname'] . '&nbsp;->&nbsp;' . $table_name['newname'] . '
		</div>';
				}
			}
			$database_state_content .= '
	</div>';
		}
		if($unmoved_table_nb > 0){
			$database_state_content .= '
	<div class="tools_db_not_ok_table_details_container" >
		<div class="tools_db_check_unexisting_table" >
			<img class="tools_db_check_img_table" src="' . admin_url('images/yes.png') . '" alt="' . __('Table renom&eacute;e puis supprim&eacute;es' ,'eobackup') . '" title="' . __('Table renom&eacute;e puis supprim&eacute;es' ,'eobackup') . '" />&nbsp;' . __('Tables renom&eacute;e puis supprim&eacute;es', 'eobackup') . '&nbsp;:&nbsp;' . $unmoved_table_nb . '
		</div>';
			foreach($unmoved_table as $version_number => $version_detail){
				$database_state_content .= '
		<div class="tools_db_check_version_number" >
			' . __('Version', 'eobackup') . '&nbsp;' . $version_number . '&nbsp;:
		</div>';
				foreach($version_detail as $table_name){
					$database_state_content .= '
		<div class="tools_db_check_version_details" >
			' . $table_name['oldname'] . '&nbsp;->&nbsp;' . $table_name['newname'] . '
		</div>';
				}
			}
			$database_state_content .= '
	</div>';
		}
		if($duplicate_table_nb > 0){
			$database_state_content .= '
	<div class="tools_db_not_ok_table_details_container" >
		<div class="tools_db_check_unexisting_table" >
			<img class="tools_db_check_img_table" src="' . admin_url('images/yes.png') . '" alt="' . __('Table renom&eacute;e puis supprim&eacute;es' ,'eobackup') . '" title="' . __('Table renom&eacute;e puis supprim&eacute;es' ,'eobackup') . '" />&nbsp;' . __('Tables renom&eacute;e puis supprim&eacute;es', 'eobackup') . '&nbsp;:&nbsp;' . $unmoved_table_nb . '
		</div>';
			foreach($unmoved_table as $version_number => $version_detail){
				$database_state_content .= '
		<div class="tools_db_check_version_number" >
			' . __('Version', 'eobackup') . '&nbsp;' . $version_number . '&nbsp;:
		</div>';
				foreach($version_detail as $table_name){
					$database_state_content .= '
		<div class="tools_db_check_version_details" >
			' . $table_name['oldname'] . '&nbsp;->&nbsp;' . $table_name['newname'] . '
		</div>';
				}
			}
			$database_state_content .= '
	</div>';
		}
		if($un_deleted_table_nb > 0){
			
		}
		if($deleted_table_nb > 0){
			
		}

	$database_state_content .= '
</div>
<script type="text/javascript" >
	eobackup(document).ready(function(){
		jQuery(".correct_table").click(function(){
			var id_to_treat = jQuery(this).attr("id").replace("correct_table_", "");
			jQuery("#correct_table_" + id_to_treat + "_loader").html(jQuery("#round_loading_img .round_loading_img").html());
			jQuery("#correct_table_" + id_to_treat + "_loader").load(EVA_AJAX_FILE_URL,{
				"post": "true",
				"nom": "tools",
				"action": "repair_table",
				"element" : id_to_treat
			});
		});
	});
</script>';

		return $database_state_content;
	}

	/**
	*
	*/
	function table_operation($table_name, $table_informations){
		global $wpdb;

		$query = $wpdb->prepare("SHOW TABLES FROM " . DB_NAME . " LIKE %s", $table_name);
		$table_result = $wpdb->query($query);
		if($table_result == 1){
			if($table_informations['ACTION'] == 'RENAME'){
				$wpdb->query("RENAME TABLE " . $table_name . " TO " . $table_informations['NEWNAME']);
			}
			elseif($table_informations['ACTION'] == 'DROP'){
				$wpdb->query("DROP TABLE " . $table_name);
			}
		}
	}
	/**
	*
	*/
	function table_field_operation($table_name, $action_to_do){
		global $wpdb;

		$query = $wpdb->prepare("SHOW COLUMNS FROM " . $table_name . " FROM " . DB_NAME . " LIKE %s", $action_to_do['FIELD']);
		$table_result = $wpdb->query($query);
		if($table_result == 1){
			$wpdb->query("ALTER TABLE " . $table_name . " " . $action_to_do['ACTION'] . " " . $action_to_do['FIELD']);
		}
	}

}
