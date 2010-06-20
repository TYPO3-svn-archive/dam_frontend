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
class tx_damfrontend_pi3 extends tslib_pibase {
	var $prefixId = 'tx_damfrontend_pi3';		// Same as class name
	var $scriptRelPath = 'pi1/class.tx_damfrontend_pi3.php';	// Path to this script relative to the extension dir.
	var $extKey = 'dam_frontend';	// The extension key.
	var $pi_checkCHash = TRUE;


	/**
	 * Inits this class and instanceates all nescessary classes
	 *
	 * @param	[type]		$conf: ...
	 * @return	[void]		...
	 */
	function init($conf) {
	    
			// Init FlexForm configuration for plugin
		$this->pi_initPIflexForm(); 

	    	// Read extension configuration
	    $extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][$this->extKey]);
	    if (is_array($extConf)) {
	       $conf = t3lib_div::array_merge($extConf, $conf);
	    }

	    	// Read TYPO3_CONF_VARS configuration
	    $varsConf = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'][$this->extKey];
	      if (is_array($varsConf)) {
	       $conf = t3lib_div::array_merge($varsConf, $conf);
	    }
	    
	          // Read FlexForm configuration
	    if ($this->cObj->data['pi_flexform']['data']) {
	          foreach ($this->cObj->data['pi_flexform']['data'] as $sheetName => $sheet) {
	               foreach ($sheet as $langName => $lang) {
	                   foreach(array_keys($lang) as $key) {
	                  $flexFormConf[$key] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], $key, $sheetName, $langName);
	                  if (!$flexFormConf[$key]) {
	                     unset($flexFormConf[$key]);
	                  }
	               }
	            }
	         }
	      }
	      if (is_array($flexFormConf)) {
	         $conf = t3lib_div::array_merge($conf, $flexFormConf);
	    }
	    foreach ($conf as $key=>$data ) {
	        if (substr($key,-1)=='.') {
	            $this->conf[substr($key,0,-1)] = $this->cObj->stdWrap($conf[substr($key,0,-1)],$conf[$key]);
	        }
	        elseif (!isset($conf[$key.'.'])) {
	                $this->conf[$key] = $conf[$key];
	        }
	  }

	      // getting values from flexform ==> it's possible to overwrite flexform values with ts settings
	  $flexform = $this->cObj->data['pi_flexform'];

	}

	

	/**
	 * reads the incoming piVars from the request and copies them to the internal array
	 *
	 * @return	void
	 */
	function convertPiVars() {

	
 	}

	/**
	 * The main method of the PlugIn
	 *
	 * @param	string		$content: The PlugIn content
	 * @param	array		$conf: The PlugIn configuration
	 * @return	string		The html content that is displayed on the website
	 */
	function main($content,$conf)	{
		$this->conf=$conf;
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();
			// initialilisation and convertion of input paramters
			// reading parameters from different sources
		$this->init($conf);
		$this->convertPiVars();

		switch ($this->internal['viewID']) {
			case 1:
					// standard filelist
				break;
			case 2:
				$content .= $this->catTree();
				break;
			default:
				$content .= 'no view selected - nothing is displayed';
				break;
		}
		// select the view to be created
		return $this->pi_wrapInBaseClass($content);
	}


	/**
	 * Prepares the basketCase_Preview View
	 *
	 * @param	array		$conf: The PlugIn configuration
	 * 
	 * @return	string		The html content that is displayed on the website
	 */
	function basketCase_Checkout() {
		;
	}
	
	
	/**
	 * Prepares the basketCase_Preview View
	 *
	 * @param	array		$conf: The PlugIn configuration
	 * 
	 * @return	string		The html content that is displayed on the website
	 */
	function basketCase_Preview() {
		;
	}
}




if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/dam_frontend/pi1/class.tx_damfrontend_pi1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/dam_frontend/pi1/class.tx_damfrontend_pi1.php']);
}

?>