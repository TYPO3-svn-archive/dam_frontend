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
class tx_damfrontend_basketCase extends tslib_pibase {
	var $prefixId = 'tx_damfrontend_basketCase';		// Same as class name
	var $scriptRelPath = 'pi1/class.tx_damfrontend_basketCase.php';	// Path to this script relative to the extension dir.
	var $extKey = 'dam_frontend';	// The extension key.
	var $pi_checkCHash = TRUE;

	var $usageDescription;

	/**
	 * Inits this class and instanceates all nescessary classes
	 *
	 * @param	[type]		$conf: ...
	 * @return	[void]		...
	 */
	function init() {
	    
		

	}

	/**
	 * Inits this class and instanceates all nescessary classes
	 *
	 * @param	[int]		$id: ID of the dam_record that should be added
	 * @return	[void]		...
	 */
	function addItem($id) {
		return true;
	}

	/**
	 * Inits this class and instanceates all nescessary classes
	 *
	 * @param	[int]		$id: ID of the dam_record that should be added
	 * @return	[void]		...
	 */
	function deleteItem($id) {
		return true;
	}
	
	/**
	 * Inits this class and instanceates all nescessary classes
	 *
	 * @return	[void]		...
	 */
	function listItems() {
		return true;
	}
	
	/**
	 * Inits this class and instanceates all nescessary classes
	 *
	 * @return	[void]		...
	 */
	function checkOut() {
		return true;
	}
	
	/**
	 * Inits this class and instanceates all nescessary classes
	 *
	 * @return	[void]		...
	 */
	function writeUsage() {
		return true;
	}
}




if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/dam_frontend/pi1/class.tx_damfrontend_pi1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/dam_frontend/pi1/class.tx_damfrontend_pi1.php']);
}

?>