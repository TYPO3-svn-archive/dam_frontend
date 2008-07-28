<?php
require_once(t3lib_extMgm::extPath('dam_frontend').'/DAL/class.tx_damfrontend_baseSessionData.php');

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
 * class.tx_damfrontend_filterState.php
 *
 * class is responsible for storing the state of the filter in the session
 *
 * @package typo3
 * @subpackage tx_dam_frontend
 * @author Martin Baum <typo3@in2form.com>
 * @todo finish the class
 *
 * Some scripts that use this class:	--
 * Depends on:		--
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 *   63: class tx_damfrontend_filterState extends tx_damfrontend_baseSessionData
 *   74:     function tx_damfrontend_filterState()
 *   86:     function setFilter($filterArray)
 *   98:     function syncFilterValues(&$internalFilter)
 *  117:     function resetFilter()
 *  128:     function getFilterFromSession()
 *  140:     function persistFilterState($name, $description, $uid)
 *  154:     function getFilterList($userID)
 *  174:     function loadFilter($filterID)
 *  185:     function deleteFilter($filterID)
 *
 * TOTAL FUNCTIONS: 9
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */
class tx_damfrontend_filterState extends tx_damfrontend_baseSessionData{

	var $fe_user_table;
	var $mm_table;
	var $filterTable;

	/**
	 * sets the filter to the current array
	 *
	 * @return	[type]		...
	 */
	function tx_damfrontend_filterState() {
		parent::tx_damfrontend_baseSessionData();
		$this->user = $GLOBALS['TSFE']->fe_user;
		$this->sessionVar = 'tx_damfrontend_filterState';
	}

	/**
	 * sets the filter to the current array
	 *
	 * @param	[$filterArray]		$filterArray: ...
	 * @return	[void]		...
	 */
	function setFilter($filterArray) {
		$this->setArrayToUser($filterArray);
	}

	/**
	 * if a value in the parameter array doesn't exist, the value is taken from the
	 * session data
	 * if the value in the given array exists, its overwritten in the session
	 *
	 * @param	array		$internalFilter: array to sync. Mostly the internal['filter'] vars
	 * @return	void
	 */
	function syncFilterValues(&$internalFilter) {
		if (!is_array($internalFilter)) die('internalFilter in class.tx_damfrontend_filterState.php:syncFilterValues must be an array!');
		$sessionData = $this->getArrayFromUser();
		foreach($internalFilter as $key => $value) {
			if (isset($sessionData[$key]) && (!isset($value) || $value == '') ) {
				$internalFilter[$key] = $sessionData[$key];
			}
			else {
				$sessionData[$key] = $value;
			}
		}
		$this->setArrayToUser($sessionData);
	}

	/**
	 * [Resets the filter settings]
	 *
	 * @return	[void]		...
	 */
	function resetFilter() {
		$arr = $this->getArrayFromUser();
		foreach ($arr as $key=>$value) $arr[$key] = '';
		$this->setArrayToUser($arr);
	}

	/**
	 * Loads the settings out of the session
	 *
	 * @return	void		...
	 */
	function getFilterFromSession() {
		return $this->getArrayFromUser();
	}

	/**
	 * saves the status of the filter to the database
	 *
	 * @param	[string]		$name: ...
	 * @param	[string]		$description: ...
	 * @param	[int]		$uid: ...
	 * @return	void
	 */
	function persistFilterState($name, $description, $uid) {

		// getting values to insert into the database
		$values = $this->getArrayFromUser();
		$values['fe_user'] = $this->user->user['uid'];
		$GLOBALS['TYPO3_DB']->exec_INSERTquery();
	}

	/**
	 * loads the filter from the database
	 *
	 * @param	[int]		$userID: ...
	 * @return	[void]		...
	 */
	function getFilterList($userID) {
	if (!intval($userID)) die('Parameter error in class.tx_damfrontend_filterState.php:getFilterList: userID is no integer value');
		$fields = '*';
		$table = $this->filterTable;
		$WHERE = 'fe_user='.$userID;
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery($fields, $table, $WHERE);
		// convert resultpointer to array
		while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			$resultlist[] = $row;
		}
		return $resultlist;
	}

	/**
	 * function loads the filter with the given ID from the database into the session
	 *
	 * @param	int		$filterID: uid of the filter to load into the session
	 * @return	void
	 * @todo function loadFilter is not finished yet
	 */
	function loadFilter($filterID) {

	}

	/**
	 * deletes a filter with the given uid from the server
	 *
	 * @param	int		$filterID: deletes the given uid from the filterState table
	 * @return	void
	 * @todo function deleteFilter is not finished yet
	 */
	function deleteFilter($filterID) {

	}
}
?>