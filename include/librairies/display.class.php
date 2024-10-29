<?php
/**
*	Display
* 
* Define the different method for plugin display
* @author Eoxia <dev@eoxia.com>
* @version 0.1
* @package backup_release_ovh
* @subpackage librairies
*/


/**
* Define the different method for plugin display
* @author Eoxia <dev@eoxia.com>
* @version 0.1
* @package backup_release_ovh
* @subpackage librairies
*/
class eobu_display
{

	/**
	*	Define content for dashboard page
	*/
	function backup_history(){
		echo  
eobu_display::start_page(__('Historique de sauvegarde(s) effectu&eacute;e(s)', 'eobackup'), '', '', '') . '
<div id="domain_details_dialog" class="eobackup_hide" >&nbsp;</div>
<div id="history_div" >' . eobu_backup::get_backup_history(date('Y-m-d'), '<', false) . '</div>
' . eobu_display::end_page();
	}

	/**
	*	Define content for dashboard page
	*/
	function dashboard(){
		$page_content = '';

		/*	Output the domain list to backup today	*/
		$page_content .= '<h2 class="eobackup_summary_title" >' . __('Liste des domaines &agrave; sauvegarder aujourd\'hui', 'eobackup') . '</h2><br/>' . eobu_domain::backup_domain_list();

		/*	Output done backup made today	*/
		$page_content .= '<h2 class="eobackup_dashboard_title" >' . __('Sauvegarde(s) effectu&eacute;e(s) aujourd\'hui', 'eobackup') . '</h2><div id="history_div" >' . eobu_backup::get_backup_history(date('Y-m-d'), '=', false) . '</div>';

		echo  
eobu_display::start_page(__('R&eacute;sum&eacute; des sauvegardes', 'eobackup'), '', '', '') . '
<div id="domain_details_dialog" class="eobackup_hide" >&nbsp;</div>
' . $page_content . '
' . eobu_display::end_page();
	}


	/**
	*	Define content for dashboard page
	*/
	function missing_var(){
		echo '<p>' . sprintf(__('Sauvegarde serveur : Certaines configurations sont manquantes. Vous ne pourrez pas utiliser le plugin tant que vous n\'aurez pas %s', 'eobackup'), '<a href="' . admin_url("options-general.php?page=" . EOBU_SLUG_OPTION) . '" >' . __('configur&eacute; ces variables', 'eobackup') . '</a>') . '</p>';
	}

	
	/**
	* Returns the header display of a classical HTML page.
	*
	* @see end_page
	*
	* @param string $titrePage Title of the page.
	* @param string $icone Path of the icon.
	* @param string $titreIcone Title attribute of the icon.
	* @param string $altIcon Alt attribute of the icon.
	*
	* @return string HTML code of the header display.
	*/
	function start_page($titrePage, $icone, $titreIcone, $altIcon){
		$debutPage = '';

		ob_start();
?>
<div class="wrap">
	<div id="loading_picture" class="eobackup_hide" ><img src="<?php echo EO_BU_MEDIA_PLUGIN_URL; ?>loading.gif" alt="<?php _e('Chargement en cours...', 'eobackup'); ?>" title="<?php _e('Chargement en cours...', 'eobackup'); ?>" /></div>
<?php
		if($icone != ''){
?>
	<div class="icon32"><img alt="<?php echo $altIcon; ?>" src="<?php echo $icone; ?>" title="<?php echo $titreIcone; ?>" /></div>
<?php
		}
?>
	<h2 class="page_title" ><?php echo $titrePage; ?></h2>
<?php
		$debutPage = ob_get_contents();
		ob_end_clean();

		return $debutPage;
	}

	/**
	* Closes the "div" tag open in the header display  of a classical HTML page.
	*
	* @see start_page
	* @return  the closure.
	*/
	function end_page(){
		$end_page = '';

		ob_start();
?>
	<div class="clear eobackup_hide" id="ajax-response" >&nbsp;</div>
</div>
<?php
		$end_page = ob_get_contents();;
		ob_end_clean();

		return $end_page;
	}



