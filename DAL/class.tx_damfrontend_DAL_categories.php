<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2006-2011 in2code.de (typo3@in2code.de)
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
 * class.tx_damfrontend_DAL_categories.php
 *
 * This class is doing all database actions for categories
 * FUNCTIONS WHITH DIRECT DATABASE ACCESS
 * getCategory
 * getSubCategories
 * getCategories
 *
 * @package typo3
 * @subpackage tx_dam_frontend
 * @author Martin Baum <typo3@in2code.de>
 *
 * Some scripts that use this class:	--
 * Depends on:		---
 */

require_once(PATH_txdam . 'components/class.tx_dam_selectionCategory.php');
require_once(PATH_tslib . 'class.tslib_content.php');

/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 *   67: class tx_damfrontend_DAL_categories
 *   91:	 function getCategory($catID)
 *  113:	 function getCategory_Rootline($catID)
 *  137:	 function getAllCategories()
 *  150:	 function getSubCategories($catID, $limit=999)
 *  193:	 function getParentCategories($catID)
 *  222:	 function getCategoryMountpoints($userID, $relID)
 *  241:	 function getCategories($groupArr, $relID)
 *  267:	 function isParentCategory($catID, $parentID)
 *  279:	 function isSubCategory($catID, $subID)
 *  294:	 function findUidinList($list, $id)
 *  316:	 function checkCategoryUploadAccess($userID, $catID)
 *  360:	 function checkCategoryAccess($userID, $catID, $relID)
 *
 * TOTAL FUNCTIONS: 12
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */
class tx_damfrontend_DAL_categories {

	var $catTable = 'tx_dam_cat';
	var $docTable = 'tx_dam';
	var $mm_Table = 'tx_dam_mm_cat';
	//TODO add support for start stop field
	var $filter = '';
	// array with all availible access relations
	var $relations = array(
		'1' => 'readaccess',
		'2' => 'downloadaccess',
		'3' => 'uploadaccess'
	);
	var $mm_table_readaccess = ''; // mm Table which stores the groups, which have readaccess to a category
	var $mm_table_downloadaccess = ''; // mm Table which stores the groups
	var $debug = false;

	/**
	 * returns th detail datea of a caegory
	 *
	 * @param	int		$catID id of the category which should be returned
	 * @return	[type]		...
	 * @author martin
	 */
	function getCategory($catID) {
		if (!intval($catID)) {
			if (TYPO3_DLOG) t3lib_div::devLog('parameter error in function getCategory: for the catID only integer values are allowed. Given value was:' . $catID, 'dam_frontend', 3);
			return false;
		}
		else {
			// retrieve data
			$SELECT = '*';
			$FROM = $this->catTable;
			$WHERE = 'uid = ' . $catID . $this->filter;
			$WHERE .= tslib_cObj::enableFields('tx_dam_cat');
			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery($SELECT, $FROM, $WHERE);
			$record = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
			return $record;
		}
	}

	/**
	 * generates a uniqe list of all categories from the
	 * specified category up to the root of the category tree
	 *
	 * @param	int		$catID: ID of the category to get the
	 * @return	array		uniqe list of all categories assosiated with the given ID
	 */
	function getCategory_Rootline($catID) {
		// create a list of all parent categories
		$completecats = array();
		$parents = $this->getParentCategories($catID);
		for ($z = 0; $z < count($parents); $z++)
		{
			$parentcat = $parents[$z];
			// if the parent category isn't already in the list add it
			if (!$this->findUidinList($completecats, $parentcat['uid'])) {
				$completecats[] = $parentcat;
			}
		}
		return $completecats;
	}


