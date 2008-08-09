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
 * class.tx_damfrontend_listState.php
 *
 * class is responsible for storing the state of the list view in the session
 *
 * @package typo3
 * @subpackage tx_dam_frontend
 * @author Martin Baum <typo3@in2form.com>
 *
 * Some scripts that use this class:	--
 * Depends on:		--
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 *   57: class tx_damfrontend_listState extends tx_damfrontend_baseSessionData
 *   64:     function tx_damfrontend_listState()
 *   75:     function resetListState()
 *   89:     function setListState($listArray)
 *   98:     function getListState()
 *  108:     function syncListState(&$listState)
 *
 * TOTAL FUNCTIONS: 5
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */
class tx_damfrontend_listState extends tx_damfrontend_baseSessionData{

	/**
	 * inits this class by loading the session data of the fe_user
	 *
	 * @return	[void]		...
	 */
	function tx_damfrontend_listState() {
		parent::tx_damfrontend_baseSessionData();
		$this->user = $GLOBALS['TSFE']->fe_user;
		$this->sessionVar = 'tx_damfrontend_listState';
	}

	/**
	 * clears the list status by deleting the relevant session data
	 *
	 * @return	[void]		...
	 */
	function resetListState() {
		$arr = $this->getListState();
		foreach($arr as $key => $value) {
			$arr[$key] = '';
		}
		$this->setListState($arr);
	}

	/**
	 * sets the list state by prossing a given listarray
	 *
	 * @param	[array]		$listArray: ...
	 * @return	[void]		...
	 */
	function setListState($listArray) {
		$this->setArrayToUser($listArray);
	}

	/**
	 * loads the listarray
	 *
	 * @return	[array]		...
	 */
	function getListState() {
		return $this->getArrayFromUser();
	}

	/**
	 * Synchronzises the liststate in the usersession with the internal liststate
	 *
	 * @param	[array]		$listState: ...
	 * @return	[void]		...
	 */
	function syncListState(&$listState) {
		if (!is_array($listState)) die('internalFilter in tx_damfrontend_listState.syncFilterValues must be an array!');
		$sessionData = $this->getArrayFromUser();
		foreach($listState as $key => $value) {
			$sessionData[$key] = $value;
		}
		foreach($sessionData as $key => $value) {
			if (!isset($listState[$key])) {
				$listState[$key] = $sessionData[$key];
			}
		}
		$this->setArrayToUser($sessionData);
	}
}
?>
