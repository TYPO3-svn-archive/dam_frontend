<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2006-2008 in2form.com (typo3@bus-netzwerk.de)
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
 * @author Martin Baum <typo3@bus-netzwerk.de>
 *
 * Some scripts that use this class:	--
 * Depends on:		---
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 *   67: class tx_damfrontend_DAL_categories
 *   81:     function getCategory($catID)
 *  103:     function getCategory_Rootline($catID)
 *  127:     function getAllCategories()
 *  140:     function getSubCategories($catID, $limit=999)
 *  183:     function getParentCategories($catID)
 *  212:     function getCategoryMountpoints($userID, $relID)
 *  231:     function getCategories($userID, $relID)
 *  258:     function getCategories_Rootline($userID, $relID)
 *  270:     function isParentCategory($catID, $parentID)
 *  282:     function isSubCategory($catID, $subID)
 *  297:     function findUidinList($list, $id)
 *  318:     function checkCategoryAccess($userID, $catID)
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
	var $filter = ' AND deleted = 0 AND hidden = 0';
	// array with all availible access relations
	var $relations = array(
		'1' => 'readaccess',
		'2' => 'downloadaccess',
		'3' => 'uploadaccess'
	);
	var $mm_table_readaccess = '';  // mm Table which stores the groups, which have readaccess to a category
	var $mm_table_downloadaccess = ''; // mm Table which stores the groups
	var $debug = true;
	
	function getCategory($catID) {
		if (!intval($catID)) {
			if (TYPO3_DLOG) t3lib_div::devLog('parameter error in function getCategory: for the catID only integer values are allowed. Given value was:' .$catID, 'dam_frontend',3);
		}
		else {
			// retrieve data
			$SELECT = '*';
			$FROM = $this->catTable;
			$WHERE = 'uid = '.$catID . $this->filter;
			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery($SELECT, $FROM, $WHERE);
			$record =  $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
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
			 	if (!$this->findUidinList($completecats, $parentcat['uid']))
			 	{
			 		$completecats[] = $parentcat;
			 	}
			}
			return $completecats;
		}

	/**
	 * creates a list of all ate, which are currently availible.
	 * This is dtermined by the current frontend user and his membership
	 * of fe_groups
	 *
	 * @return	array		all availible documents as an array
	 * @todo getAllCategories: this function is not ready!
	 */
		function getAllCategories() {

		}



	/**
	 * get all subcategories of the specified category
	 *
	 * @param	int		$catID: name of the category to fiond all subcategories
	 * @param	int		$limit: how deep shall the call run
	 * @return	array		list of all subcategory of an extension
	 */
		function getSubCategories($catID, $limit=999) {
			if (!intval($catID)) {
				if (TYPO3_DLOG) t3lib_div::devLog('parameter error in function getSubCategories: for the catID only integer values are allowed. Given value was:' .$catID, 'dam_frontend',3);
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
				$WHERE = 'parent_id = '.$catID.$this->filter;
				$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery($SELECT, $FROM, $WHERE);

				// adding new category records to the table
				$z = 0;
				while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
					$subIDs[] = $row;
					$z++;
				}
				// subcategories found -> get them - search them
				if ($z > 0 && $limit > 0) {
					foreach($subIDs as $row) {
						$subrows = $this->getSubCategories($row['uid']);
						foreach($subrows as $subrow) {
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
				if (TYPO3_DLOG) t3lib_div::devLog('parameter error in function getParentCategories: for the catID only integer values are allowed. Given value was:' .$catID, 'dam_frontend',3);
			}
			else {
				if ($catID == null || $catID == 0 ) {
					if (TYPO3_DLOG) t3lib_div::devLog('parameter error in function getParentCategories: catID must be greater than 0 and not null! Given value was:' .$catID, 'dam_frontend',2);
				}
				$recArray = array();
				$record = $this->getCategory($catID);
				$parentID = intval($record['parent_id']);
				$recArray[] = $record;
				if ($parentID != 0)
				{
					$pRows = $this->getParentCategories($parentID);
					foreach($pRows as $pRow)  $recArray[] = $pRow;
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
			foreach($this->relations as $key => $relname ) {
				$catlist_rel[] = $this->getCategories($userID, $relID);
				foreach($catlist_rel as $cat) {
					if (!is_array($this->findUidinList($catlist_complete, $cat['uid']))) $catlist_complete[] = $cat;
				}
			}
			return $catlist_complete;
		}

	/**
	 * returns all categories, the current user has access to
	 *
	 * @param	int		$userID: id of the user, who has the right to use the system
	 * @param	int		$relID: id to specify read / downloadaccess
	 * @return	array	array of all category records
	 */
		function getCategories($userID, $relID) {
			if (!isset($userID) || !isset($relID) || $userID == '' || $relID == '') {
				if (TYPO3_DLOG) t3lib_div::devLog('parameter error in function getCategories: userID and relID must be set and empty strings are not allowed! Given value were$userID:' .$userID .' and relID: ' . $relID, 'dam_frontend',2);
			}
			$mm_table = 'tx_dam_cat_'.$this->relations[$relID].'_mm';
			// executing database search
			$local_table = $this->catTable;
			$foreign_table = 'fe_groups';
			$where = 'AND '.$local_table.'.uid = '. (int)$userID ;
			$select = $local_table.'.*';
			t3lib_div::debug($select.' / '.$local_table.' / '. $mm_table.' / '. $foreign_table.' / '. $where);
			$res = $GLOBALS['TYPO3_DB']->exec_SELECT_mm_query($select,$local_table, $mm_table, $foreign_table, $where);

			while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
				$resultlist[] = $row;
			}
			return $resultlist;
		}

	/**
	 * gets all categories related to an user, also categories,
	 * which are related via pagetree inheritence
	 *
	 * @param	int		$userID: id of the user
	 * @param	int		$relID: id of the relation in the
	 * @return	void		...
	 * @todo getCategories_Rootline: this function is not ready!
	 */
		function getCategories_Rootline($userID, $relID) {

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
	 * @param	array	$list: various kind of array
	 * @param	int		$id: uid to search in the list
	 * @return	array	returns the resultrow as an array
	 */
		function findUidinList($list, $id)
		{
			if (!is_array($list)) return null;
			$searchrow = null;
			foreach($list as $catrow) {
				if ($catrow['uid'] == $id)
				{
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
		 * @param	array	$list: various kind of array
		 * @param	int		$id: uid to search in the list
		 * @return	array	returns the resultrow as an array
		 */
		function checkCategoryAccess($userID, $catID) {

			$catRow = $this->getCategory($catID);
			// check first, if no usergroup has been assigned to the given category
			if ($catRow['tx_damtree_fe_groups_uploadaccess'] == 0) {
				if ($this->debug ==1) {
					t3lib_div::debug('checkCategoryAccess = true (no group selected) catID: '.$catID);
				}
				return true;
			}
			else {
				if($this->findUidinList($this->getCategories($userID,3),$catID)) {
					if ($this->debug ==1) {
						t3lib_div::debug('checkCategoryAccess = true catID: '.$catID);
						t3lib_div::debug($userID);
						$cats = $this->getCategories($userID,3);
						t3lib_div::debug($cats); 
					}
					return true;
				}
				else {
					if ($this->debug ==1) {
						t3lib_div::debug('checkCategoryAccess = false catID: '.$catID);
					}
					return false;
				}
			}
		}
	}
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/dam_frontend/DAL/class.tx_damfrontend_DAL_categories.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/dam_frontend/DAL/class.tx_damfrontend_DAL_categories.php']);
}
?>