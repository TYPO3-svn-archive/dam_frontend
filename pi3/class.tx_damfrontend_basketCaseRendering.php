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
require_once(t3lib_extMgm::extPath('dam_frontend').'frontend/class.tx_damfrontend_rendering.php');
require_once('class.tx_damfrontend_basketCase.php');
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
	var $scriptRelPath = 'pi3/class.tx_damfrontend_basketCaseRendering.php';	// Path to this script relative to the extension dir.
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
	function renderCheckOutForm($items, $errors = array(), $data= array()) {
		$this->pi_loadLL();
		$htmlTemplate = tsLib_CObj::getSubpart(tsLib_CObj::fileResource($this->conf['templateFile']),'###BASKET_CASE###');
		$markerArray= array();	 
		if (empty($items)) {
			return $this->renderError($this->pi_getLL('error_no_items'));
		} 
		else {
				// text how many items are in the basket
			count($items)==1 ? $markerArray['###ITEMS###']='1 ' . $this->pi_getLL('item'):$markerArray['###ITEMS###']=count($items). ' ' .$this->pi_getLL('items');;
			$markerArray['###ITEMSTEXT###']=$this->pi_getLL('itemstext');
			
				// usage text area
			$markerArray['###LABEL_USAGE###']=$this->pi_getLL('label_usage');
			if ($errors['usageMissing']) $markerArray['###LABEL_USAGE###']=$this->cObj->stdWrap($this->pi_getLL('missing_data'),$this->conf['marker.']['missingData.'])  . $markerArray['###LABEL_USAGE###'];
			$markerArray['###USAGE###']= $data['usage'];
			
				// checkbox accept usage conditions
			$markerArray['###LABEL_ACCEPT###']=$this->pi_getLL('label_accept');
			if ($errors['notAccepted']) $markerArray['###LABEL_ACCEPT###']=$this->cObj->stdWrap($this->pi_getLL('missing_data'),$this->conf['marker.']['missingData.'])  . $markerArray['###LABEL_ACCEPT###'];
			$data['accept']=='accept' ? $markerArray['###CHECKED###']='checked':$markerArray['###CHECKED###']=''; 
			
			$markerArray['###LABEL_SUBMIT###']=$this->pi_getLL('label_submit');
			$markerArray['###TARGET###']= $this->cObj->typolink('', $this->conf['marker.']['CheckOutFormTarget.']);
			
				// render details of the basket
			$damRendering = new tx_damfrontend_rendering;
			$damRendering->conf = $GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_damfrontend_pi1.'];
	 		$countElement = 0;
			$rows = '';
			$cObj = t3lib_div::makeInstance('tslib_cObj');
	 		$record_Code = tsLib_CObj::getSubpart(tsLib_CObj::fileResource($this->conf['templateFile']),'###FILELIST_RECORD###');
	 		// overwrite the ts setting for the filelist
	 		$damRendering->conf['filelist.'] = $GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_damfrontend_pi1.'];
	 		foreach ($items as $item) {
	 			$countElement++;
	 			$cObj->start($item, 'tx_dam');
	 			$markerArray['###DELETE_FROM_BASKET###']= $cObj->cObjGetSingle($this->conf['marker.']['delete_from_basket'], $this->conf['marker.']['delete_from_basket.']);
	 			$tsConf = $this->conf['marker.']['thumb.'];
	 			$tsConf['file'] = $item['file_path'].$item['file_name'];
	 			$tsConf['params.']['width'] = '50m';
	 			$tsConf['params.']['heigth'] = '50m';
	 			$markerArray['###THUMB###']=$cObj->cObjGetSingle($this->conf['marker.']['thumb'], $tsConf);
	 			$rows .= $damRendering->renderDamRecordRow($item,$countElement,0,9999,'filelist',$record_Code,$cObj,$markerArray );
	 		}
	 		$markerArray['###RECORDS###']=$rows;
		}
		
			//Debug statements
		if ($this->conf['enableDebug']==1) {
			if ($this->conf['debug.']['renderCheckOutForm.']['markerArray']==1)		t3lib_div::debug($markerArray);
			if ($this->conf['debug.']['renderCheckOutForm.']['conf']==1)			t3lib_div::debug($this->conf);
			if ($this->conf['debug.']['renderCheckOutForm.']['items']==1)			t3lib_div::debug($items);
		}
		
		return tslib_cObj::substituteMarkerArray($htmlTemplate, $markerArray);
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
	function renderError($message) {
		$htmlTemplate = tsLib_CObj::getSubpart(tsLib_CObj::fileResource($this->conf['templateFile']),'###ERROR###');
		$markerArray['###MESSAGE###']= $message;
		return  tslib_cObj::substituteMarkerArray($htmlTemplate, $markerArray);
	}

	
	/**
	 * Inits this class and instanceates all nescessary classes
	 *
	 * @param	[type]		$conf: ...
	 * @return	[void]		...
	 */
	function renderCheckOutResult($items) {
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
	 * Using the dam_frontend_rending hook to add the basket case button 
	 * 
	 */
	function render_dam_record($markerArray,$plugin, $elem){
		$cObj = t3lib_div::makeInstance('tslib_cObj');
		$cObj->start($elem, 'tx_dam');
		$markerArray['###ADD_TO_BASKET###']= $cObj->cObjGetSingle($this->conf['marker.']['add_to_basket'], $this->conf['marker.']['add_to_basket.']);
	}
	
	function renderPreview($items) {
		$this->pi_loadLL();
		$htmlTemplate = tsLib_CObj::getSubpart(tsLib_CObj::fileResource($this->conf['templateFile']),'###BASKET_CASE_PREVIEW###');
		$markerArray= array();	 
		if (count($items)==0) {
			$markerArray['###ITEMS###']='';
			$markerArray['###ITEMSTEXT###']=$this->pi_getLL('no_itemstext');
			$markerArray['###CHECKOUT###']='';
		} 
		else {
			count($items)==1 ? $markerArray['###ITEMS###']='1 ' . $this->pi_getLL('item'):$markerArray['###ITEMS###']=count($items). ' ' .$this->pi_getLL('items');;
			$markerArray['###ITEMSTEXT### ']=$this->pi_getLL('itemstext');
			$markerArray['###CHECKOUT###']=$this->cObj->cObjGetSingle($this->conf['marker.']['checkout'], $this->conf['marker.']['checkout.']);
		}
		
		//Debug statements
		if ($this->conf['enableDebug']==1) {
			if ($this->conf['debug.']['render_dam_record.']['markerArray']==1)	t3lib_div::debug($markerArray);
			if ($this->conf['debug.']['render_dam_record.']['conf']==1)			t3lib_div::debug($this->conf);
			if ($this->conf['debug.']['render_dam_record.']['items']==1)			t3lib_div::debug($items);
		}
			
		return tslib_cObj::substituteMarkerArray($htmlTemplate, $markerArray);
	}
}




if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/dam_frontend/pi1/class.tx_damfrontend_pi1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/dam_frontend/pi1/class.tx_damfrontend_pi1.php']);
}

?>