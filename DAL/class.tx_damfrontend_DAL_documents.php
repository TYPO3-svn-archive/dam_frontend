<?php
require_once(t3lib_extMgm::extPath('dam_frontend').'/DAL/class.tx_damfrontend_DAL_categories.php');
require_once(t3lib_extMgm::extPath('dam').'/lib/class.tx_dam_indexing.php');
require_once(t3lib_extMgm::extPath('cms').'/tslib/class.tslib_search.php');
require_once(PATH_tslib.'class.tslib_content.php');
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
 * class.tx_damfrontend_DAL_documents.php
 *
 * What does it do?
 *
 * @package typo3
 * @subpackage tx_dam_frontend
 * @author Martin Baum <typo3@in2form.com>
 *
 * Some scripts that use this class:	---
 * Depends on:		--
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 *   80: class tx_damfrontend_DAL_documents
 *  121:     function setFullTextSearchFields($fieldlist)
 *  131:     function tx_damfrontend_DAL_documents()
 *  144:     function getCategoriesbyDoc($docID,$simple=false)
 *  176:     function checkAccess_fileRef($filePath)
 *  198:     function checkAccess($docID, $relID)
 *  223:     function getResultCount()
 *  251:     function checkOwnerRights($docID,$fe_user_uid)
 *  271:     function getDocumentFEGroups($docID, $relID)
 *  313:     function getDocument($docID)
 *  333:     function getDocumentList($userUID=0)
 *  488:     function getCategoriesByDoc_Rootline($docID)
 *  534:     function setFilter($filterArray)
 *  591:     function getSearchwordWhereString($searchword)
 *  609:     function evalDateError($day, $month, $year)
 *  625:     function saveMetaData($docID, $docData)
 *  644:     function addDocument($path, $docData='')
 *  695:     function categoriseDocument($uid, $catArray)
 *  719:     function delete_document ($uid,$deleteFile=false, $userUID = 0)
 *  744:     function get_FEUserName ($uid=0)
 *  771:     function versioningCreateNewVersionPrepare($docID)
 *  819:     function versioningCreateNewVersionExecute($docID)
 *  958:     function versioningOverridePrepare($docID)
 * 1001:     function versioningOverrideExecute($docID)
 * 1045:     function delete_category ($uid, $catID)
 * 1059:     function checkDocumentAccess($docFEGroups)
 * 1096:     function storeDocument($docID)
 *
 * TOTAL FUNCTIONS: 26
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */
	class tx_damfrontend_DAL_documents {
		var $fileTypeList;				// Contains a list of filetypes, the selection is restricted to
		var $uidList;					// Array which contains all selected Files

		var $resultCount;				// After any executed query - this var contains the rowcount of the result
		var $catLogic;					// Pointer to the Category access Layer
		var $searchwords;				// array of searchwords, the user might have searched for

		var $conf =array();				// Configuration Array


		var $catTable = 'tx_dam_cat';
		var $docTable = 'tx_dam';
		var $mm_Table = 'tx_dam_mm_cat';
		var $filter = ' AND deleted = 0 AND hidden = 0';

		var $additionalFilter = '';		// string which contains an additional where string, cerated by setFilter Method
		var $orderBy = ''; // contains an orderby clause
		var $limit = '';
		var $categories = array(); // contains all categories, documents shall be shown from

		var $selectionMode = '';
		var $searchAllCats;
		var $fullTextSearchFields = 'title';
		var $searchword = false;

		var $relations = array(
			'1' => 'readaccess',
			'2' => 'downloadaccess',
			'3' => 'uploadaccess'
		);

		var $feuser;			// pointing to the fe user object instance - please use this instead of GLOBALS['TSFE']

		/**
		 * Sets fullTextSearchFields - in which fields should be searched
		 *
		 * description')
		 *
		 * @param	string		$fieldlist kommaseparated list of fields (f.e. 'title,
		 * @return	void
		 */
		function setFullTextSearchFields($fieldlist) {
			$this->fullTextSearchFields = $fieldlist;
		}


	/**
	 * inits the class
	 *
	 * @return	array		list of all categories
	 */
		function tx_damfrontend_DAL_documents() {
			$this->catLogic = t3lib_div::makeInstance('tx_damfrontend_DAL_categories');
			$this->feuser = $GLOBALS['TSFE']->fe_user;
		}

	/**
	 * gets all categories for the specified document and returns them as an array
	 *
	 * @param	int		$fileUid: ...
	 * @param	[type]		$simple: ...
	 * @return	array		list of all categories
	 */
		function getCategoriesbyDoc($docID,$simple=false) {

			// array which accumulates all records
			$cats = array();

			$local_table = $this->docTable;
			$mm_table = $this->mm_Table;
			$foreign_table = $this->catTable;
			$WHERE = 'AND '.$local_table.'.uid = '.$docID;
			$res = $GLOBALS['TYPO3_DB']->exec_SELECT_mm_query($this->catTable.'.*',$local_table ,$mm_table ,$foreign_table ,$WHERE);

			while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
				if ($simple==true) {
					$cats[] = $row['uid'];
				}
				else {
				$cats[] = $row;
			}

			}
			return $cats;
		}

	/**
	 * Checks, if access is allowed by a given file (path + name)
	 * This function is retrieving the path filename and then checks, if a user has access to that file
	 *
	 * @param	string		$filePath: ...
	 * @return	boolean		true if access is allowed
	 * @see checkAccess
	 */
		function checkAccess_fileRef($filePath) {
			// getting filename and filepath from the given path
			$splitpos = strrpos($filePath, '/') + 1;
			$file = substr($filePath, $splitpos);
			$path = substr($filePath, 0, $splitpos);

			$where = 'file_path = \''.$path.'\' AND file_name=\''.$file.'\'';

			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid', $this->docTable, $where);
			$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
			return $this->checkAccess(intval($row['uid']),2);
		}

	/**
	 * checks, if the current user has access to an specific document
	 * if the document has no g, the access is not limited
	 *
	 * @param	int		$docID: uid of the the document, which delimites, if the user has access or not
	 * @param	int		$relID: ID in the array $this->realtions. Determines, which database relation is used (1: Readaccess; 2: Download / Edit Access)
	 * @return	boolean		returns true if the user has access to this file
	 */
		function checkAccess($docID, $relID) {
			if (!isset($docID) || $docID == '') {
				if (TYPO3_DLOG) t3lib_div::devLog('parameter error in function checkAccess: for the docID only integer values are allowed. Given value was:' .$docID, 'dam_frontend',3);
			}
			if (!isset($relID) || $relID == ''){
				if (TYPO3_DLOG) t3lib_div::devLog('parameter error in function checkAccess: for the relID only integer values are allowed. Given value was:' .$relID, 'dam_frontend',3);
			}
			// all frontend usergroups assigned to the document
			$docgroups = $this->getDocumentFEGroups($docID, $relID);
			if (empty($docgroups)) return true; // no groups assigned - allow access
			// get the ID's of the usergroups, the current user is a member of
			$usergroups = $this->feuser->groupData['uid'];
			$valid = false;
			foreach($docgroups as $docgroup) {
				if ((array_search($docgroup['uid'], $usergroups))) {
					$valid=true;
				}
			}
			return $valid;
		}

	/**
	 * Returns the number of rows for the result browser
	 *
	 * @return	int		Number of rows
	 * @todo: return number of rows by sql query
	 */
		function getResultCount() {
			foreach($this->categories as $catList) {
				if (count($catList)) {
					$catStringArr[] = '( '.$this->catTable.'.uid='.implode(' OR '.$this->catTable.'.uid=',$catList).')';
				}
			}
			// $catString = '( '.$this->catTable.'.uid='.implode(' OR '.$this->catTable.'.uid=',$this->categories).')';
			count($catStringArr) > 1 ? $catString = implode(' AND ', $catStringArr):$catString = $catStringArr[0];
			$select = ' DISTINCT '.$this->docTable.'.uid';
			$local_table = $this->docTable;
			$foreign_table = $this->catTable;
			$mm_table = $this->mm_Table;
			$where = ' AND '.$catString.' AND '.$this->docTable.'.deleted = 0  AND '.$this->docTable.'.hidden = 0'.$this->additionalFilter;
			$res = $GLOBALS['TYPO3_DB']->exec_SELECT_mm_query($select,$local_table, $mm_table, $foreign_table, $where);
			//return $GLOBALS['TYPO3_DB']->sql_num_rows($res);
			//todo check this / fix this
			return 10;
		}

	/**
	 * checks if a user has the right for edits and deletions
	 *
	 * @param	int		$fe_user_uid UserID
	 * @param	int		$docID id of the document
	 * @return	true		if user is allowed to edit		...
	 * @author Stefan Busemann
	 * @todo extend support for fe_groups
	 */
		function checkOwnerRights($docID,$fe_user_uid) {
			$doc = $this->getDocument($docID);
			return $this->checkEditRights($doc);
		}



	/**
	 * creates a list of all groups, which are assosiated with an FE Group
	 * also parent categories of the document are included
	 *
	 * @param	int		$docID: uid of the document
	 * @param	int		$relID: ID in the array $this->realtions. Determines, which database relation is used (1: Readaccess; 2: Download / Edit Access)
	 * @return	array		Array which contains all usergroups associated with the file
	 */
		function getDocumentFEGroups($docID, $relID) {
			if ($docID == null || !intval($docID)) {
				if (TYPO3_DLOG) t3lib_div::devLog('parameter error in function getDocumentFEGroups: for the docID only integer values are allowed. Given value was:' .$docID, 'dam_frontend',3);
			}
			if ($relID == null || !intval($relID)) {
				if (TYPO3_DLOG) t3lib_div::devLog('parameter error in function getDocumentFEGroups: for the relID only integer values are allowed. Given value was:' .$relID, 'dam_frontend',3);
			}

			// first find all categories for the given document
			if ($this->conf['filelist.']['security_options.']['checkCategoriesByRootline']==1) {
				// if the rootline option is enabled, all categoris from the rootline are checked
				$catlist = $this->getCategoriesByDoc_Rootline($docID);
			}
			else {
				$catlist = $this->getCategoriesByDoc($docID);
			}
			$grouparray = array();
			if ($relID==1) {
				foreach ($catlist as $category){
					$groups =  explode(',',$category['fe_group']);
					foreach($groups as $group) {
						if ($group)	{
							#$row = $GLOABLS['TYPO_3']->exec_SELECTgetSingleRow ('*', 'fe_groups', 'uid = ' . $group);
							$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery ('*', 'fe_groups', 'uid = ' . $group);
				
							$row =  $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
				
						$grouparray[] = $row;
						}
						else {
							if ($this->conf['filelist.']['security_options.']['checkAllAsignedCategories']==0) {
								// if no group is asigned, gain access
								return array();
							}
						}
					}
				}
				
			}
			else {
				foreach ($catlist as $category){
					if ($category['uid']>0) {
						$mm_table = 'tx_dam_cat_'.$this->relations[$relID].'_mm';
						// executing database search
						$local_table = $this->catTable;
						$foreign_table = 'fe_groups';
						$where = 'AND '.$local_table.'.uid = '.$category['uid'];
						$select = $foreign_table.'.*';
						$res = $GLOBALS['TYPO3_DB']->exec_SELECT_mm_query($select,$local_table, $mm_table, $foreign_table, $where);

						// adding groups from the database to the GroupArray - check if group is already in list
						while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)){
							if (!is_array($this->catLogic->findUidinList($grouparray, $row['uid'])))
							{
								$grouparray[] = $row;
							}
						}
						$GLOBALS['TYPO3_DB']->sql_free_result($res);
					}
				}
			}
			return $grouparray;
		}

	/**
	 * Returns the resultlist of a requestet DAM Document
	 *
	 * @param	[int]		$docID: the UID of a document
	 * @return	[SQL]		Result
	 */
		function getDocument($docID) {
			$select = '*';
			$where = 'uid ='.$docID;
			$from = $this->docTable;
			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select, $from, $where);
			$row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
			if ($this->conf['filelist.']['useLanguageOverlay']==1) {
				$langConf['sys_language_uid'] = $GLOBALS['TSFE']->sys_language_uid;
				$row['pid']=tx_dam_db::getPid();
				$row = tx_dam_db::getRecordOverlay('tx_dam', $row, $conf);
			}
			$GLOBALS['TYPO3_DB']->sql_free_result($res);
			return $row;
		}


	/**
	 * Returns the resultlist of a requestet DAM Document
	 *
	 * @param	[string]	$filename: filename of the requested file
	 * @param	[string]	$path: path to the requested file
	 * @return	[array]		dam record
	 */
		function getDocumentByFilename($filename, $path) {
			$select = '*';
			$where = 'file_name like '. $GLOBALS['TYPO3_DB']->fullQuoteStr($filename,$this->docTable) . ' AND file_path like ' .$GLOBALS['TYPO3_DB']->fullQuoteStr($path,$this->docTable);
			#﻿. $GLOBALS['TYPO3_DB']->fullQuoteStr($filename) . ' AND file_path = '. $GLOBALS['TYPO3_DB']->fullQuoteStr($path);
			$from = $this->docTable;
			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select, $from, $where);
			return $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
		}



	/**
	 * generates a list of all availible documents. Used by the frontend. The
	 * selection is filterd by the given list of categories and the
	 * access restrictions  -> relation "READ ACCESS" defined for the document
	 *
	 * @param	[type]		$userUID: ...
	 * @return	[array]		returns an array which contains all selected records
	 */
	function getDocumentList($userUID=0) {

		$filter =  tslib_cObj::enableFields('tx_dam');
		if(!is_array($this->categories)) {
			if (TYPO3_DLOG) t3lib_div::devLog('parameter error in function getDcoumentList: for the this->categories is no array. Given value was:' .$this->categories, 'dam_frontend',3);
		}

		$hasCategories=false;
		foreach ($this->categories as $cat) {
			if (!empty($cat) ) $hasCategories=true;
		}
		if ($hasCategories===true) {

			/*
			 * Building the from clause manually by joining the DAM tables
			 */
			$select = $this->docTable.'.uid';
			$from = $this->docTable.' INNER JOIN '.$this->mm_Table.' ON '.$this->mm_Table.'.uid_local  = '.$this->docTable.
			'.uid INNER JOIN '.$this->catTable.' ON '.$this->mm_Table.'.uid_foreign = '.$this->catTable.'.uid';
			if ($this->conf['searchCategoryAttributes'] == 1 AND $this->searchword) {
				if ($this->conf['searchCategoryAttributes.']['fields']) {
					$catSearchString = '';
					foreach(explode(',',$this->conf['searchCategoryAttributes.']['fields']) as $field) {
						$catSearchString .=  ' OR ('.$this->catTable.'.'.$field.' LIKE "%'.$GLOBALS['TYPO3_DB']->quoteStr(trim($this->searchword), $this->catTable).'%")';
					}
					t3lib_utility_Debug::debug($catSearchString, __FILE__ . __LINE__);
					$this->additionalFilter = str_replace('###CATMARKER###', $catSearchString ,$this->additionalFilter);
				}
			}

			$filter .= $this->additionalFilter;


			// preparing the category array - deleting all empty entries
			foreach($this->categories as $number => $catList) {
				if (!count($catList)) {
					unset($this->categories[$number]);
				}
			}

			$queryText = array();
			$z = 0;

			/**
			 * every element in the categories array stores a list of cats that are associated with an array
			 */
			foreach($this->categories as $number => $catList) {

					if ($this->searchAllCats === true) {
						if ($this->conf['searchAllCats_allowedCats']) {
								// limit the search in categories
							$catString ='('.$this->catTable.'.uid IN ('. $this->conf['searchAllCats_allowedCats'] .'))';
						}
						else {
								// no limitation for category is set
							$catString = "1=1";
						}
					}
					else {
						$catString = '( '.$this->catTable.'.uid='.implode(' OR '.$this->catTable.'.uid=',$catList).')';
					}

					if  ($this->conf['useTreeAndSelection'] == 1) {
						if ($z != count($this->categories)-1) {
							if (!count($queryText)) {
								$queryText[] = $GLOBALS['TYPO3_DB']->SELECTquery($select,$from,  $catString);
							}
							else {
								$where = $this->docTable.'.uid IN ('.$queryText[count($queryText)- 1].') AND '.$catString;
								$queryText[] = $GLOBALS['TYPO3_DB']->SELECTquery('tx_dam.uid', $from, $where);
							}
						}
							// building the last element of the list - final building of the list
						else {
							if(count($this->categories ) > 1) {
								$where = $this->docTable.'.uid IN ('.$queryText[count($queryText)- 1].') AND '.$catString.$filter;
							}
								// list is having more then one "AND" criteria
							else {
									// filter is added in case there is only one cat selected
								$where = $catString.$filter;
							}
							$select = ' DISTINCT '.$this->docTable.'.*';
						}
					}
					else {
							if ($where <>'') {
								$where .= ' OR ';
							}
							$where .=  $catString;
					}
					$z++;
				}

				if  ($this->conf['useTreeAndSelection'] == 0) {
					$where = '('. $where .')'. $filter;
				}

				// adding access information for categories
				$where .=  tslib_cObj::enableFields($this->catTable);

				// limit the categories. Hide those categories, a use has not access to it
				if ($this->conf['filelist.']['security_options.']['checkAllRelatedCategories']==1) {
					$resctrictedUids = $this->getCategoriesWithNoAccess();
					if ($resctrictedUids)	$where .= ' AND NOT tx_dam_cat.uid IN ('.$resctrictedUids.')';
				}
			}
			else {
				// query without using categories
				$from=$this->docTable;
				if ($this->conf['searchAllCats_allowedCats']) {
					// limit the search in categories to only allowed categories
					 $filter .='AND ('.$this->catTable.'.uid IN ('. $this->conf['searchAllCats_allowedCats'] .'))' . tslib_cObj::enableFields($this->catTable);
					 $from = $this->docTable.' INNER JOIN '.$this->mm_Table.' ON '.$this->mm_Table.'.uid_local  = '.$this->docTable.'.uid INNER JOIN '.$this->catTable.' ON '.$this->mm_Table.'.uid_foreign = '.$this->catTable.'.uid';

					// limit the categories. Hide those categories, a use has not access to it
					if ($this->conf['filelist.']['security_options.']['checkAllRelatedCategories']==1) {
						$resctrictedUids = $this->getCategoriesWithNoAccess();
						if ($resctrictedUids)	$filter .= ' AND NOT tx_dam_cat.uid IN ('.$resctrictedUids.')';
					}
				}
				$filter .= $this->additionalFilter;
				$select='*';
				$where.= ' 1=1 '.$filter;
			}


			$select = ' DISTINCT '.$this->docTable.'.*';
			if ($this->conf['useLatestList']==1) {
					// if latest days is set the
					if (intval($this->conf['latestDays'])>0) {
						$d = intval($this->conf['latestDays']);
						$dateLimit  = time() - (60*60*24*$d);

						$where.= ' AND '.$this->docTable.'.'.$this->conf['latestField'] .' > '.$dateLimit ;
						$this->conf['latestLimit']=0;
					}
					else {
						if ($this->orderBy) {
							$this->orderBy =$this->docTable.'.'.$this->conf['latestField'] . ' DESC, ' . $this->orderBy;
						}
						else {
							$this->orderBy =$this->docTable.'.'.$this->conf['latestField'] . ' DESC';
						}
					}
			}


			if ($this->conf['useGroupedView']==1) {
				$select .= ','. $this->catTable.'.title AS categoryTitle';
				$groupedOrderBy = 'ASC';
				// check if as ts setting exists, and if it is correct
				if 	($this->conf['filelist.']['groupedFileListCategorySorting'] AND
					($this->conf['filelist.']['groupedFileListCategorySorting']=='ASC' OR
					 $this->conf['filelist.']['groupedFileListCategorySorting']=='DESC')) $groupedOrderBy = $this->conf['filelist.']['groupedFileListCategorySorting'];

				if ($this->orderBy) {
					$this->orderBy = $this->catTable.'.title '.$groupedOrderBy.','. $this->orderBy;
				}
				else {
					$this->orderBy = $this->catTable.'.title '.$groupedOrderBy;
				}

			}

		if ($this->conf['filelist.']['security_options.']['showOnlyFilesWithPermission']==1) {
			$where .=  $this->getOnlyFilesWithPermissionSQL();
		}

		// replace a maybe existing marker, if still there
		$where= str_replace('###CATMARKER###', '' ,$where);
			// is defnied as: $this->internal['list']['limit'] = $this->internal['list']['pointer'].','. ($this->internal['list']['listLength']);
			// limit = "pointer,counter"
		list($pointer, $listLength) = explode (',',$this->limit);
		$startRecord = $pointer * $listLength;
		$endRecord = $startRecord + $listLength;


			//Debug statements
		if ($this->conf['enableDebug']==1) {
			if ($this->conf['debug.']['tx_damfrontend_DAL_documents.']['getDocumentList.']['SQL']==1)		t3lib_utility_Debug::debug('SELECT ' . $select . ' FROM ' . $from . ' WHERE '. $where . ' ORDER BY '  .$this->orderBy . ' LIMIT '. $startRecord.','.$listLength,'SQL Statement');
			if ($this->conf['debug.']['tx_damfrontend_DAL_documents.']['getDocumentList.']['conf']==1)		t3lib_utility_Debug::debug($this->conf,'Configuration');
		}

		// do not select missing files
		$where   .=" AND tx_dam.file_status != 255";

		// show only records of the live workspace
		$where .= ' AND tx_dam.pid!=-1 AND tx_dam.t3ver_state!=1';

		// get result counter
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('DISTINCT tx_dam.uid', $from, $where,'',$this->orderBy);

			// TODO check why count does not work?
		#$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('DISTINCT count(tx_dam.uid) AS counter', $from, $where);


		$resultCounter = $GLOBALS['TYPO3_DB']->sql_num_rows($res);

			// if latest list is used and a fixed number of entries has to be shown
		if ($this->conf['latestLimit']>0 ){
			$startRecord=0;
			$listLength = $this->conf['latestLimit'] ;
			if ($resultCounter>$this->conf['latestLimit'])	$resultCounter =  $this->conf['latestLimit'] ;
		}


		$whereAccess =$where;

			// executing the final query and convert the results into an array
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select, $from, $where,'',$this->orderBy,$startRecord.','.$listLength);
		$result = array();

		if ($this->conf['filelist.']['useLanguageOverlay']==1) {
				$langConf['sys_language_uid'] = $GLOBALS['TSFE']->sys_language_uid;
				$damPID=tx_dam_db::getPid();
		}

		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			if ($this->conf['enableDebug']==1) {
				if ($this->conf['debug.']['tx_damfrontend_DAL_documents.']['getDocumentList.']['rows']==1)		t3lib_utility_Debug::debug($row);;
			}

			if ($this->conf['enableDebug']==1) {
				if ($this->conf['debug.']['tx_damfrontend_DAL_documents.']['getDocumentList.']['rowsAfterAccessCheck']==1)		t3lib_utility_Debug::debug($row);;
			}

			
			// do version overlay only if be_user is active
			if($GLOBALS['TSFE']->beUserLogin) {
				$GLOBALS['TSFE']->sys_page->versionOL('tx_dam',$row, FALSE);
			}
			
			if ($this->conf['filelist.']['useLanguageOverlay']==1) {
				$row['pid']=$damPID;
				$row = tx_dam_db::getRecordOverlay('tx_dam', $row, $conf);
			}

			if ($this->checkAccess($row['uid'],2)) {
					$row['allowDownload']=1;
			}
			else {
					$row['allowDownload']=0;
			}

				//add a delete information
			if ($this->checkEditRights($row)===TRUE){
				$row['allowDeletion']=1;
				$row['allowEdit']=1;
			}


			$result[] = $row;

		}

		$this->resultCount = $resultCounter;
		$GLOBALS['TYPO3_DB']->sql_free_result($res);
		return $result;
	}



	/**
	 * creates a list of all categories, a document is associated
	 * Interited groups from parent folders are included in this list
	 *
	 * @param	int		$docID: id of the table, to get the pages from
	 * @return	array		list of categories
	 */
		function getCategoriesByDoc_Rootline($docID)
		{
			if(!intval($docID)) {
				if (TYPO3_DLOG) t3lib_div::devLog('parameter error in function getCategoriesByDoc_Rootline: docID must be an int value. Given value was:' .$this->categories, 'dam_frontend',3);
			}
			// array which accumulates all records
			$cats = array();

			$local_table = $this->docTable;
			$mm_table = $this->mm_Table;
			$foreign_table = $this->catTable;
			$WHERE = 'AND '.$local_table.'.uid = '.$docID.' AND '.$foreign_table.'.deleted=0'.' AND '.$foreign_table.'.hidden=0';
			$res = $GLOBALS['TYPO3_DB']->exec_SELECT_mm_query($this->catTable.'.*',$local_table ,$mm_table ,$foreign_table ,$WHERE);

			while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
				$cats[] = $row;
			}
			// create a list of all parent categories
			if (!is_array($cats)) {
				return array();
			}
			// create a list of all parent categories
			$completecats = array();
			foreach ($cats as $category)
			{
				 $parents = $this->catLogic->getParentCategories($category['uid']);
				 for ($z = 0; $z < count($parents); $z++)
				 {
				 	$parentcat = $parents[$z];
				 	// if the parent category isn't already in the list add it
				 	if (!$this->catLogic->findUidinList($completecats, $parentcat['uid']))
				 	{
				 		$completecats[] = $parentcat;
				 	}
				 }
			}
			return $completecats;
		}

	/**
	 * this function creates the WHERE - String with all availible filters
	 * input filter requests are mapped to SQL - querys
	 *
	 * @param	array		$filterArray: array of all get - Vars which are relatet with the filter system
	 * @return	array		returns an array with error codes - filled while cration of the form
	 */
		function setFilter($filterArray) {
			if (!is_array($filterArray)){
				return array();
			}
			// searching in all Documents if filter is set
			if ($filterArray['searchAllCats']===true) {
				$this->searchAllCats = true;
			}
			else {
				$this->searchAllCats = false;
			}

			$errors = array();
			$this->additionalFilter = '';
			/********************************
			 *
			 * Setting date and time filter
			 * if nothing is inserted - nothing is done and nothing is checked
			 * if something is inserted - the date must be complete and in possible range
			 *
			 ********************************/
			if ($filterArray ['from_day'] != '' && $filterArray['from_month'] != '' && $filterArray['from_year'] != '') {
				if ($this->evalDateError($filterArray['from_day'], $filterArray['from_month'], $filterArray['from_year'])) {
					$timestamp = mktime(0,0,0,$filterArray['from_month'], $filterArray['from_day'], $filterArray['from_year']);
					$this->additionalFilter .= ' AND '.$this->docTable.'.crdate >= '.$timestamp;
				} else {
					$errors['error_from_date'] = 1;
				}
			}

			if ($filterArray ['to_day'] != '' && $filterArray['to_month'] != '' && $filterArray['to_year'] != '') {
				if ($this->evalDateError($filterArray['to_day'], $filterArray['to_month'], $filterArray['to_year'])) {
					$timestamp = mktime(0,0,0,$filterArray['to_month'], $filterArray['to_day'], $filterArray['to_year']);
					$this->additionalFilter .= ' AND '.$this->docTable.'.crdate <= '.$timestamp;
				} else {
					$errors['error_to_date'] = 1;
				}
			}

				// if the filetype filter is a group of filetypes
			if ($this->conf['filterView.']['filetypes.'][$filterArray['filetype'].'.']) {
				foreach ($this->conf['filterView.']['filetypes.'][$filterArray['filetype'].'.'] as $ext =>$type) {
					$types[]= $GLOBALS['TYPO3_DB']->fullQuoteStr($ext,'tx_dam');
				}
				 $this->additionalFilter .= ' AND ' . $this->docTable.'.file_type IN ('.implode( ',', $types) .') ';
			}
			else {
				if ($filterArray['filetype'] != '' && $filterArray['filetype'] != ' ') $this->additionalFilter .= ' AND '.$this->docTable.'.file_type = \''.$filterArray['filetype'].'\'' ;
			}

			if ($filterArray['searchword'] != '' && $filterArray['searchword'] != ' ') {
				$this->searchword = $filterArray['searchword'];
				$this->additionalFilter .= $this->getSearchwordWhereString($filterArray['searchword'],'',true);
			}
			else {
				$this->searchword = false;
			}

			if ($filterArray['creator'] != '' && $filterArray['creator'] != ' ') $this->additionalFilter .= $this->getSearchwordWhereString($filterArray['creator'],'creator');

			if ($filterArray['owner'] > 0 ) $this->additionalFilter .=   ' AND '.$this->docTable.'.tx_damfrontend_feuser_upload  ='.$filterArray['owner'];

			if (trim($filterArray['LanguageSelector']) != '' && $filterArray['LanguageSelector'] != 'nosel') $this->additionalFilter .=  ' AND '.$this->docTable.'.language = "'.trim($filterArray['LanguageSelector']).'"';

			if ($filterArray['showOnlyFilesWithPermission'] == 1) $this->additionalFilter .=  ' AND '.$this->docTable.'.fe_group <>"" AND '.$this->docTable.'.fe_group <>"-1" AND '.$this->docTable.'.fe_group <>"-2" AND '.$this->docTable.'.fe_group <>"0"';

			if (is_array($filterArray['searchAllCats_allowedCats'])) $this->conf['searchAllCats_allowedCats'] = implode(',',$filterArray['searchAllCats_allowedCats']);
				// looking for custom filters
			if (is_array($filterArray['customFilters'])) {
				foreach ($filterArray['customFilters'] as $filter=>$value) {
					switch ($value['type']) {
						case 'TEXT':
							$this->additionalFilter .= $this->getCustomWhereString($value['field'],isset($value['value'])?$value['value']:$filterArray[$filter]);
							break;
                        case 'INT':
                            $this->additionalFilter .= $this->getCustomWhereInt($value['field'],isset($value['value'])?$value['value']:$filterArray[$filter]);
                            break;
					}
				}
			}

			if (is_array($filterArray['staticFilters.'])) {
				foreach ($filterArray['staticFilters.'] as $filter=>$value) {
					switch ($value['type']) {
						case 'TEXT':
							$this->additionalFilter .= $this->getCustomWhereString($value['field'],isset($value['value'])?$value['value']:$filterArray[$filter]);
							break;
						case 'BOOLEAN':
							$this->additionalFilter .= ' AND '. $this->docTable.'.'.$value['field'].'='.intval($value['value']);
							break;
					}
				}
			}

			return $errors;
		}


	/**
	 * returns a searchword transfered to int
	 *
	 * @param	string		$searchword: blank separated string
	 * @param	string		$searchField: blank separated string (optional)
	 *
	 * @return	string		where clause, ready for adding it to the document array
	 */
		function getSearchwordWhereString($searchword,$searchField='',$addCatMarker=false) {
			if ($searchField) {
				$searchFields[] = $searchField;
			}
			else {
				$searchFields = t3lib_div::trimExplode(',', $this->fullTextSearchFields, true);
			}
			if (0 == count($searchFields)) { return ''; }

			$queryPart = array();

			$sword_array = $this->get_searchWordArray($searchword);
			if (is_array($sword_array))	{
				foreach ($searchFields as $field) {
					$searchStringOR=array();
					$searchSQL_OR='';
					$searchStringAND=array();
					$searchSQL_AND='';

					foreach ($sword_array as $key=>$word) {
							switch ($word['oper']) {
							case 'OR':
									$searchStringOR[] 		= '('. $this->docTable.'.'.$field . ' LIKE "%'.$GLOBALS['TYPO3_DB']->quoteStr(trim($word['sword']), $this->docTable).'%")';
								break;
							case 'AND':
									$searchStringAND[] 		= '('. $this->docTable.'.'.$field . ' LIKE "%'.$GLOBALS['TYPO3_DB']->quoteStr(trim($word['sword']), $this->docTable).'%")';
								break;
							case 'AND NOT':
									$searchStringAND[] 	= '('. $this->docTable.'.'.$field . ' NOT LIKE "%'.$GLOBALS['TYPO3_DB']->quoteStr(trim($word['sword']), $this->docTable).'%")';
								break;
							default:
								$searchStringOR[] 			=  '('. $this->docTable.'.'.$field . ' LIKE "%'.$GLOBALS['TYPO3_DB']->quoteStr(trim($word['sword']), $this->docTable).'%")';
								break;
						}
					}
					if (!empty($searchStringOR)) $searchSQL_OR = '(' . implode(' OR ',$searchStringOR ).')';
					if (!empty($searchStringAND)) $searchSQL_AND = '(' . implode(' AND ',$searchStringAND ).')';
					if ($searchSQL_AND<>'') {
						$result = $searchSQL_AND;
					}
					else {
						$result = $searchSQL_OR;
					}
					if ($searchSQL_AND<>'' AND $searchSQL_OR<>'') $result = $searchSQL_AND . ' AND ' . $searchSQL_OR;
					$queryPart[]=$result;
				}

				# Catmarker: in some case the name of categories should be searched too. But when the funktion
				# "setFilter" is executed, we do not know, if the query has categories or not. So we add a marker
				# which is replaced later
				$catMarker = '';
				if ($addCatMarker){
					$catMarker = ' ###CATMARKER###';
				}
				return ' AND (' . implode(' OR ', $queryPart) . $catMarker . ' ) ';
			}
		}

	/**
	 * evaluates the given Date and returns true if the date is in correct form to covert it to timestamp
	 *
	 * @param	int		$day: day of month
	 * @param	int		$month: month of year
	 * @param	int		$year: year in
	 * @return	bool		if the given date is correct - return  true, if something is wrong or missing - return false
	 */
		function evalDateError($day, $month, $year) {
			if (($day == '') || ($month == '') || ($year == '') || ($day > 31) ||($month > 12) || ($month < 1) || ($day < 1)) {
					return false;
				}
				else {
					return true;
				}
		}

	/**
	 * [Describe function...]
	 *
	 * @param	[type]		$docID: ...
	 * @param	[type]		$docData: ...
	 * @return	[type]		...
	 */
		function saveMetaData($docID, $docData) {
			foreach( $docData as $key => $value ) {
				$DATA[$key] = $value;
				$DATA['tstamp']=time();
			}
			$TABLE = 'tx_dam';
			$WHERE = 'uid = '.$docID;
			$GLOBALS['TYPO3_DB']->exec_UPDATEquery($TABLE,$WHERE,$DATA);
		}


		/**
 * adding an document to the index
 *
 * @param	[string]		$path: path where file is stored
 * @param	[array]		$docData: array of the document data
 * @return	[int]		$newID: new UID of the dam_record
 */
		function addDocument($path, $docData='') {

			// the indexer gets the metadata from the document
			$indexer = t3lib_div::makeInstance('tx_dam_indexing');
			$indexer->init();
			$indexer->setDefaultSetup();
			$indexer->initEnabledRules();
			$indexer->collectMeta = true;
			$indexer->setDryRun(true); // just getting metadata from the dock
			$indexer->setPID(tx_dam_db::getPid());
			$indexer->setRunType("man");
			$data = $indexer->indexfile($path,0);
			$newrecord = $data['fields'];


			// adding the data from the form to the new indexed data
			if (is_array($docData)) {
				foreach($docData as $key => $value) {
					$newrecord[$key] = $value;
				}
			}

			if (!is_array($this->feuser->user)) die('no frontend user logged in');
			$newrecord['tx_damfrontend_feuser_upload'] = $this->feuser->user['uid'];

			// unsetting all array elements, which are not used
			unset($newrecord['__type']);
			unset($newrecord['__exists']);
			unset($newrecord['file_title']);
			unset($newrecord['file_path_absolute']);
			unset($newrecord['file_path_relative']);
			unset($newrecord['file_extension']);
			unset($newrecord['file_owner']);
			unset($newrecord['file_perms']);
			unset($newrecord['file_writable']);
			unset($newrecord['file_readable']);
			// executing the insert operation for the database
			$GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_dam', $newrecord);
			// FIXME insert error handling, if $newID is empty or 0
			$newID = $GLOBALS['TYPO3_DB']->sql_insert_id();
			return $newID;
		}

	/**
	 * Insert the category selection in the mm table of the dam
	 *
	 * @param	[int]		$uid: ...
	 * @param	[array]		$catArray: ...
	 * @return	[void]		...
	 */
		function categoriseDocument($uid, $catArray) {
			if (!intval($uid) || !is_array($catArray)) die('Parametererror in categoriseDocument: Check DatabaseID:' . $uid);
			// clear all cats
			$GLOBALS['TYPO3_DB']->exec_DELETEquery($this->mm_Table, 'uid_local ='.$uid);

			foreach($catArray as $catID) {
				if (!intval($catID)) die('one categoryID was not delivered as Integer');
				$newrow = array(
					'uid_local' => $uid,
					'uid_foreign' => $catID
				);
				$GLOBALS['TYPO3_DB']->exec_INSERTquery($this->mm_Table, $newrow);
					// TODO add error handling
			}
	 		return true;
		}

		/**
		 * @param	int		$uid UID of the dam entry, which should be created
		 * @param	[type]		$deleteFile: ...
		 * @param	[type]		$userUID: ...
		 * @return	boolean		true, if the deletion was sucessful
		 * @author stefan
		 */
		function delete_document ($uid,$deleteFile=false, $userUID = 0) {
			$doc = $this->getDocument($uid)	;
			if ($doc['tx_damfrontend_feuser_upload']==$userUID) {
				if ($deleteFile==1){
						// TOOO error handling
					unlink(PATH_site.$doc['file_path'].$doc['file_name']);
				}
				$table="tx_dam";
				$where="uid=" .$uid;
				$fields_values=array('deleted'=>'1');
				$res = $GLOBALS['TYPO3_DB']->exec_UPDATEquery($table,$where,$fields_values,$no_quote_fields=FALSE);
				return $res ;
			}
			else {
				return false;
			}

	}


		/**
 * if a file is added twice to the system, a new version is genreated
 *
 * @param	int		$docID ID of the file which should be versionized
 * @return	[type]		...
 * @author stefan
 */
		function versioningCreateNewVersionPrepare($docID) {

				// ---- getting the new record
			$newDoc = $this->getDocument($docID);
			$filename = $GLOBALS['TSFE']->fe_user->getKey('ses','uploadFileName');
			$filetype = $newDoc['file_type'];

				// ---- get the latest version id of an existing old file
			$FIELDS = 'MAX(tx_damfrontend_version),uid, tx_damfrontend_version';
			$TABLE = 'tx_dam';
			$GROUPBY = 'tx_damfrontend_version';
			$WHERE = 'file_dl_name = \''.$filename.' \' AND uid != '.$docID . ' AND deleted = 0';
			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery($FIELDS,$TABLE,$WHERE,$GROUPBY);
			while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
				$oldID = $row['uid'];
				$oldversion = $row['tx_damfrontend_version'];
			}
			if ($oldversion == '') $oldversion = 0;
				// store the ID of the old record, so that if can be overwritten in the last step
			$GLOBALS['TSFE']->fe_user->setKey('ses','versioningNewVersionID',$oldID);

				// ---- getting rest of the old record
			$oldDoc = $this->getDocument($oldID);
			$newVersion = $oldversion +1;
				// copying the old data to the new id
				// record with the old file
			$oldDoc['uid']=$docID;
			$oldDoc['tx_damfrontend_version']=$newVersion;
			$oldDoc['file_name']=$newDoc['file_name'];
			$oldDoc['file_path']=$newDoc['file_path'];
			$oldDoc['date_cr']=$newDoc['date_cr'];
			$oldDoc['date_mod']=$newDoc['date_mod'];
			$oldDoc['tstamp']=time();
			$TABLE = 'tx_dam';
			$WHERE = 'uid = '.$docID;
			$GLOBALS['TYPO3_DB']->exec_UPDATEquery($TABLE,$WHERE,$oldDoc);

			return $docID;

		}

		/**
 * if a file is added twice to the system, a new version is genreated
 *
 * @param	int		$docID ID of the file which should be versionized
 * @return	[type]		...
 * @author stefan
 */
		function versioningCreateNewVersionExecute($docID) {
			$newDoc = $this->getDocument($docID);

			$filename = $GLOBALS['TSFE']->fe_user->getKey('ses','uploadFileName');
			$filetype = $newDoc['file_type'];
			$filepath = $newDoc['file_path'];

				// ---- getting the pure filename
			list($purename,$type) = split('\.',$filename);
			$newversion = $newDoc['tx_damfrontend_version'];
			$new_filename = $purename.'_v'.$newversion.'.'.$filetype;

			if (!$this->moveFile(PATH_site.$newDoc['file_path'].$newDoc['file_name'],$GLOBALS['TSFE']->fe_user->getKey('ses','uploadFilePath').$new_filename)) {
				return false;
			}
				//

				//copying the old data to the new id
				// record with the old file
			$oldDoc = $this->getDocument($GLOBALS['TSFE']->fe_user->getKey('ses','versioningNewVersionID'));

			// set the record of the old version to new ID
			$oldID = $oldDoc['uid'];
			$oldDoc['uid']=$docID;
			$TABLE = 'tx_dam';
			$WHERE = 'uid = '.$docID;
			$GLOBALS['TYPO3_DB']->exec_UPDATEquery($TABLE,$WHERE,$oldDoc);

				// 	---- changing the id's of the records and copying the old meta data to the file
				//	record with the new file
			$newDoc['uid'] = $oldID;
			$newDoc['deleted']=0;
			$newDoc['file_name']=$new_filename;
			$newDoc['file_path']=$GLOBALS['TSFE']->fe_user->getKey('ses','uploadFilePath');
			$newDoc['tx_damfrontend_version']= $newversion;
			$newDoc['date_mod']=time();
			$newDoc['crdate']=time();

			$TABLE = 'tx_dam';
			$WHERE = 'uid = '.$oldID;
			$GLOBALS['TYPO3_DB']->exec_UPDATEquery($TABLE,$WHERE,$newDoc);

			// change the categories UID
			$newDoc = array();
			$newDoc[uid_local]='-'.$GLOBALS['TSFE']->fe_user->user['uid'];
			$TABLE = 'tx_dam_mm_cat';
			$WHERE = 'uid_local = '.$oldID;
			$GLOBALS['TYPO3_DB']->exec_UPDATEquery($TABLE,$WHERE,$newDoc);

			// change the categories UID
			$newDoc = array();
			$newDoc[uid_local]=$oldID;
			$TABLE = 'tx_dam_mm_cat';
			$WHERE = 'uid_local = '.$docID;
			$GLOBALS['TYPO3_DB']->exec_UPDATEquery($TABLE,$WHERE,$newDoc);

			// change the categories UID
			$newDoc = array();
			$newDoc[uid_local]=$docID;
			$TABLE = 'tx_dam_mm_cat';
			$WHERE = 'uid_local = -'.$GLOBALS['TSFE']->fe_user->user['uid'];
			$GLOBALS['TYPO3_DB']->exec_UPDATEquery($TABLE,$WHERE,$newDoc);

			return $oldID;

		}

		/**
		 * 	not finished
		 * 	restores the last version of a versionized file
		 * 	@author stefan
		 *	@param int $docID ID of the doc which should be rollbacked
		 */
