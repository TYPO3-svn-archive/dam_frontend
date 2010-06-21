<?php

require_once(PATH_tslib.'class.tslib_pibase.php');


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
#require_once(t3lib_extMgm::extPath('dam_frontend').'pi3/class.tx_damfrontend_basketCase.php');
require_once('class.tx_damfrontend_basketCase.php');

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
class tx_damfrontend_basketCaseRendering extends tslib_pibase {
	var $prefixId = 'tx_damfrontend_basketCaseRendering';		// Same as class name
	var $scriptRelPath = 'pi1/class.tx_damfrontend_basketCaseRendering.php';	// Path to this script relative to the extension dir.
	var $extKey = 'dam_frontend';	// The extension key.
	
	function tx_damfrontend_basketCaseRendering() {
		$this->conf = $GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_damfrontend_pi3.'];
		$this->pi_loadLL();
	}
	/**
	 * Inits this class and instanceates all nescessary classes
	 *
	 * @param	[type]		$conf: ...
	 * @return	[void]		...
	 */
	function renderCheckOutForm() {
		return 'renderCheckOutForm';
	}

	/**
	 * Inits this class and instanceates all nescessary classes
	 *
	 * @param	[type]		$conf: ...
	 * @return	[void]		...
	 */
	function renderMail() {
		return 'renderMail';
	}

	
	/**
	 * Inits this class and instanceates all nescessary classes
	 *
	 * @param	[type]		$conf: ...
	 * @return	[void]		...
	 */
	function renderError() {
		return 'renderError';
	}

	
	/**
	 * Inits this class and instanceates all nescessary classes
	 *
	 * @param	[type]		$conf: ...
	 * @return	[void]		...
	 */
	function renderCheckOutResult() {
		return 'renderCheckOutResult';
	}
	
	/** 
	 * Hook 
	 * 
	 */
	function renderSingleView($markerArray,$plugin, $elem) {
		// call the render_dam_record because it does the same we need in the singleView
		$this->render_dam_record(&$markerArray,$plugin, $elem);
	}

	/** 
	 * Hook 
	 * 
	 */
	function render_dam_record($markerArray,$plugin, $elem){
		$cObj = t3lib_div::makeInstance('tslib_cObj');
		$cObj->start($elem, 'tx_dam');
		$markerArray['###ADD_TO_BASKET###']= $cObj->cObjGetSingle($this->conf['marker.']['add_to_basket'], $this->conf['marker.']['add_to_basket.']);
	}
	
	function renderPreview($items) {
		return 'basketCase Preview';
	}
}




if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/dam_frontend/pi1/class.tx_damfrontend_pi1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/dam_frontend/pi1/class.tx_damfrontend_pi1.php']);
}

?>