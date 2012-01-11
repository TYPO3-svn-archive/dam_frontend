<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2006-2011 in2code (typo3@in2code.de)
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
 * @author Martin Baum <typo3@in2code.de>
 *
 * Some scripts that use this class:	--
 * Depends on:		---
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 *   63: class tx_damfrontend_catList extends tx_damfrontend_baseSessionData
 *   71:     function tx_damfrontend_catList()
 *   83:     function op_Plus($catID, $treeID)
 *  117:     function op_Minus($catID, $treeID)
 *  147:     function unsetAllCategories()
 *  158:     function op_Equals($catID, $treeID)
 *  178:     function getCatSelection($treeID = 0,$pageID=0)
 *  224:     function clearCatSelection($treeID)
 *
 * TOTAL FUNCTIONS: 7
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */


require_once(t3lib_extMgm::extPath('dam_frontend').'/DAL/class.tx_damfrontend_DAL_categories.php');
require_once(t3lib_extMgm::extPath('dam_frontend').'/DAL/class.tx_damfrontend_baseSessionData.php');
require_once(PATH_tslib.'class.tslib_content.php');

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
			return false;
		}
		if (!intval($treeID)) {
			if (TYPO3_DLOG) t3lib_div::devLog('parameter error in function op_Plus: for the treeID only integer values are allowed. Given value was:' .$treeID, 'dam_frontend',3);
		}
		$catarray = $this->getArrayFromUser();
		if (!is_array($catarray)) $catarray = array();

		if ($treeID>=0) {
			$treeArray = is_array($catarray[$GLOBALS['TSFE']->id][$treeID]) ? $catarray[$GLOBALS['TSFE']->id][$treeID] : array();
			$treeArray[] = $catID;
			$catarray[$GLOBALS['TSFE']->id][$treeID] = array_unique($treeArray) ;
		}
		else {
			// if a file is getting categorized  the tree id -1 is used without the use of the page ID in the array key of the category array
			$catLogic = t3lib_div::makeInstance('tx_damfrontend_DAL_categories');
			if (!$catLogic->checkCategoryUploadAccess($GLOBALS['TSFE']->fe_user->user['uid'],$catID)) {
				return false;
			}
			$treeArray = is_array($catarray[$treeID]) ? $catarray[$treeID] : array();
			$treeArray[] = $catID;
			$catarray[$treeID] = array_unique($treeArray) ;
		}

 		$this->setArrayToUser($catarray);
	}

	function op_PlusRec($catID, $treeID){
		$catLogic = t3lib_div::makeInstance('tx_damfrontend_DAL_categories');
		if ($catID==-1 ) $catID=0;
		$subs = $catLogic->getSubCategories($catID);
		foreach ($subs as $sub) {
			$this->op_Plus($sub['uid'],$treeID);
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
		if ($treeID < 0 OR (!empty($catarray) && $catarray[$GLOBALS['TSFE']->id][$treeID] )) {
			
			if ($treeID>=0) {
				$treeCats = $catarray[$GLOBALS['TSFE']->id][$treeID];
			} 
			else {
				$treeCats = $catarray[$treeID];
			}
			foreach ($treeCats as $key=>$cat) {							
				if ($cat ==$catID) {
					unset($treeCats[$key]);
				}
				
			}
			if ($treeID>=0) { 
				$catarray[$GLOBALS['TSFE']->id][$treeID] = $treeCats;
			}
			else {
				
				$catarray[$treeID] = $treeCats;
			}

		}
		$this->setArrayToUser($catarray);
	}


	/**
	 * Operation for removing all categories from session
	 *
	 * @return	void
	 */
	function unsetAllCategories() {
		$this->setArrayToUser(null);
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
		if ($treeID>=0) {
			$catarray[$GLOBALS['TSFE']->id][$treeID] = array($catID);
		}
		else {
			$catarray[$treeID] = array($catID);
		}
		$this->setArrayToUser($catarray);
	}

	/**
	 * returns a list of either the selected categories of a tree ($treeID must be set) or the categories of a page (pageID must be set)
	 *
	 * @param	int		$treeID: ID of used category tree (optional: if set only the categories of this tree are returned)
	 * @param	int		$pageID: ID of the page where the tree should be used (optional: if all categories which are selected are returned for this page)
	 * @return	array		list of all selected categories
	 */
	function getCatSelection($treeID = 0,$pageID=0) {
		$ar = $this->getArrayFromUser();
		$extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['dam_frontend']);
		if ($extConf['enableDebug']==1) {
			$conf =$GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_damfrontend_pi1.'];
			if ($conf['debug.']['tx_damfrontend_catlist.']['getCatSelection.']['getArrayFromUser']==1)		t3lib_div::debug($ar);
		}
		if ($treeID <> 0) {
			//returns the selected categories for a specified treeID
			if ($treeID==-1){
				return is_array($ar[$treeID]) ? array_unique($ar[$treeID]) : null;
			}
			else {
				$returnArr =  null;
				// the cat selections are stored by pages. The array is like this: [PageID][TreeID][ID=>catID]
				foreach ($ar as $key => $value) {
					// check if the  treeID exists at the current page array
					if (is_array($value[$treeID])) {
						$returnArr[$treeID]= array_unique($value[$treeID]);
					}
				}
				//Debug statements
				$extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['dam_frontend']);
				if ($extConf['enableDebug']==1) {
					$conf =$GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_damfrontend_pi1.'];
					if ($conf['debug.']['tx_damfrontend_catlist.']['getCatSelection']==1)		t3lib_div::debug($returnArr);
				}
				return $returnArr;
			}
		}
		else {
			# return only treeIDs of the current PageID
			if (!is_array($ar)) {
				return null;
			}
			else {
				$returnArr=array();
//				foreach ($ar as $key=>$value) {
//					$FIELDS = 'pid';
//					$TABLE = 'tt_content';
//					$WHERE = 'uid = '.$key ;
//					$WHERE .= tslib_cObj::enableFields('tt_content');
//					$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery($FIELDS,$TABLE,$WHERE);
//					while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
//						// TODO check for language overlay
//						if ($row['pid']==$pageID ) {
//							$returnArr[$key] = array();
//							$returnArr[$key] = array_unique($ar[$key]);
//						}
//					}
//				}

				if ($extConf['enableDebug']==1) {
					$conf =$GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_damfrontend_pi1.'];
					if ($conf['debug.']['tx_damfrontend_catlist.']['getCatSelection']==1)		t3lib_div::debug($returnArr);
				}
				
				if (is_array($ar[$GLOBALS['TSFE']->id])) {
					foreach ($ar[$GLOBALS['TSFE']->id] as $key => $value) {
						// eliminate selections that have no treeID
						if ($key) {
							$return[$key] = $value;
						}
					}
					return $return;
				}
				else {
					return null;
				}
			}
		}
	}

	/**
	 * clears the selected category of a user
	 *
	 * @param	int		$treeID: ID of used category tree
	 * @return	void		nothing
	 */
	function clearCatSelection($treeID) {
		$ar = $this->getArrayFromUser();

		unset($ar[$GLOBALS['TSFE']->id][$treeID]);
		$this->setArrayToUser($ar);
	}
}
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/dam_frontend/DAL/class.tx_damfrontend_catList.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/dam_frontend/DAL/class.tx_damfrontend_catList.php']);
}

?>
