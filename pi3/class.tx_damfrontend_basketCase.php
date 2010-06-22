<?php

/***************************************************************
*  Copyright notice
*
*  (c) 2006-2010 in2form.com (typo3@in2form.com)
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
require_once(t3lib_extMgm::extPath('dam_frontend').'DAL/class.tx_damfrontend_DAL_documents.php');
#require_once('../DAL/class.tx_damfrontend_DAL_documents.php');
require_once(t3lib_extMgm::extPath('dam_frontend').'/DAL/class.tx_damfrontend_baseSessionData.php');


/**
 *
 * class.tx_damfrontend_pi3.php
 *
 * Plugin 'DAM frontend basketcase' for the 'dam_frontend' extension.
 *
 * @package typo3
 * @subpackage tx_dam_frontend
 * @author Stefan Busemann <typo3@in2form.com>
 *
 * Depends on:		--
 */
class tx_damfrontend_basketCase extends tx_damfrontend_baseSessionData  {

	var $usageDescription;
	var $items;
	var $documents;
	var $conf;
	
	function tx_damfrontend_basketCase() {
		parent::tx_damfrontend_baseSessionData();
		$this->sessionVar = 'tx_damfrontend_basketCase';
		$this->items = $this->getArrayFromUser();
		$this->documents = new tx_damfrontend_DAL_documents;
		$this->documents->conf = $GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_damfrontend_pi1.']; 
	}
	

	/**
	 * Inits this class and instanceates all nescessary classes
	 *
	 * @param	[int]		$id: ID of the dam_record that should be added
	 * @return	[void]		...
	 */
	function addItem($id) {
		//check if user is allowed to add
		$this->items[]=$id;
		$this->setArrayToUser(array_unique($this->items)); 
		return true;
	}

	/**
	 * Inits this class and instanceates all nescessary classes
	 *
	 * @param	[int]		$id: ID of the dam_record that should be added
	 * @return	[void]		...
	 */
	function deleteItem($id) {
		$key = array_search($id, $this->items);
		if ($key===false) {
			
		}
		else {
			unset($this->items[$key]);
			$this->setArrayToUser(array_unique($this->items));
		}
		return true;
	}
	
	
	/**
	 * Inits this class and instanceates all nescessary classes
	 *
	 * @return	[mixes]		boolean in case of sucess
	 */
	function listItems() {
		$result = array();
		if (!empty($this->items)) {
			$this->documents->additionalFilter = ' AND tx_dam.uid in ('. implode(',',$this->items).')';
			$this->documents->limit = '0,999999';
			$result = $this->documents->getDocumentList($GLOBALS['TSFE']->fe_user->user['uid']);
		}
		return $result;
	}
	
	/**
	 * Inits this class and instanceates all nescessary classes
	 *
	 * @return	[void]		...
	 */
	function writeUsage() {
		
		return true;
	}

	
	/**
	 * Inits this class and instanceates all nescessary classes
	 *
	 * @return	[void]		...
	 */
	function clearBasketcase() {
		unset($this->items);
		$this->setArrayToUser($this->items);
		return true;
	}
	
	
	
}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/dam_frontend/pi1/class.tx_damfrontend_pi1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/dam_frontend/pi1/class.tx_damfrontend_pi1.php']);
}

?>