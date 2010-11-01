<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2010 Stefan Busemann <info@in2code.de>
*  All rights reserved
*
*  This script is part of the Typo3 project. The Typo3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

/**
 * This file adds an 'UPDATE' entry in the Extension Manager for the dam_frontend
 * extension. The button is visible if the access() method below returns true.
 * 
 * ext_update class used to schedule events to gabriel ext
 *
 * @author	Stefan Busemann <info@in2code.de>
 * @version	0.1 - 1. Nov., 2010
 */

class ext_update  {

 	/**
	 * Called by TYPO3 when a user clicks the 'UPDATE' entry in the drop-down box
	 * for this extension in the Extension Manager. Moves the access Information category read access to the access field	
	 *
	 * @return	string	HTML message
	 * @access	public
	 *
	 * @author	Kasper Ligaard
	 * @version 0.1 - 1. Oct., 2009
	 */	
    public function main()	{
    if(t3lib_div::_GP('update')) {
			
    	$from_table = "tx_dam_cat_readaccess_mm";
    	$select_fields = 'uid_local';
    	$groupBy ='uid_local';
    	$tce = t3lib_div::makeInstance('t3lib_TCEmain'); 
    	$tce->stripslashes_values = 0;
    	$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select_fields, $from_table, '', $groupBy);
    	while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc ($res)) {
    		$where = 'uid_local = ' . $row['uid_local'];
    		$select_fields = 'uid_foreign';
    		$resResult = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select_fields, $from_table, $where);
    		$result = array();
    		while ($resultRow = $GLOBALS['TYPO3_DB']->sql_fetch_assoc ($resResult)) {
    			$result[]=$resultRow['uid_foreign'];
    		}

    		$data['tx_dam_cat'][$row['uid_local']] = array(
    			'fe_group' => implode(',',$result)
    		);
    		$tce->start($data, array());
    		$tce->process_datamap();
    		$tce->updateRefIndex('tx_dam_cat',$row['uid_local']);
    		$i++;
    	}
    		
	    	$content = 'Update successful!. We updated '. $i . ' rows.';
		} else {
			$content = '<form name="static_info_tables_form" action="'.htmlspecialchars(t3lib_div::linkThisScript()).'" method="post">';
			$linkScript = t3lib_div::slashJS(t3lib_div::linkThisScript());
	    	$content .= '<h2>Updatescript for dam_frontend version 0.7</h2>';
	    	$content .= '<p>Use this skript if you have used dam_frontend version 0.65 or earlier. In these versions the category read access was stored in a user defined column (see picture). Now we are using the built in access field for the readaccess.</p>';
	    	$content .= '<img scr="../../../../typo3conf/ext/dam_frontend/res/Update_ReadAccess.jpg">';
	    	$content .= '<p>Background: The read access is used to control the read access for dam_records. Example if you have a file example.jpg and that file is assigned to a category "A", then the user has to have read access to that category "A". Otherwise the picture won\'t be visible.</p>';
	    	$content .= '<p>Click update, to perform the database update. Important note: All informations of the access field are get overwritten!</p>';
			$content .= '<br /><br />';
			$content .= '<input type="submit" name="update" value="Update"/>';
			$content .= '</form>';
		}
    	
    	
    	
    	return $content;
    	
    	} //end of main()

 	/**
	 * Checks if any gabriel entries do already exists and returns false then to prevent double inserts
	 *
	 * @return	boolean		false if any record exists in 'tx_gabriel' table
	 * @access	public
	 *
	 * @author	Stefan Busemann
	 * @version 0.1 - 1. Nov., 2010
	 */	
    public function access()	{
	
		return true;
    } //end of access()

}

?>