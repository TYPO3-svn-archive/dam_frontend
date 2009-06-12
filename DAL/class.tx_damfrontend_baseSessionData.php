<?php
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
 * class.tx_dam_frontend_baseSessionData.php
 *
 * This class is a Baseclass for Providers, which store Data in the Session
 * in PHP 5, this methods would be protected
 *
 * @package typo3
 * @subpackage tx_dam_frontend
 * @author Martin Baum <typo3@in2form.com>
 *
 * Some scripts that use this class:	class.tx_damfrontend_catList.php
 * Depends on:		--
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 *   56: class tx_damfrontend_baseSessionData
 *   60:     function tx_damfrontend_baseSessionData()
 *   69:     function getArrayFromUser()
 *   80:     function setArrayToUser($array)
 *   90:     function unSetArrayToUser($array)
 *
 * TOTAL FUNCTIONS: 4
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */
class tx_damfrontend_baseSessionData {
	var $user; // reference to the frontend user
	var $sessionVar; //name of the session variable, where user data is sored at

	function tx_damfrontend_baseSessionData() {
		$this->user =& $GLOBALS['TSFE']->fe_user;
	}

	/**
	 * returns the data that hos been stored by the key
	 *
	 * @return	void
	 */
	function getArrayFromUser() {
		$sesarray = $this->user->getKey('ses', $this->sessionVar);
		if (is_array($sesarray)) return $sesarray;
	}

	/**
	 * sets an array by the given var
	 *
	 * @param	array		$array: array whith values in any form you want to
	 * @return	void
	 */
	function setArrayToUser($array) {
		$this->user->setKey('ses', $this->sessionVar, $array);
	}

	/**
	 * [Describe function...]
	 *
	 * @param	[type]		$array: ...
	 * @return	[type]		...
	 */
	function unSetArrayToUser($array) {
		$this->user->unSetKey('ses');
	}
}
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/dam_frontend/DAL/class.tx_damfrontend_baseSessionData.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/dam_frontend/DAL/class.tx_damfrontend_baseSessionData.php']);
}

?>