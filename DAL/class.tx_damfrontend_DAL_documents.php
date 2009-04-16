<?php
require_once(t3lib_extMgm::extPath('dam_frontend').'/DAL/class.tx_damfrontend_DAL_categories.php');
require_once(t3lib_extMgm::extPath('dam').'/lib/class.tx_dam_indexing.php');

/***************************************************************
*  Copyright notice
*
*  (c) 2006-2008 in2form.com (typo3@in2form.com)
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
 *   69: class tx_damfrontend_DAL_documents
 *  100:     function tx_damfrontend_DAL_documents()
 *  111:     function getCategoriesbyDoc($docID)
 *  136:     function checkAccess_fileRef($filePath)
 *  158:     function checkAccess($docID, $relID)
 *  183:     function getResultCount()
 *  207:     function createCatString()
 *  221:     function getDocumentFEGroups($docID, $relID)
 *  261:     function getDocument($docID)
 *  280:     function getDocumentList()
 *  365:     function getCategoriesByDoc_Rootline($docID)
 *  411:     function setFilter($filterArray)
 *  454:     function getSearchwordWhereString($searchword)
 *  466:     function evalDateError($day, $month, $year)
 *  484:     function addDocument($path, $docData='')
 *  535:     function categoriseDocument($uid, $catArray)
 *
 * TOTAL FUNCTIONS: 15
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */
	class tx_damfrontend_DAL_documents {
		var $fileTypeList;				// Contains a list of filetypes, the selection is restricted to
		var $uidList;					// Array which contains all selected Files
		var $catList;
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

		var $relations = array(
			'1' => 'readaccess',
			'2' => 'downloadaccess',
			'3' => 'uploadaccess'
		);

		var $feuser;			// pointing to the fe user object instance - please use this instead of GLOBALS['TSFE]

		/**
		 * Sets fullTextSearchFields - in which fields should be searched
		 *
		 * @param string $fieldlist kommaseparated list of fields (f.e. 'title,
		 * description')
		 *
		 * @return void
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
	 * includes also parent categories of the assigned categories
	 *
	 * @param	int		$fileUid: ...
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
	 * This function is retrieving the uid in the dam by resolving name and path and sending the uid to the function  checkAcess
	 *
	 * @param	string		$filePath: ...
	 * @return	boolean		true if access is allowed
	 * @see checkAccess
	 */
	 //TODO this function is not used? delete it? - Stefan
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

// TODO implement a function which combines checkAccess and checkDokumentAcess  - Stefan
	/**
	 * checks, if the current user has access to an specific document
	 * if the document has no category, the access is not limited
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
			if (!is_array($docgroups)) return true; // no groups assigned - allow access
			// get the ID's of the usergroups, the current user is a member of
			$usergroups = $this->feuser->groupData['uid'];
			$valid = true;
			foreach($docgroups as $docgroup) {
				$valid = $valid && array_search($docgroup['uid'], $usergroups);
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
			return 10;
		}

	/**
	 * This function is currently not used and empty
	 *
	 * @return	void		...
	 * @todo: check if we can delete it
	 */
		function createCatString() {

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
			// first find all categoies for the given document
			$catlist = $this->getCategoriesByDoc_Rootline($docID);
			// accumulates all groups
			$grouparray = array();
			foreach ($catlist as $category)
			{
				$mm_table = 'tx_dam_cat_'.$this->relations[$relID].'_mm';
				// executing database search
				$local_table = $this->catTable;
				$foreign_table = 'fe_groups';
				$where = 'AND '.$local_table.'.uid = '.$category['uid'];
				$select = $foreign_table.'.*';
				$res = $GLOBALS['TYPO3_DB']->exec_SELECT_mm_query($select,$local_table, $mm_table, $foreign_table, $where);

				// adding groups from the database to the GroupArray - check if group is already in list
				while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))
				{
					if (!is_array($this->catLogic->findUidinList($grouparray, $row['uid'])))
					{
						$grouparray[] = $row;
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
			return $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
		}





	/**
	 * generates a list of all availible documents. Used by the frontend. The
	 * selection is filterd by the given list of categories and the
	 * access restrictions  -> relation "READ ACCESS" defined for the document
	 *
	 * @return	[array]		returns an array which contains all selected records
	 */
		function getDocumentList($userUID=0) {
			if(!is_array($this->categories)) {
				if (TYPO3_DLOG) t3lib_div::devLog('parameter error in function getDcoumentList: for the this->categories is no array. Given value was:' .$this->categories, 'dam_frontend',3);
			}
				// TODO: is there a reason not to use API: Enablefields?
			$filter = ' AND '.$this->docTable.'.deleted = 0  AND '.$this->docTable.'.hidden = 0';
			$filter .= ' AND ('.$this->docTable.'.starttime > '.time().' OR '.$this->docTable.'.starttime = 0)';
			$filter .= ' AND ('.$this->docTable.'.endtime < '.time().' OR '.$this->docTable.'.endtime = 0)';
			
			if ($this->conf['useLatestList']==true) {
					// query without using categories and restricting via latest date.
					
					// get the date of the last record, that the list can be limited via a where condition, then is the list later sortable from the fe_user
					// otherwise, if the fe_user would sort by date asc not the last x files would be shown, but the oldest one
				
				/**$select= $this->conf['latestField'];
				$from=$this->docTable;
				$where.= ' 1=1  '.$filter;				
			
				$select=$this->conf['latestField'];
				$filter .= $this->additionalFilter;
		
				$from=$this->docTable;
				$where.= ' 1=1  '.$filter;**/				
			}
			else {
				
				if (count($this->categories)) {

					/*
					 * Building the from clause manually by joining the DAM tables
					 */
				$select = $this->docTable.'.uid';
				$from = $this->docTable.' INNER JOIN '.$this->mm_Table.' ON '.$this->mm_Table.'.uid_local  = '.$this->docTable.
				'.uid INNER JOIN '.$this->catTable.' ON '.$this->mm_Table.'.uid_foreign = '.$this->catTable.'.uid';

					$filter .= $this->additionalFilter;


					// preparing the category array - deleting all empty entries
					// TODO: rethinking if it is a good idea to change $this->categories in a function for reading entrys?
				foreach($this->categories as $number => $catList) {
					if (!count($catList)) {
						unset($this->categories[$number]);
					}
				}

				$queryText = array();
				$z = 0;
				/**
				 * every element in the categories array stores a list of cats that are associated with an array
				 *
				 *
				 *
				 */
				foreach($this->categories as $number => $catList) {
							
						if ($this->searchAllCats === true) {
							$catString = "1=1";
						}
						else {
							$catString = '( '.$this->catTable.'.uid='.implode(' OR '.$this->catTable.'.uid=',$catList).')';
						}
	
						if ($z != count($this->categories)-1) {
							if (!count($queryText)) {
								$queryText[] = $GLOBALS['TYPO3_DB']->SELECTquery($select,$from, $catString);
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
						$z++;
					}
				} 
				else {
					// query without using categories
				$filter .= $this->additionalFilter;
				$select='*';
				$from='tx_dam';
					$where.= ' 1=1 '.$filter;
				}
			}
			// TODO: is there a reason not to define SELECT here? 
			// TODO: do not use '*' but whitlist defined via TypoScript
			$select = ' DISTINCT '.$this->docTable.'.*';

			$resultCounter=0;
				// executing the final query and convert the results into an array
				// is defnied as: $this->internal['list']['limit'] = $this->internal['list']['pointer'].','. ($this->internal['list']['listLength']);
			list($pointer, $listLength) = explode (',',$this->limit);
			$startRecord = $pointer * $listLength;

				// limit = "pointer,counter"
			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery($select, $from, $where,'',$this->orderBy);
			$result = array();
			while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
				if ($this->checkAccess($row['uid'], 1) && $this->checkDocumentAccess($row['fe_group'])) {
						//add a delete information
					if ($userUID == $row['tx_damfrontend_feuser_upload'] AND $userUID>0){
						$row['allowDeletion']=1;
						$row['allowEdit']=1;
					}
					$row['tx_damfrontend_feuser_upload']= $this->get_FEUserName($row['tx_damfrontend_feuser_upload']);

						// TODO: we should use SQL-LIMIT instead! Cant we create an SQL-Syntax for $this->checkAccess($row['uid'], 1) && $this->checkDocumentAccess($row['fe_group']) ??
						// add row only, if the current resultID is between the limit range
					if ($resultCounter >=$startRecord && $resultCounter<=($startRecord+$listLength-1)){
						$result[] = $row;
					}
						// pointer starts at "0" so the first result counter has to be 0 too
					$resultCounter++;
				}
			}

			$this->resultCount = $resultCounter;
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
				if (TYPO3_DLOG) t3lib_div::devLog('parameter error in function setFilter: filterArray must be an array. Given value was:' .$this->categories, 'dam_frontend',3);
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
			if ($filterArray['filetype'] != '' && $filterArray['filetype'] != ' ') $this->additionalFilter .= ' AND '.$this->docTable.'.file_type = \''.$filterArray['filetype'].'\'' ;
			if ($filterArray['searchword'] != '' && $filterArray['searchword'] != ' ') $this->additionalFilter .= $this->getSearchwordWhereString($filterArray['searchword']);
			if ($filterArray['creator'] != '' && $filterArray['creator'] != ' ') $this->additionalFilter .= $this->getSearchwordWhereString($filterArray['creator'],'creator');
			# todo: check access (user must be part of the selected usergroup)
			if ($filterArray['owner'] > 0 ) $this->additionalFilter .=   ' AND '.$this->docTable.'.tx_damfrontend_feuser_upload  ='.$filterArray['owner'];

			if (trim($filterArray['LanguageSelector']) != '' && $filterArray['LanguageSelector'] != 'nosel') $this->additionalFilter .=  ' AND '.$this->docTable.'.language = "'.trim($filterArray['LanguageSelector']).'"';

			if ($filterArray['showOnlyFilesWithPermission'] == 1) $this->additionalFilter .=  ' AND '.$this->docTable.'.fe_group <>"" AND '.$this->docTable.'.fe_group <>"-1" AND '.$this->docTable.'.fe_group <>"-2" AND '.$this->docTable.'.fe_group <>"0"';
			return $errors;
		}


	/**
	 * returns a searchword transfered to int
	 *
	 * @param	string		$searchword: blank separated string
	 * @return	string		where clause, ready for adding it to the document array
	 */
		function getSearchwordWhereString($searchword) {
			$searchFields = t3lib_div::trimExplode(',', $this->fullTextSearchFields, true);
			if (0 == count($searchFields)) { return ''; }
			$queryPart = array();
			foreach ($searchFields as $field) {
				$queryPart[] = ' '.$this->docTable.'.'.$field.' LIKE "%'.$GLOBALS['TYPO3_DB']->quoteStr(trim($searchword), $this->docTable).'%" ';
			}
			return ' AND ('.implode(' OR ', $queryPart).') ';
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



		function saveMetaData($docID, $docData) {
			foreach( $docData as $key => $value ) {
				$DATA[$key] = $value;
			}
			$TABLE = 'tx_dam';
			$WHERE = 'uid = '.$docID;
			$GLOBALS['TYPO3_DB']->exec_UPDATEquery($TABLE,$WHERE,$DATA);
		}


		/**
		 * adding an document to the index
		 *
		 * @param	[string]	$path: path where file is stored
		 * @param	[array]		$docData: array of the document data
		 * @return	[int]		$newID: new UID of the dam_record
		 * @todo get the pid for the indexer = media folder
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
			// FIXME check if all fields exist, because a user can enter his own fields (mapping of fe_user data to dam records)
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
			}
	 		// FIXME function should return true if success
		}

		/**
		 *
		 * @author stefan
		 * @param int $uid UID of the dam entry, which should be created
		 * @return boolean true, if the deletion was sucessful
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

		function get_FEUserName ($uid=0) {

			if ($uid >0) {
				$SELECT = '*';
				$FROM = 'fe_users';
				$WHERE = 'uid = ' . intval($uid);
				$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery($SELECT, $FROM, $WHERE);
				while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
					if ($row['name']=='') {
						$content =$row['username'];
					}
					else {
						$content =$row['name'];
					}
				}
			}
			return $content;
		}


		/**
		 *	 if a file is added twice to the system, a new version is genreated
		 * 	@author stefan
		 *	@param int $docID ID of the file which should be versionized
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
			$oldDoc['tstamp']=time();
			$TABLE = 'tx_dam';
			$WHERE = 'uid = '.$docID;
			$GLOBALS['TYPO3_DB']->exec_UPDATEquery($TABLE,$WHERE,$oldDoc);

			return $docID;

		}

		/**
		 * if a file is added twice to the system, a new version is genreated
		 * @author stefan
		 *	@param int $docID ID of the file which should be versionized
		 */
		function versioningCreateNewVersionExecute($docID) {
			$newDoc = $this->getDocument($docID);
			
				// FIXME access check	
				// FIXME insert error handling
			
			$filename = $GLOBALS['TSFE']->fe_user->getKey('ses','uploadFileName');
			$filetype = $newDoc['file_type'];
			$filepath = $newDoc['file_path'];

				// ---- getting the pure filename
			list($purename,$type) = split('\.',$filename);
			$newversion = $newDoc['tx_damfrontend_version'];
			$new_filename = $purename.'_v'.$newversion.'.'.$filetype;
				// copy the new file from temp dir to destionation dir with new version number
			copy(PATH_site.$newDoc['file_path'].$newDoc['file_name'],$GLOBALS['TSFE']->fe_user->getKey('ses','uploadFilePath').$new_filename);
				// delete the temp file
			unlink($newDoc['file_path'].$newDoc['file_name']);
			
				//copying the old data to the new id
				// record with the old file
			$oldDoc = $this->getDocument($GLOBALS['TSFE']->fe_user->getKey('ses','versioningNewVersionID'));

			// set the record of the old version to new ID 
			$oldID = $oldDoc['uid'];
			$oldDoc['uid']=$docID;
			$TABLE = 'tx_dam';
			$WHERE = 'uid = '.$docID;
			$GLOBALS['TYPO3_DB']->exec_UPDATEquery($TABLE,$WHERE,$oldDoc);

				// ---- changing the id's of the records and copying the old meta data to the file
				//record with the new file
			$newDoc['uid'] = $oldID;
			$newDoc['deleted']=0;
			$newDoc['file_name']=$new_filename;
			$newDoc['file_path']=$GLOBALS['TSFE']->fe_user->getKey('ses','uploadFilePath');
			$newDoc['tx_damfrontend_version']= $newversion;
			$newDoc['date_mod']=time();
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
			
			$GLOBALS['TSFE']->fe_user->setKey('ses','versioningNewVersionID','');
			$GLOBALS['TSFE']->fe_user->setKey('ses','versioning','');
			$GLOBALS['TSFE']->fe_user->setKey('ses','uploadFilePath','');
			$GLOBALS['TSFE']->fe_user->setKey('ses','uploadFileName','');	
			
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
		 * @author stefan
		 *
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
				// FIXME insert error handling
			while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
				$oldDoc = $row;
			}
				
				// store the ID of the old record, so that if can be overwritten in the last step
			$GLOBALS['TSFE']->fe_user->setKey('ses','versioningOverrideID',$oldDoc['uid']);
						
			$oldDoc['date_mod']=time();
			$oldDoc['file_name']=$newDoc['file_name'];
			$oldDoc['file_path']=$newDoc['file_path'];
				// set deleted to 1, that the new record (not yet saved thru the user) is not shown in the FE
			$oldDoc['deleted']=1;
			$oldDoc['uid']=$docID;
				// copy the old data to the new record
			$TABLE = 'tx_dam';
			$WHERE = 'uid = '.$docID;
				// FIXME insert error handling
			$GLOBALS['TYPO3_DB']->exec_UPDATEquery($TABLE,$WHERE,$oldDoc);
						
			return $docID;
		}
		
		function versioningOverrideExecute($docID) {
			
			$newDoc = $this->getDocument($docID);
			
				// FIXME access check	
				// FIXME the old file
			unlink($GLOBALS['TSFE']->fe_user->getKey('ses','uploadFilePath').$GLOBALS['TSFE']->fe_user->getKey('ses','uploadFileName'));
				// FIXME insert error handling
				
				// copy the new file from temp dir to destionation dir
			copy(PATH_site.$newDoc['file_path'].$newDoc['file_name'],$GLOBALS['TSFE']->fe_user->getKey('ses','uploadFilePath').$GLOBALS['TSFE']->fe_user->getKey('ses','uploadFileName'));
				// delete the temp file
			unlink($newDoc['file_path'].$newDoc['file_name']);
			
				// update the new file
			$newDoc['file_name']=$GLOBALS['TSFE']->fe_user->getKey('ses','uploadFileName');
			$newDoc['file_path']=$GLOBALS['TSFE']->fe_user->getKey('ses','uploadFilePath');
				// set deleted to 1, that the new record (not yet saved thru the user) is not shown in the FE
			$newDoc['deleted']=0;
			$newDoc['uid']=$GLOBALS['TSFE']->fe_user->getKey('ses','versioningOverrideID');
			
			$TABLE = 'tx_dam';
			$WHERE = 'uid = '.$GLOBALS['TSFE']->fe_user->getKey('ses','versioningOverrideID');
				// FIXME insert error handling
			$GLOBALS['TYPO3_DB']->exec_UPDATEquery($TABLE,$WHERE,$newDoc);

				// delete the temp dam record (in case of overwrite action, the record is not needed anymore)
			$TABLE = 'tx_dam';
			$WHERE = 'uid = '.$docID;
				// FIXME insert error handling
			$GLOBALS['TYPO3_DB']->exec_DELETEquery($TABLE,$WHERE);
			
			$GLOBALS['TSFE']->fe_user->setKey('ses','versioningNewVersionID','');
			$GLOBALS['TSFE']->fe_user->setKey('ses','versioning','');
			$GLOBALS['TSFE']->fe_user->setKey('ses','uploadFilePath','');
			$GLOBALS['TSFE']->fe_user->setKey('ses','uploadFileName','');	
			
			return 1;
		}
		
		/**
		 * 	@author stefan
		 *	@param int $uid: uid of the dam record
		 *	@param int $catID category which should be deleted
		 *
		 */
		 function delete_category ($uid, $catID) {
			if (!intval($uid)) die('Parametererror in delete_category: Check DatabaseID:' . $uid);
			if (!intval($catID)) die('one categoryID was not delivered as Integer');
			$where = ' uid_local=' .$uid .' AND uid_foreign= ' . $catID;
			return $GLOBALS['TYPO3_DB']->exec_DELETEquery($this->mm_Table, $where);
		}

	 /**
	 * Checks if the FE_User has Access to the Document
	 * @author Stefan Busemann
	 * @param	string		$docFEGroup -> fe_group the document is restricted to
	 * @return	bool	true if fe_user has access, false if not
	 */
		function checkDocumentAccess($docFEGroups) {
			
			// if no fe group is asigned, access is given
			if (!$docFEGroups) {
				return true;
				//FIXME must check if option showOnlyFilesWithPermission = 1 is set, then here must be returned False 
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
	
		function storeDocument($docID) {
		
			// handle versioning
			switch ($GLOBALS['TSFE']->fe_user->getKey('ses','versioning')){
				case 'override':
					$this->versioningOverrideExecute($docID);
					break;
				case 'new_version':
					$this->versioningCreateNewVersionExecute($docID);
					break;
				default:
						
						// correct filename & filepath
					$newDoc = $this->getDocument($docID);
					
						// copy file to the final destination
					$uploadFile = $GLOBALS['TSFE']->fe_user->getKey('ses','uploadFilePath').$GLOBALS['TSFE']->fe_user->getKey('ses','uploadFileName');
					
						// FIXME insert error handling
					copy(PATH_site.$newDoc['file_path'].$newDoc['file_name'],$uploadFile);
						// delete the temp file
					unlink($newDoc['file_path'].$newDoc['file_name']);
		
						// set the dam record info the the final state	
					$newDoc['deleted']=0;
					$newDoc['file_name']=$GLOBALS['TSFE']->fe_user->getKey('ses','uploadFileName');
					$newDoc['file_path']=$GLOBALS['TSFE']->fe_user->getKey('ses','uploadFilePath');

					$GLOBALS['TSFE']->fe_user->setKey('ses','versioningNewVersionID','');
					$GLOBALS['TSFE']->fe_user->setKey('ses','versioning','');
					$GLOBALS['TSFE']->fe_user->setKey('ses','uploadFilePath','');
					$GLOBALS['TSFE']->fe_user->setKey('ses','uploadFileName','');	
										
					$TABLE = 'tx_dam';
					$WHERE = 'uid = '.$docID;
					$GLOBALS['TYPO3_DB']->exec_UPDATEquery($TABLE,$WHERE,$newDoc);
					break;	
			}
		}
	}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/dam_frontend/DAL/class.tx_damfrontend_DAL_documents.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/dam_frontend/DAL/class.tx_damfrontend_DAL_documents.php']);
}

?>