	/*
	* Return a complete html table with header, body and content
	*
	*	@param string $tableId The unique identifier of the table in the document
	*	@param array $tableTitles An array with the different element to put into the table's header and footer
	*	@param array $tableRows An array with the different value to put into the table's body
	*	@param array $tableClasses An array with the different class to affect to table rows and cols
	*	@param array $tableRowsId An array with the different identifier for table lines
	*	@param string $tableSummary A summary for the table
	*	@param boolean $withFooter Allow to define if the table must be create with a footer or not
	*
	*	@return string $table The html code of the table to output
	*/
	function output_table($tableId, $tableTitles, $tableRows, $tableClasses, $tableRowsId, $tableSummary, $withFooter = true, $tableClass = '', $tableRowClass = ''){
		$tableTitleBar = $tableBody = '';

		/*	Create the header and footer row	*/
		for($i=0; $i<count($tableTitles); $i++){
			$tableTitleBar .= '
				<th class="' . $tableClasses[$i] . '" scope="col" >' . $tableTitles[$i] . '</th>';
		}

		/*	Create each table row	*/
		for($lineNumber=0; $lineNumber<count($tableRows); $lineNumber++){
			$tableRow = $tableRows[$lineNumber];
			$tableRowClassLine = (is_array($tableRowClass) && isset($tableRowClass[$lineNumber]['class'])) ? $tableRowClass[$lineNumber]['class'] : '';
			$tableBody .= '
		<tr id="' . $tableRowsId[$lineNumber] . '" class="tableRow ' . $tableRowClassLine . '" >';
			for($i=0; $i<count($tableRow); $i++){
				$rowCellOption = (isset($tableRow[$i]['option'])) ? $tableRow[$i]['option'] : '';
				$tableBody .= '
			<td class="' . $tableClasses[$i] . ' ' . $tableRow[$i]['class'] . '" ' . $rowCellOption . ' >' . $tableRow[$i]['value'] . '</td>';
			}
			$tableBody .= '
		</tr>';
		}

		/*	Create the table output	*/
		$table = '
<table id="' . $tableId . '" cellspacing="0" cellpadding="0" class="widefat post fixed ' . $tableClass . '" summary="' . $tableSummary . '" >';
		if($tableTitleBar != ''){
			$table .= '
	<thead>
			<tr class="tableTitleHeader" >' . $tableTitleBar . '
			</tr>
	</thead>';
			if($withFooter){
				$table .= '
	<tfoot>
			<tr class="tableTitleFooter" >' . $tableTitleBar . '
			</tr>
	</tfoot>';
			}
		}
		$table .= '
	<tbody>' . $tableBody . '
	</tbody>
</table>';

		return $table;
	}

	

	/**
	*	Create an input type text or hidden or password
	*
	*	@param string $name The name of the field given by the database
	*	@param mixed $value The default value for the field Default is empty
	*	@param string $type The input type Could be: text or hidden or passowrd
	*	@param string $option Allows to define options for the input Could be readonly or disabled or style
	*
	*	@return mixed The output code to add to the form
	*/
	function form_input($name, $id, $value = '', $type = 'text', $option = '')
	{
		$allowedType = array('text', 'hidden', 'password', 'file');
		if(in_array($type, $allowedType))
		{
			return '<input type="' . $type . '" name="' . $name . '" id="' . $id . '" value="' . $value . '" ' . $option . ' />' ;
		}
		else
		{
			return __('Input type not allowed here in ' . __FILE__ . 'at line ' . __LINE__, 'wpspending');
		}
	}

	/**
	*	Create an textarea
	*
	*	@param string $name The name of the field given by the database
	*	@param mixed $value The default value for the field Default is empty
	*	@param string $option Allows to define options for the input Could be maxlength or style
	*
	*	@return mixed The output code to add to the form
	*/
	function form_input_textarea($name, $id, $value = '', $option = '')
	{
		return '<textarea name="' . $name.' " id="' . $id . '" ' . $option . ' rows="4" cols="10" >' . $value . '</textarea>';
	}

