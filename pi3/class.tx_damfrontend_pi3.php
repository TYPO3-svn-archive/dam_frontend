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
require_once('class.tx_damfrontend_basketCaseRendering.php');

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
	var $scriptRelPath = 'pi3/class.tx_damfrontend_pi3.php';	// Path to this script relative to the extension dir.
	var $extKey = 'dam_frontend';	// The extension key.
	var $pi_checkCHash = TRUE;
	var $basketCase;
	var $errors; // array with error messages
	var $renderer;
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
	       $conf = t3lib_div::array_merge($conf,$extConf);
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
	  
		$this->basketCase = new tx_damfrontend_basketCase;
		$this->renderer = new tx_damfrontend_basketCaseRendering;
		$this->renderer->cObj = $this->cObj;
		$this->renderer->conf = $this->conf;
		#$this->basketCase = new tx_damfrontend_basketCase;
	}

	

	/**
	 * reads the incoming piVars from the request and copies them to the internal array
	 *
	 * @return	void
	 */
	function convertPiVars() {
		if (intval($this->piVars['add'])) {
			$this->basketCase->addItem(intval($this->piVars['add']));
		}
		if (intval($this->piVars['delete'])) {
			$this->basketCase->deleteItem(intval($this->piVars['delete']));
		}

		if ($this->piVars['usage']) {
			$this->data['usage']=strip_tags($this->piVars['usage']);
		}
		if ($this->piVars['accept']) {
			$this->data['accept']=strip_tags($this->piVars['accept']);
		}
		
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
		if (!$GLOBALS['TSFE']->fe_user->user['uid']) return $this->pi_wrapInBaseClass($this->renderer->renderError($this->pi_getLL('noUser')));
		
		switch ($this->conf['viewID']) {
			case 1:
				$content .= $this->basketCase_Checkout();
				// mini basket case
				break;
			case 2:
				$content .=	$this->basketCase_Preview();	
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
		
		if ($this->checkOutPossible()) {
				if ($this->doCheckOut()) {
					$content = $this->renderer->renderCheckOutResult($this->basketCase->listItems());
					$this->basketCase->clearBasketcase();
					return $content;
				}
				else {
					return $this->renderer->renderError($this->errors);
				}
		}
		else {
				// if the user has not clicked "submit", then show no "missing" warning, so we need to delete the error array
			if (!$this->piVars['submit']) unset($this->errors);
			return $this->renderer->renderCheckOutForm($this->basketCase->listItems(),$this->errors,$this->data);
		}
	}
	
	
	/**
	 * Prepares the basketCase_Preview View
	 *
	 * @param	array		$conf: The PlugIn configuration
	 * 
	 * @return	string		The html content that is displayed on the website
	 */
	function basketCase_Preview() {
		return $this->renderer->renderPreview($this->basketCase->listItems());
	}
	
	function doCheckOut(){
			if (!$this->basketCase->writeUsage($this->data)) {
				$this->errors['noUsage']=$this->pi_getLL('noUsage');
				return false;
			}
			
			// create mail
			if ($this->conf['sendMailAfterCheckout']==1) {
				$this->sendMail($this->basketCase->listItems());
			}
		return true;
	}
	
	/**
	 * Inits this class and instanceates all nescessary classes
	 *
	 * @return	[void]		...
	 */
	function sendMail($items) {
		
		require_once(PATH_t3lib.'class.t3lib_htmlmail.php');
		// get FE_USER data
		if (!$GLOBALS['TSFE']->fe_user->user['email']) {
			if ($this->conf['showMailWarning']==1) {
				return $this->renderer->renderError($this->pi_getLL('noMailAdress'));
			}
		}
		else {
			$recipient_email = $GLOBALS['TSFE']->fe_user->user['email'];
		}
		
		
		if (!$this->conf['mail.']['from']) $maildata['from']='norepley@'.t3lib_div::getIndpEnv('HTTP_HOST')	;
		($this->conf['mail.']['subject']) ? $maildata['subject']= $this->cObj->cObjGetSingle($this->conf['mail.']['subject'],$this->conf['mail.']['subject.']) :$maildata['subject']='Your downloads' ;
		$maildata['htmlbody'] = $this->renderer->renderMail($this->basketCase->listItems());
		$htmlMail = t3lib_div::makeInstance('t3lib_htmlmail');
		$htmlMail->start();
		$htmlMail->recipient =$recipient_email;
		$htmlMail->subject =$maildata['subject'];
		$htmlMail->from_email =$maildata['from'];
		#$htmlMail->addPlain(strip_tags($maildata['body']));
		$htmlMail->setHTML($htmlMail->encodeMsg($maildata['htmlbody']));
		return $htmlMail->send();
	}
	
	function checkOutPossible() {
		if ($this->piVars['accept']=='accept' AND $this->piVars['usage']<>'') {
			return true;
		}
		else {
			if (!$this->piVars['accept']) 		$this->errors['notAccepted']=1;
			if (empty($this->piVars['usage'])) 	$this->errors['usageMissing']=1;
			return false;
		} 
	}
	
}




if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/dam_frontend/pi1/class.tx_damfrontend_pi1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/dam_frontend/pi1/class.tx_damfrontend_pi1.php']);
}

?>