	/**
	 * get all subcategories of the specified category
	 *
	 * @param	int		$catID: name of the category to fiond all subcategories
	 * @param	int		$limit: how deep shall the call run
	 * @return	array		list of all subcategory of an extension
	 */
	function getSubCategories($catID, $limit = 999) {
		if (!intval($catID) && !$catID == 0) {
			if (TYPO3_DLOG) t3lib_div::devLog('parameter error in function getSubCategories: for the catID only integer values are allowed. Given value was:' . $catID, 'dam_frontend', 3);
		}
		else {
			//contains ids of subcategories
			$subIDs = array();

			// contains records of the categories
			$recArray = array();
			$recArray[] = $this->getCategory($catID);

			// retrieving subrecords
			$SELECT = 'uid';
			$FROM = $this->catTable;
			$WHERE = 'parent_id = ' . $catID . $this->filter . ' AND sys_language_uid = 0';
			$WHERE .= tslib_cObj::enableFields('tx_dam_cat');
			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery($SELECT, $FROM, $WHERE);

			// adding new category records to the table
			$z = 0;
			while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
				$subIDs[] = $row;
				$z++;
			}
			// subcategories found -> get them - search them
			if ($z > 0 && $limit > 0) {
				foreach ($subIDs as $row) {
					$subrows = $this->getSubCategories($row['uid']);
					foreach ($subrows as $subrow) {
						$recArray[] = $subrow;
					}
				}
			}
			return $recArray;
		}
	}

	/**
	 * gets all parent categories of an existing category
	 *
	 * @param	int		$catID category to check
	 * @return	array		$recArray with all parent categories
	 */
	function getParentCategories($catID) {
		if (!intval($catID)) {
			if (TYPO3_DLOG) t3lib_div::devLog('parameter error in function getParentCategories: for the catID only integer values are allowed. Given value was:' . $catID, 'dam_frontend', 3);
		}
		else {
			if ($catID == null || $catID == 0) {
				if (TYPO3_DLOG) t3lib_div::devLog('parameter error in function getParentCategories: catID must be greater than 0 and not null! Given value was:' . $catID, 'dam_frontend', 2);
			}
			$recArray = array();
			$record = $this->getCategory($catID);
			$parentID = intval($record['parent_id']);
			$recArray[] = $record;
			if ($parentID != 0) {
				$pRows = $this->getParentCategories($parentID);
				foreach ($pRows as $pRow) $recArray[] = $pRow;
			}
			return $recArray;
		}
	}


	/**
	 * creates a list, of all category - subIDs of an article
	 *
	 * @param	int		$userID: uid of the current frontend user
	 * @param	int		$relID: id of the name of the relation in $this->realtions
	 * @return	array		a uniqe list of all mountpoints. Can be used to create a array of trees
	 */
	function getCategoryMountpoints($userID, $relID) {
		// iterate throgh all relations and collect all categories
		$catlist_complete = null;
		foreach ($this->relations as $key => $relname) {
			$catlist_rel[] = $this->getCategories($userID, $relID);
			foreach ($catlist_rel as $cat) {
				if (!is_array($this->findUidinList($catlist_complete, $cat['uid']))) $catlist_complete[] = $cat;
			}
		}
		return $catlist_complete;
	}

	/**
	 * returns all categories, the current user has access to
	 *
	 * @param	int		$groupArr: id of the user, who has the right to use the system
	 * @param	int		$relID: id to specify read / download / upload access
	 * @return	array		array of all category records
	 */
	function getCategories($userID, $relID) {
		if (!isset($userID) || !isset($relID) || $userID == '' || $relID == '') {
			if (TYPO3_DLOG) t3lib_div::devLog('parameter error in function getCategories: userID and relID must be set and empty strings are not allowed! Given value were$userID:' . $userID . ' and relID: ' . $relID, 'dam_frontend', 2);
		}
		$mm_table = 'tx_dam_cat_' . $this->relations[$relID] . '_mm';
		// executing database search
		$local_table = $this->catTable;
		$foreign_table = 'fe_groups';
		$where = 'AND ' . $local_table . '.uid = ' . (int)$userID;
		$select = $local_table . '.*';
		$res = $GLOBALS['TYPO3_DB']->exec_SELECT_mm_query($select, $local_table, $mm_table, $foreign_table, $where);

		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			$resultlist[] = $row;
		}
		return $resultlist;
	}


	/**
	 * checks if the $subID Category is really a subcategory of $catID
	 *
	 * @param	int		$catID: suggested child Category
	 * @param	int		$parentID: suggested Parent Category
	 * @return	boolean		returns true, if parentID is parentID of catID
	 */
	function isParentCategory($catID, $parentID) {
		$parents = $this->getParentCategories($catID);
		return is_array($this->findUidinList($parents, $parentID));
	}

	/**
	 * checks if the $subID Category is really a subcategoy of $catID
	 *
	 * @param	int		$catID: suggested Parent Category
	 * @param	int		$subID: suggested child Category
	 * @return	boolean		returns true, if subID is subcategory of catID
	 */
	function isSubCategory($catID, $subID) {
		$children = $this->getSubCategories($catID);
		return is_array($this->findUidinList($children, $subID));
	}


	/**
	 * searches for an uid in an given array and returns the found row. If multiple
	 * records with the same uid exists in the list
	 *
	 * @param	array		$list: various kind of array
	 * @param	int		$id: uid to search in the list
	 * @return	array		returns the resultrow as an array
	 */
	function findUidinList($list, $id) {
		if (!is_array($list)) return null;
		$searchrow = null;
		foreach ($list as $catrow) {
			if ($catrow['uid'] == $id) {
				$searchrow = $catrow;
				break;
			}
		}
		return $searchrow;
	}

	/**
	 * searches for an uid in an given array and returns the found row. If multiple
	 * records with the same uid exists in the list
	 *
	 * @param	array		$list: various kind of array
	 * @param	int		$id: uid to search in the list
	 * @return	array		returns the resultrow as an array
	 */
	function checkCategoryUploadAccess($userID, $catID) {

		$catRow = $this->getCategory($catID);
		// Ralf Merz: $catRow may be empty (because of enableFields) so check this!
		if (empty($catRow)) {
			return false;
		}
		// check first, if no usergroup has been assigned to the given category
		if ($catRow['tx_damtree_fe_groups_uploadaccess'] == 0) {
			if ($this->debug == 1) {
				t3lib_div::debug('checkCategoryAccess = true (no group selected) catID: ' . $catID);
			}
			return true;
		}
		else {
			// get all usergroups a fe_user belongs to
			$usergroups = implode(',', $GLOBALS['TSFE']->fe_user->groupData['uid']);
			// TODO add error handling
			$mm_table = 'tx_dam_cat_uploadaccess_mm';
			// executing database search: should return a row with the usergroup(s)
			$local_table = $this->catTable;
			$foreign_table = 'fe_groups';
			$where = 'AND ' . $foreign_table . '.uid in (' . $usergroups . ') AND ' . $local_table . '.uid = ' . $catID;
			$select = $local_table . '.*';
			$res = $GLOBALS['TYPO3_DB']->exec_SELECT_mm_query($select, $local_table, $mm_table, $foreign_table, $where);

			while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
				$resultlist[] = $row;
			}
			if ($resultlist) {
				return true;
			}
			else {
				return false;
			}
		}
	}


	/**
	 * searches for an uid in an given array and returns the found row. If multiple
	 * records with the same uid exists in the list
	 *
	 * @param	array		$list: various kind of array
	 * @param	int		$id: uid to search in the list
	 * @param	int		$relID sets the relationship (which kind of Acess should be checked)
	 * @return	array		returns the resultrow as an array
	 */
	function checkCategoryAccess($userID, $catID, $relID) {
		/*var $relations = array(
			   '1' => 'readaccess',
			   '2' => 'downloadaccess',
			   '3' => 'uploadaccess'
		   );*/
		switch ($relID) {
			case 1:
				$relCheck = 'tx_damtree_fe_groups_readaccess';
				break;
			case 2:
				$relCheck = 'tx_damtree_fe_groups_downloadaccess';
				break;
			case 3:
				$relCheck = 'tx_damtree_fe_groups_uploadaccess';
				break;
			default:
				die('no rel ID given!');
		}

		// TODO: do we need an array with indexes from 0 on?
		// Or can we just use the groupData['uid'] array?
		$usergroups = implode(',', $GLOBALS['TSFE']->fe_user->groupData['uid']);
		$usergroups = explode(',', $usergroups);
		#$usergroups = $GLOBALS['TSFE']->fe_user->groupData['uid'];

		if ($usergroups) {
			$catRow = $this->getCategory($catID);
			// check first, if no usergroup has been assigned to the given category
			if ($relID == 1) {
				if (empty($catRow)) return false;
				if ($catRow['fe_group'] == -1) return false;
				if ($catRow['fe_group'] == -2) return true;

				$groups = explode(',', $catRow['fe_group']);
				if (empty($groups[0])) {
					return true;
				}
				foreach ($groups as $group) {
					if (in_array($group, $usergroups)) {
						return true;
					}
				}
			}
			else {
				if ($catRow[$relCheck] == 0) {
					if ($this->debug == 1) {
						t3lib_div::debug('checkCategoryAccess = true (no group selected) catID: ' . $catID);
					}
					return true;
				}
				else {
					// TODO add error handling
					$mm_table = 'tx_dam_cat_' . $this->relations[$relID] . '_mm';
					// executing database search: should return a row with the usergroup(s)
					$local_table = $this->catTable;
					$foreign_table = 'fe_groups';
					$where = 'AND ' . $foreign_table . '.uid in (' . $usergroups . ') AND ' . $local_table . '.uid = ' . $catID;
					$select = $local_table . '.*';
					$res = $GLOBALS['TYPO3_DB']->exec_SELECT_mm_query($select, $local_table, $mm_table, $foreign_table, $where);

					while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
						$resultlist[] = $row;
					}
					if ($resultlist) {
						return true;
					}
					else {
						return false;
					}

				}
			}
		}
		else {
			return false;
		}

	}

	function getCategoryTitleLocalized($row, $titleLen = 30) {

		$conf['sys_language_uid'] = $GLOBALS['TSFE']->sys_language_uid;

		$row['pid'] = tx_dam_db::getPid(); // @todo add to init of class

		$rowLocalized = tx_dam_db::getRecordOverlay('tx_dam_cat', $row, $conf);
		// @todo edit ts value for titleLen and add a crop
		$title = trim($row['title']);
		if ($rowLocalized === False) {
			return $row['title'];
		}
		else {
			return $rowLocalized['title'];
		}
	}


	/**
	 * gets all child categories of an existing category
	 *
	 * @param	int		$catID category to check
	 * @return	array		$recArray with all parent categories
	 */
	function getChildCategories($catID) {
		if (!intval($catID)) {
			if (TYPO3_DLOG) t3lib_div::devLog('parameter error in function getChildCategories: for the catID only integer values are allowed. Given value was:' . $catID, 'dam_frontend', 3);
		}
		else {
			if ($catID == null || $catID == 0) {
				if (TYPO3_DLOG) t3lib_div::devLog('parameter error in function getChildCategories: catID must be greater than 0 and not null! Given value was:' . $catID, 'dam_frontend', 2);
			}
			$SELECT = '*';
			$FROM = $this->catTable;
			$WHERE = 'parent_id = ' . $catID . ' AND sys_language_uid=0 ' . tslib_cObj::enableFields($this->catTable) . ' AND deleted = 0';
			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery($SELECT, $FROM, $WHERE);
			while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
				$records[] = $row;
			}
			return $records;
		}
	}

	/**
	 * checks if a category has children an on the lowest tree-level if it has files
	 *
	 * @param  $catUid	int	The Category UID to check for files
	 * @return bool
	 */
	public function checkCategoryForFiles($catUid) {
		$catWithChild = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
			'uid',
			'tx_dam_cat',
			'parent_id=' . $catUid
		);
		if (!empty($catWithChild)) {
			return true;
		}

		// TODO: language field is not used at the moment, maybe should
		$sysLanguageUid = strtoupper($GLOBALS['TSFE']->conf['']);

		$res = $GLOBALS['TYPO3_DB']->exec_SELECT_mm_query(
			'tx_dam.uid',
			'tx_dam',
			'tx_dam_mm_cat',
			'tx_dam_cat',
			$whereClause = '
					AND tx_dam.deleted=0
					AND tx_dam.hidden=0
					AND tx_dam_mm_cat.uid_foreign=' . $catUid,
			//. ' AND tx_dam.language=' . $sysLanguageUid,
			$groupBy = '',
			$orderBy = '',
			$limit = ''
		);
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			$damUidArray[] = $row;
		}

		if (empty($damUidArray)) {
			return false;
		} else {
			return true;
		}
	}

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/dam_frontend/DAL/class.tx_damfrontend_DAL_categories.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/dam_frontend/DAL/class.tx_damfrontend_DAL_categories.php']);
}
?>