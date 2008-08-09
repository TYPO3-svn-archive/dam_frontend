<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2006-2007 BUS Netzwerk (typo3@in2form.com)
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*  A copy is found in the textfile GPL.txt and important notices to the license
*  from the author is found in LICENSE.txt distributed with these scripts.
*
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/**
 *
 * class.tx_damfrontend_catList.php
 *
 * This class is dealing the category list. If a FE_USER (deI selects a category this class is used.
 *
 * @package typo3
 * @subpackage tx_dam_frontend
 * @author Martin Baum <typo3@in2form.com>
 *
 * Some scripts that use this class:	--
 * Depends on:		---
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 *   61: class tx_damfrontend_catList extends tx_damfrontend_baseSessionData
 *   68:     function tx_damfrontend_catList()
 *   80:     function op_Plus($catID, $treeID)
 *  103:     function op_Minus($catID, $treeID)
 *  123:     function op_Equals($catID, $treeID)
 *  140:     function getCatSelection()
 *
 * TOTAL FUNCTIONS: 5
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */


require_once(t3lib_extMgm::extPath('dam_frontend').'/DAL/class.tx_damfrontend_DAL_categories.php');
require_once(t3lib_extMgm::extPath('dam_frontend').'/DAL/class.tx_damfrontend_baseSessionData.php');

class tx_damfrontend_catList extends tx_damfrontend_baseSessionData {

	 /**
 * initialization of the the session
 *
 * @return	void
 */
	function tx_damfrontend_catList() {
		parent::tx_damfrontend_baseSessionData();
		$this->sessionVar = 'tx_damfrontend_catList';
	}

	/**
	 * Operation for adding a category to the current selection
	 *
	 * @param	int		$catID: id of the category to add to the selection
	 * @param	int		$treeID: id of the tree to add to the selection
	 * @return	void
	 */
	function op_Plus($catID, $treeID) {
		if (!intval($catID)) {
			if (TYPO3_DLOG) t3lib_div::devLog('parameter error in function op_Plus: for the catID only integer values are allowed. Given value was:' .$catID, 'dam_frontend',3);
		}
		if (!intval($treeID)) {
			if (TYPO3_DLOG) t3lib_div::devLog('parameter error in function op_Plus: for the treeID only integer values are allowed. Given value was:' .$treeID, 'dam_frontend',3);
		}
		$catarray = $this->getArrayFromUser();
		if (!is_array($catarray)) $catarray = array();
		if(!is_array($catarray[$treeID])) $catarray[$treeID] = array();
		if (!array_search($catID, $catarray[$treeID])) {
			$catarray[$treeID][] = $catID;
			$this->setArrayToUser($catarray);
		}
	}

	/**
	 * Operation for removing a given ID from the current category selection
	 *
	 * @param	int		$catID: category ID to remove from the current category selection
	 * @param	int		$treeID: tree id of the used category mount point
	 * @return	void
	 */
	function op_Minus($catID, $treeID) {
		if (!intval($catID)) {
			if (TYPO3_DLOG) t3lib_div::devLog('parameter error in function op_Minus: for the catID only integer values are allowed. Given value was:' .$catID, 'dam_frontend',3);
		}
		if (!intval($treeID)){
			if (TYPO3_DLOG) t3lib_div::devLog('parameter error in function op_Minus: for the treeID only integer values are allowed. Given value was:' .$treeID, 'dam_frontend',3);
		}
		$catarray = $this->getArrayFromUser();
		$test = array_search($catID,$catarray[$treeID]);
		unset($catarray[$treeID][$test]);
		$this->setArrayToUser($catarray);
	}

	/**
	 * Deletes the current selection an makes the given catID the only selected category
	 *
	 * @param	int		$catID: catID to be the current selection
	 * @param	int		$treeID: ID of the used cat Tree Mount Point
	 * @return	void
	 */
	function op_Equals($catID, $treeID) {
		if (!intval($catID)){
			if (TYPO3_DLOG) t3lib_div::devLog('parameter error in function op_Equals: for the catID only integer values are allowed. Given value was:' .$catID, 'dam_frontend',3);
		}
		if (!intval($treeID)) {
			if (TYPO3_DLOG) t3lib_div::devLog('parameter error in function op_Equals: for the treeID only integer values are allowed. Given value was:' .$treeID, 'dam_frontend',3);
		}
		$catarray = $this->getArrayFromUser();
		$catarray[$treeID] = array($catID);
		$this->setArrayToUser($catarray);
	}

	/**
	 * returns a list of all selected categories
	 *
	 * @return	array		list of all selected categories
	 */
	function getCatSelection() {
			$ar = $this->getArrayFromUser();
			return $ar;
	}
}
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/dam_frontend/DAL/class.tx_damfrontend_catList.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/dam_frontend/DAL/class.tx_damfrontend_catList.php']);
}

?>