//		function rollbackVersion($docID) {
//				// ---- getting the current record
//			$currentDoc = $this->getDocument($docID);
//
//				// save the file information of the newly uploaded file
//			$filename = $currentDoc['file_name'];
//			$filetype = $currentDoc['file_type'];
//			$filepath = $currentDoc['file_path'];
//
//			if ($currentDoc['tx_damfrontend_version']==0) {
//				return false; // record is not versionized
//			}
//
//			// ---- get the version id of the old file
//			$FIELDS = 'MAX(tx_damfrontend_version),uid, tx_damfrontend_version';
//			$TABLE = 'tx_dam';
//			$GROUPBY = 'tx_damfrontend_version';
//			$WHERE = 'file_dl_name = \''.$filename.' \' AND uid != '.$docID;
//			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery($FIELDS,$TABLE,$WHERE,$GROUPBY);
//			while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
//				$oldID = $row['uid'];
//				$oldversion = $row['tx_damfrontend_version'];
//			}
//			if ($oldversion == '') {
//				return false; //no versionzid record is found
//			}
//			else {
//					// ---- getting rest of the old record
//				$oldDoc = $this->getDocument($oldID);
//					// restore all old values
//
//					// rename the old filename to the current file to delete filename
//				rename(PATH_site.$filepath.$filename,PATH_site.$filepath.$filename.'_delete');
//
//				rename(PATH_site.$oldDoc['file_path'].$oldDoc['file_name'],PATH_site.$filepath.$filename);
//
//					//copying the old data to the new id
//					// record with the old file
//				$oldDoc['uid']=$docID;
//				$oldDoc['file_name']=$filename;
//				$oldDoc['file_type']=$filetype;
//				$oldDoc['file_path']=$filepath;
//				$TABLE = 'tx_dam';
//				$WHERE = 'uid = '.$oldID;
//				$GLOBALS['TYPO3_DB']->exec_UPDATEquery($TABLE,$WHERE,$oldDoc);
//
//				$currentDoc['uid'] = $oldID;
//				$currentDoc['file_name']=$filename.'_delete';
//				$WHERE = 'uid = '.$docID;
//				$GLOBALS['TYPO3_DB']->exec_UPDATEquery($TABLE,$WHERE,$currentDoc);
//
//				return true;
//			}
//		}

		/**
 * returns the UID of the record which should be overwritten
 *
 * @param	[type]		$docID: ...
 * @return	[type]		...
 * @author stefan
 */
		function versioningOverridePrepare($docID) {

				// getting the new record
			$newDoc = $this->getDocument($docID);

			$filename = $GLOBALS['TSFE']->fe_user->getKey('ses','uploadFileName');
				// getting the old dataset id
			$FIELDS = '*';
			$TABLE = 'tx_dam';
			$WHERE = 'file_name = \''.$filename.' \' AND uid <>'.$docID ;
			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery($FIELDS,$TABLE,$WHERE);
				// TODO insert error handling
			while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
				$oldDoc = $row;
			}

				// store the ID of the old record, so that if can be overwritten in the last step
			$GLOBALS['TSFE']->fe_user->setKey('ses','versioningOverrideID',$oldDoc['uid']);

			$oldDoc['tstamp']=time();
			$oldDoc['file_name']=$newDoc['file_name'];
			$oldDoc['file_path']=$newDoc['file_path'];
			$oldDoc['date_cr']=$newDoc['date_cr'];
			$oldDoc['date_mod']=$newDoc['date_mod'];

				// set deleted to 1, that the new record (not yet saved thru the user) is not shown in the FE
			$oldDoc['deleted']=1;
			$oldDoc['uid']=$docID;
				// copy the old data to the new record
			$TABLE = 'tx_dam';
			$WHERE = 'uid = '.$docID;
				// TODO insert error handling
			$GLOBALS['TYPO3_DB']->exec_UPDATEquery($TABLE,$WHERE,$oldDoc);

			return $docID;
		}

	/**
	 * [Describe function...]
	 *
	 * @param	[type]		$docID: ...
	 * @return	[type]		...
	 */
		function versioningOverrideExecute($docID) {
			if (!$docID >0 ) {
				return false;
			}

			if (!$GLOBALS['TSFE']->fe_user->getKey('ses','versioningOverrideID')>0) {
				return false;
			}
			$newDoc = $this->getDocument($docID);
			// TODO Review
			if (is_file($GLOBALS['TSFE']->fe_user->getKey('ses','uploadFilePath').$GLOBALS['TSFE']->fe_user->getKey('ses','uploadFileName'))) {
				//
				$fileToDelete = $GLOBALS['TSFE']->fe_user->getKey('ses','uploadFilePath').$GLOBALS['TSFE']->fe_user->getKey('ses','uploadFileName');

				// rename it
				rename($fileToDelete, $fileToDelete.'.delete');
			}

			if (!$this->moveFile(PATH_site.$newDoc['file_path'].$newDoc['file_name'],$GLOBALS['TSFE']->fe_user->getKey('ses','uploadFilePath').$GLOBALS['TSFE']->fe_user->getKey('ses','uploadFileName'))) {
				// rename it again
				rename($fileToDelete.'.delete', $fileToDelete);
				return false;
			}
			else {
				unlink($fileToDelete.'.delete');
			}

				// update the new file
			$newDoc['file_name']=$GLOBALS['TSFE']->fe_user->getKey('ses','uploadFileName');
			$newDoc['file_path']=$GLOBALS['TSFE']->fe_user->getKey('ses','uploadFilePath');

				// set deleted to 1, that the new record (not yet saved thru the user) is not shown in the FE
			$newDoc['deleted']=0;
			$newDoc['uid']=$GLOBALS['TSFE']->fe_user->getKey('ses','versioningOverrideID');
			$oldID = $GLOBALS['TSFE']->fe_user->getKey('ses','versioningOverrideID');
			$TABLE = 'tx_dam';
			$WHERE = 'uid = '.$GLOBALS['TSFE']->fe_user->getKey('ses','versioningOverrideID');

				// TODO insert error handling
			$GLOBALS['TYPO3_DB']->exec_UPDATEquery($TABLE,$WHERE,$newDoc);

				// delete the temp dam record (in case of overwrite action, the record is not needed anymore)
			$TABLE = 'tx_dam';
			$WHERE = 'uid = '.$docID;

				// TODO insert error handling
			$GLOBALS['TYPO3_DB']->exec_DELETEquery($TABLE,$WHERE);

			return $oldID;
		}

		/**
		 *
		 * @param	int		$uid: uid of the dam record
		 * @return	[boolean]	true if successful		...
		 * @author stefan
		 */
		function versioningCreateNewRecordExecute($docID) {
				// set new filename
			$newDoc = $this->getDocument($docID);
				// set a new unique filename

			$newFilename= strftime('%Y%m%d_%H%M', time()).'_'.$GLOBALS['TSFE']->fe_user->getKey('ses','uploadFileName');
				// copy the new file from temp dir to destionation dir
			copy(PATH_site.$newDoc['file_path'].$newDoc['file_name'],$GLOBALS['TSFE']->fe_user->getKey('ses','uploadFilePath').$newFilename);
				// delete the temp file
			unlink($newDoc['file_path'].$newDoc['file_name']);
			unset ($newDoc);
				// update the new file
			$newDoc['file_name']=$newFilename;
			$newDoc['file_dl_name']=$newFilename;
			$newDoc['file_path']=$GLOBALS['TSFE']->fe_user->getKey('ses','uploadFilePath');
				// set deleted to 1, that the new record (not yet saved thru the user) is not shown in the FE
			$newDoc['deleted']=0;

			$TABLE = 'tx_dam';
			$WHERE = 'uid = '.$docID;
				// FIXME insert error handling
			$GLOBALS['TYPO3_DB']->exec_UPDATEquery($TABLE,$WHERE,$newDoc);

			return $docID;
		}

		/**
		 * @param	int		$uid: uid of the dam record
		 * @param	int		$catID category which should be deleted
		 * @return	[type]		...
		 * @author stefan
		 */
		 function delete_category ($uid, $catID) {
			if (!intval($uid)) die('Parametererror in delete_category: Check DatabaseID:' . $uid);
			if (!intval($catID)) die('one categoryID was not delivered as Integer');
			$where = ' uid_local=' .$uid .' AND uid_foreign= ' . $catID;
			return $GLOBALS['TYPO3_DB']->exec_DELETEquery($this->mm_Table, $where);
		}

		 /**
		 * Checks if the FE_User has Access to the Document
		 *
		 * @param	string		$docFEGroup -> fe_group the document is restricted to
		 * @return	bool		true if fe_user has access, false if not
		 * @author Stefan Busemann
		 */
		function checkDocumentAccess($docFEGroups) {

			// if no fe group is assigned, access is given (only if if the option showOnlyFilesWithPermission is set to 0
			if (!$docFEGroups) {
				//check if option showOnlyFilesWithPermission = 1 is set, then here must be returned False
				if ($this->conf['filelist.']['security_options.']['showOnlyFilesWithPermission']==1) {
					return false;
				}
				return true;
			}

			$access = false;

				// get all usergroups of the fe_user
			$feuserGroups=$GLOBALS['TSFE']->fe_user->groupData['uid'];
				// if fe_user is not assigned to group return false, because a fe_user has to be at least member of one group
			if (!is_array($feuserGroups)) return false;

			$docFEGroups = explode(',',$docFEGroups);
				// check if at least one fe_group has access to file
			foreach ($feuserGroups as $group ){

				if (array_search($group,$docFEGroups, true)===false) {
					//if the array search founds no value - nothing is to do
					// TODO is there a more elegantly way for this construction? - stefan
				} else {
					$access = true;
				}
			}
			return $access;
		}

	/**
	 * saves an uploaded document in the datastore and cares for versioning
	 *
	 * @param	[type]		$docID: ...
	 * @return	void
	 * @author stefan
	 */
	function storeDocument($docID) {
		// handle versioning
		switch ($GLOBALS['TSFE']->fe_user->getKey('ses','versioning')){
			case 'override':
				$returnID = $this->versioningOverrideExecute($docID);
				break;
			case 'new_version':
				$returnID = $this->versioningCreateNewVersionExecute($docID);
				break;
			default:
			case 'new_record':
				$returnID = $this->versioningCreateNewRecordExecute($docID);
				break;
			default:
					// correct filename & filepath
				$newDoc = $this->getDocument($docID);

					// copy file to the final destination
				$uploadFile = $GLOBALS['TSFE']->fe_user->getKey('ses','uploadFilePath').$GLOBALS['TSFE']->fe_user->getKey('ses','uploadFileName');
				if ($uploadFile<>'') {
					if ($this->moveFile(PATH_site.$newDoc['file_path'].$newDoc['file_name'],$uploadFile)==false) {
						$returnID = false;
						break;
					}

						// set the dam record info the the final state
					$newDoc['deleted']=0;
					$newDoc['file_name']=$GLOBALS['TSFE']->fe_user->getKey('ses','uploadFileName');
					$newDoc['file_path']=$GLOBALS['TSFE']->fe_user->getKey('ses','uploadFilePath');

					$TABLE = 'tx_dam';
					$WHERE = 'uid = '.$docID;
					$GLOBALS['TYPO3_DB']->exec_UPDATEquery($TABLE,$WHERE,$newDoc);
				}
				$returnID = $docID;
				break;
			}

				// clean up
			$GLOBALS['TSFE']->fe_user->setKey('ses','versioningNewVersionID','');
			$GLOBALS['TSFE']->fe_user->setKey('ses','versioning','');
			$GLOBALS['TSFE']->fe_user->setKey('ses','uploadFilePath','');
			$GLOBALS['TSFE']->fe_user->setKey('ses','uploadFileName','');
			return $returnID;
		}

	 /**
	 * returns a searchword transfered to int
	 *
	 * @param	string		$column: colum which should be searched
	 * @param	string		$value: value for which it should be resctricted
	 * @return	string		where clause, ready for adding it to the document array
	 */
	function getCustomWhereString($column, $value) {
		if (trim($value) <>'') $result =' AND '. $this->docTable.'.'.$column.' LIKE "%'.$GLOBALS['TYPO3_DB']->quoteStr(trim($value), $this->docTable).'%" ';
		return $result;
	}

    /**
     * returns a searchword transfered to int
     *
     * @param	string		$column: colum which should be searched
     * @param	int		$value: value for which it should be resctricted (int)
     * @return	string		where clause, ready for adding it to the document array
     */
    function getCustomWhereInt($column, $value) {
        if (intval($value>0)) {
            $value = intval($value);
            return " AND (" .  $this->docTable.'.'.$column. " LIKE '%,".$value.",%' OR " . $this->docTable.'.'.$column . " LIKE '".$value.",%' OR " . $this->docTable.'.'.$column . " LIKE '%,".$value."' OR " . $this->docTable.'.'.$column . "='".$value."')";
        }
    }

	 /**
	 * checks if a user has edit / delete rights
	 *
	 * @param	array		dam record
	 * @return	boolean		true if the user has access
	 */
	function checkEditRights($document) {
		// if the current user is the owner of the document return true
		if ($GLOBALS['TSFE']->fe_user->user['uid']==$document['tx_damfrontend_feuser_upload']) return true;

			// get all usergroups of the fe_user
		$feuserGroups=$GLOBALS['TSFE']->fe_user->groupData['uid'];
			// if fe_user is not assigned to group return false, because a fe_user has to be at least member of one group
		if (!is_array($feuserGroups)) return false;
		$access = FALSE;
			// resolve groups of the document
		$docFEGroups = explode(',',$document['tx_damfrontend_fegroup']);

			// adding groups of the current content elment (flexform)
		if ($this->conf['feEditGroups']) {
			$feEditGroups =  explode(',',$this->conf['feEditGroups']);
		}
		if (is_array($feEditGroups)) $docFEGroups=array_merge($docFEGroups,$feEditGroups);

			// adding groups added via typoscript
		if ($this->conf['filelist.']['fileEdit.']['uids_FEGroups'])	$uids_FEGroups =  explode(',',$this->conf['filelist.']['fileEdit.']['uids_FEGroups']);
		if (is_array($uids_FEGroups)) $docFEGroups=array_merge($docFEGroups,$uids_FEGroups);
		$docFEGroups=array_unique($docFEGroups);
			// check if at least one fe_group has access to file
		foreach ($feuserGroups as $group ){
			if (array_search($group,$docFEGroups, true)===false) {
			}
			else {
					//if the array search founds a value access is allowed
					$access = TRUE;
			}
		}
		return $access;
	}

	 /**
	 * returns a sql where statement, for each searchword
	 *
	 * @param	string		$searchWord: the search phrase a user is looking for e.g. "results 2010"
	 * @return	array		an array with each search word and operator
	 */
	function get_searchWordArray ($searchWord) {
		$operator_translate_table = Array (		// case-sensitive. Defines the words, which will be operators between words
			$this->conf['filterView.']['multipleSearchWords.']['operator_translate_table.']['AND.'],
			$this->conf['filterView.']['multipleSearchWords.']['operator_translate_table.']['OR.'],
			$this->conf['filterView.']['multipleSearchWords.']['operator_translate_table.']['NOT.'],
		);
		$search = t3lib_div::makeInstance('tslib_search');
		$search->default_operator = $this->conf['filterView.']['multipleSearchWords.']['defaultOperator'] ? $this->conf['filterView.']['multipleSearchWords.']['defaultOperator'] : 'OR';
		$search->operator_translate_table = $operator_translate_table;
		$search->register_and_explode_search_string($searchWord);
		return $search->sword_array;
	}

	/**
	 * returns a sql where statement, for looking only for files with permission
	 *
	 * @return	string		String with a SQL Where statement
	 */
	function getOnlyFilesWithPermissionSQL () {
		return " AND NOT ((tx_dam.fe_group='' OR 	tx_dam.fe_group IS NULL	OR 	tx_dam.fe_group='0'
	OR (
			tx_dam.fe_group LIKE '%,0,%' OR tx_dam.fe_group LIKE '0,%'
		OR tx_dam.fe_group LIKE '%,0' OR tx_dam.fe_group='0')
		OR (
				tx_dam.fe_group LIKE '%,-1,%'
			OR tx_dam.fe_group LIKE '-1,%'
			OR tx_dam.fe_group LIKE '%,-1'
			OR tx_dam.fe_group='-1'
			)
		))";
	}

	/**
	 * returns a list of uids, a fe_user has no read access
	 *
	 * @return	string / boolean 	String with uids commaseparated / false in case of no cats
	 */
	function getCategoriesWithNoAccess () {
		$select = 'uid';
		$from	= 'tx_dam_cat';
		$where="  NOT (tx_dam_cat.fe_group='' OR tx_dam_cat.fe_group IS NULL OR tx_dam_cat.fe_group='0')
				 AND NOT (tx_dam_cat.fe_group LIKE '%,-2,%' OR tx_dam_cat.fe_group LIKE '-2,%' OR tx_dam_cat.fe_group LIKE '%,-2' OR tx_dam_cat.fe_group='-2') ";

		if (is_array($this->feuser->user)) {
				// get the user groups of the current user
			$usergroups = $this->feuser->groupData['uid'];
			foreach ($usergroups as $group) {
				$where.=
				" AND NOT
					(	tx_dam_cat.fe_group LIKE '%,".$group.",%'
					OR 	tx_dam_cat.fe_group LIKE '".$group.",%'
					OR 	tx_dam_cat.fe_group LIKE '%,".$group."'
					OR 	tx_dam_cat.fe_group='".$group."'
					)";
			}
		}
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select,$from,$where);
		while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			$result[] = $row['uid'];
		}
		if ($result) {
			return  implode(',',$result);
		}
		else {
			return false;
		}
	}

	/**
	 * returns a sql where statement, for looking only for files with permission
	 *
	 * @return	string		String with a SQL Where statement
	 */
	function getDownloadAccessSQL () {
		$where="(		tx_dam_cat.tx_damtree_fe_groups_downloadaccess=''
				OR 	tx_dam_cat.tx_damtree_fe_groups_downloadaccess IS NULL
				OR 	tx_dam_cat.tx_damtree_fe_groups_downloadaccess='0'
			OR (
					tx_dam_cat.tx_damtree_fe_groups_downloadaccess LIKE '%,0,%'
				OR	tx_dam_cat.tx_damtree_fe_groups_downloadaccess LIKE '0,%'
				OR 	tx_dam_cat.tx_damtree_fe_groups_downloadaccess LIKE '%,0'
				OR	tx_dam_cat.tx_damtree_fe_groups_downloadaccess='0')
			";

		if (!is_array($this->feuser->user)) {
				// no user is logged in
			$where.=
			" OR
				(	tx_dam_cat.tx_damtree_fe_groups_downloadaccess LIKE '%,-1,%'
				OR 	tx_dam_cat.tx_damtree_fe_groups_downloadaccess LIKE '-1,%'
				OR 	tx_dam_cat.tx_damtree_fe_groups_downloadaccess LIKE '%,-1'
				OR 	tx_dam_cat.tx_damtree_fe_groups_downloadaccess='-1'
				)";
		}
		else {
				// get the user groups of the current user
			$usergroups = $this->feuser->groupData['uid'];
			foreach ($usergroups as $group) {
				$where.=
				" OR
					(	tx_dam_cat.tx_damtree_fe_groups_downloadaccess LIKE '%,".$group.",%'
					OR 	tx_dam_cat.tx_damtree_fe_groups_downloadaccess LIKE '".$group.",%'
					OR 	tx_dam_cat.tx_damtree_fe_groups_downloadaccess LIKE '%,".$group."'
					OR 	tx_dam_cat.tx_damtree_fe_groups_downloadaccess='".$group."'
					)";
			}
		}
		return $where.=')';
	}

	/**
	 * moves a file
	 *
	 * @return	boolean	true if success
	 */
	function moveFile ($source, $destination) {
		t3lib_div::upload_copy_move($source,$destination);

		// check if movement was successful
		if (!is_file($destination)) return false;

		// delete the temp file
		unlink($source);

		return true;
	}


}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/dam_frontend/DAL/class.tx_damfrontend_DAL_documents.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/dam_frontend/DAL/class.tx_damfrontend_DAL_documents.php']);
}

?>