	/**
	*	Create a combo box input regarding to the type of content given in parameters could be an array or a wordpress database object
	*
	*	@param string $name The name of the field given by the database
	*	@param mixed $content The list of element to put inot the combo box Could be an array or a wordpress database object with id and nom as field
	*	@param mixed $value The selected value for the field Default is empty
	*	@param string $option Allows to define options for the input Could be onchange
	*
	*	@return mixed $output The output code to add to the form
	*/
	function form_input_select($name, $id, $content, $value = '', $option = '', $optionValue = '')
	{
		global $comboxOptionToHide;

		$output = '';
		if(is_array($content) && (count($content) > 0))
		{
			$output = '<select id="' . $id . '" name="' . $name . '" ' . $option . ' >';

			foreach($content as $index => $datas)
			{
				if(is_object($datas) && (!is_array($comboxOptionToHide) || !in_array($datas->id, $comboxOptionToHide)))
				{
					$datas->name = stripslashes($datas->name);
					$selected = ($value == $datas->id) ? ' selected="selected" ' : '';
					$dataText = __($datas->name ,'wpspending');
					if(isset($datas->code))
					{
						$dataText = __($datas->code ,'wpspending');
					}
					$output .= '<option value="' . $datas->id . '" ' . $selected . ' >' . $dataText . '</option>';
				}
				elseif(!is_array($comboxOptionToHide) || !in_array($datas, $comboxOptionToHide))
				{
					$datas = stripslashes($datas);
					$valueToPut = $datas;
					$selected = ($value == $datas) ? ' selected="selected" ' : '';
					if($optionValue == 'index')
					{
						$valueToPut = $index;
						$selected = ($value == $index) ? ' selected="selected" ' : '';
					}
					$output .= '<option value="' . $valueToPut . '" ' . $selected . ' >' . __($datas ,'wpspending') . '</option>';
				}
			}

			$output .= '</select>';
		}

		return $output;
	}

	/**
	*	Create a checkbox input
	*
	*	@param string $name The name of the field given by the database
	*	@param string $id The identifier of the field
	*	@param string $type The input type Could be checkbox or radio
	*	@param mixed $content The list of element to put inot the combo box Could be an array or a wordpress database object with id and nom as field
	*	@param mixed $value The selected value for the field Default is empty
	*	@param string $option Allows to define options for the input Could be onchange
	*
	*	@return mixed $output The output code to add to the form
	*/
	function form_input_check($name, $id, $content, $value = '', $type = 'checkbox', $option = '')
	{
		$allowedType = array('checkbox', 'radio');
		if(in_array($type, $allowedType))
		{
			if(is_array($content) && (count($content) > 0))
			{
				foreach($content as $index => $datas)
				{
					if(is_object($datas))
					{
						$id = $name . '_' . $datas->nom;
						$checked = ($value == $datas->id) ? ' checked="checked" ' : '';
					}
					else
					{
						$id = $name . '_' . $datas;
						$checked = ($value == $datas) ? ' checked="checked" ' : '';
						$output .= '<div><input type="' . $type . '" name="' . $name . '" id="' . $id . '" value="' . $datas . '" ' . $checked . ' ' . $option . ' /><label for="' . $id . '" >' . __($name . '_' . $datas, 'eobackup') . '</label></div>' ;
					}
				}
			}
			else
			{
				$checked = (($value != '') && ($value == $content)) ? ' checked="checked" ' : '';
				$output .= '<input type="' . $type . '" name="' . $name . '" id="' . $id . '" value="' . $content . '" ' . $checked . ' ' . $option . ' />' ;
			}

			return $output;
		}
		else
		{
			return __('Input type not allowed here in ' . __FILE__ . 'at line ' . __LINE__, 'wpspending');
		}
	}

}