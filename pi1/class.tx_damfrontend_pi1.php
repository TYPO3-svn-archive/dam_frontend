<?php

require_once(PATH_tslib.'class.tslib_pibase.php');

// references to the DAL
require_once(t3lib_extMgm::extPath('dam_frontend').'/DAL/class.tx_damfrontend_DAL_documents.php');
require_once(t3lib_extMgm::extPath('dam_frontend').'/DAL/class.tx_damfrontend_DAL_categories.php');
require_once(t3lib_extMgm::extPath('dam_frontend').'/DAL/class.tx_damfrontend_catList.php');
require_once(t3lib_extMgm::extPath('dam_frontend').'/DAL/class.tx_damfrontend_filterState.php');
require_once(t3lib_extMgm::extPath('dam_frontend').'/DAL/class.tx_damfrontend_listState.php');
require_once(t3lib_extMgm::extPath('dam_frontend').'/frontend/class.tx_damfrontend_catTreeView.php');
require_once(t3lib_extMgm::extPath('dam_frontend').'/frontend/class.tx_damfrontend_rendering.php');

/***************************************************************
*  Copyright notice
*
*  (c) 2006-2008 BUS Netzwerk (typo3@in2form.com)
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
 * class.tx_damfrontend_pi1.php
 *
 * Plugin 'DAM Frontend' for the 'dam_frontend' extension.
 *
 * @package typo3
 * @subpackage tx_dam_frontend
 * @author Martin Baum <typo3@in2form.com>
 *
 * @todo add stdWrap functions
 * Some scripts that use this class:	--
 * Depends on:		--
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 *   79: class tx_damfrontend_pi1 extends tslib_pibase
 *  101:     function init()
 *  120:     function initFilter()
 *  156:     function initList()
 *  197:     function initUpload()
 *  206:     function convertPiVars()
 *  247:     function loadFlexForm()
 *  268:     function main($content,$conf)
 *  353:     function catTree()
 *  366:     function getTree($mount= '')
 *  385:     function fileList()
 *  431:     function filterView()
 *  444:     function catSelection()
 *  473:     function singleView()
 *  501:     function filterList()
 *  529:     function uploadForm()
 *
 * TOTAL FUNCTIONS: 15
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */
class tx_damfrontend_pi1 extends tslib_pibase {
	var $prefixId = 'tx_damfrontend_pi1';		// Same as class name
	var $scriptRelPath = 'pi1/class.tx_damfrontend_pi1.php';	// Path to this script relative to the extension dir.
	var $extKey = 'dam_frontend';	// The extension key.
	var $pi_checkCHash = TRUE;


	// references to the DAL
	var $docLogic; // handling of documents
	var $catLogic; // handling of categories

	// references to various helpers
	var $catList; // stores the category selection in the session
	var $renderer; // handeles the frontend rendering
	var $filterState; // stores the current filter state in the session, provides synchronisation with the $internal['filter'] array
	var $listState; // stores the current state of the list

	/**
	 * Inits this class and instanceates all nescessary classes
	 *
	 * @return	[void]		...
	 */
	function init() {
		// instanciate the references to the DAL
		$this->docLogic = t3lib_div::makeInstance('tx_damfrontend_DAL_documents');
		$this->catLogic= t3lib_div::makeInstance('tx_damfrontend_DAL_categories');
		$this->catList = t3lib_div::makeInstance('tx_damfrontend_catList');
		$this->renderer = t3lib_div::makeInstance('tx_damfrontend_rendering');
		$this->filterState = t3lib_div::makeInstance('tx_damfrontend_filterState');
		$this->listState = t3lib_div::makeInstance('tx_damfrontend_listState');
		$this->renderer->setFileRef($this->conf['templateFile']);
		$this->renderer->piVars = $this->piVars;
		$this->renderer->conf = $this->conf;
		$this->renderer->cObj = $this->cObj;
	}

	/**
	 * Receiving filter information from the pluging view "filter_list"
	 *
	 * @return	[void]		...
	 */
	function initFilter() {
		//variables for setting filters for the current category selection
 		$this->internal['filter']['from_day'] = intval(t3lib_div::_GP('von_tag'));
 		$this->internal['filter']['from_month'] =intval(t3lib_div::_GP('von_monat'));
 		$this->internal['filter']['from_year'] = intval(t3lib_div::_GP('von_jahr'));

 		$this->internal['filter']['to_day'] = (int)t3lib_div::_GP('bis_tag');
 		$this->internal['filter']['to_month'] = intval(t3lib_div::_GP('bis_monat'));
 		$this->internal['filter']['to_year'] = intval(t3lib_div::_GP('bis_jahr'));

 		// clear all 0 - values - now they are not shown in the frontend form
 		foreach ($this->internal['filter'] as $key => $value) {
 			if ($value == '0') $this->internal['filter'][$key] = '';
 		}

 		$this->internal['filter']['filetype'] = strip_tags(t3lib_div::_GP('filetype'));
		$this->internal['filter']['searchword'] = strip_tags(t3lib_div::_GP('searchword'));

		if (t3lib_div::_GP('resetFilter')) $this->filterState->resetFilter();
		if (!count($this->filterState->getFilterFromSession())) {
			$emptyArray = $this->internal['filter'];
			foreach ($emptyArray as $key => $value) $emptyArray[$key] = ' ';
			$this->filterState->setFilter($emptyArray);
		}
		if (t3lib_div::_GP('setFilter')) {
			$this->filterState->setFilter($this->internal['filter']);
		}
		$this->internal['filter'] = $this->filterState->getFilterFromSession();
		$this->docLogic->setFilter($this->internal['filter']);
	}

	/**
	 * this function is preparing the listview
	 *
	 * @return	[void]		...
	 */
	function initList() {
		// setting internal values for pagebrowsing from the incoming request
 		if (t3lib_div::_GP('setListLength')) {
			$this->internal['list']['listLength'] = t3lib_div::_GP('listLength') != null ? intval(t3lib_div::_GP('listLength')) : 10;
 		}

		$this->internal['list']['pointer'] =  $this->piVars['pointer'] != null ? intval($this->piVars['pointer']) : 0;
		// setting the internal values for sorting
		foreach ($this->piVars as $postvar => $postvalue) {
 			// clearing SQL Injection
 			if ($postvalue == 'DESC' || $postvalue == 'ASC') {
 				if (substr($postvar, 0, 5) == 'sort_') {
 					$this->internal['list']['sorting'] = strip_tags(substr($postvar, 5).' '.$postvalue);
 				}
 			}

		}

		$this->listState->syncListState($this->internal['list']);

		if (!isset($this->internal['list']['listLength'])) $this->internal['list']['listLength'] = 10;

		/*
		 * if a filter criteria is changed, the pagebrowsing is reseted to the beginning value
		 */
		if (t3lib_div::_GP('setFilter') || !empty($this->internal['catPlus']) ||
				!empty($this->internal['catPlus']) || !empty($this->internal['catMinus']) ||
				!empty($this->internal['catEquals']) || !empty($this->internal['catPlus_Rec']) || !empty($this->internal['catMinus_Rec']))
		{
			$this->internal['list']['pointer'] = 0;
		}
		$this->internal['list']['limit'] = $this->internal['list']['pointer'].','. ($this->internal['list']['listLength']);
		$this->listState->setListState($this->internal['list']);
	}

	/**
	 * this function is providing the fileupload to the ter
	 *
	 * @return	[void]		...
	 * @todo finish this functionality
	 */
	function initUpload() {
		$fileContent = t3lib_div::_GP('fiel');
	}

	/**
	 * reads the incoming piVars from the request and copies them to the internal array
	 *
	 * @return	void
	 */
	function convertPiVars() {
		// variables for category selection
		/*
		$this->internal['catPlus'] = intval($this->piVars['catPlus']);
		$this->internal['catMinus'] = intval($this->piVars['catMinus']);
		$this->internal['catEquals'] = intval($this->piVars['catEquals']);
		*/
		$this->internal['catPlus'] = intval(t3lib_div::_GP('catPlus'));
		$this->internal['catMinus'] = intval(t3lib_div::_GP('catMinus'));
		$this->internal['catEquals'] = intval(t3lib_div::_GP('catEquals'));
		$this->internal['catPlus_Rec'] = intval(t3lib_div::_GP('catPlus_Rec'));
		$this->internal['catMinus_Rec'] = intval(t3lib_div::_GP('catMinus_Rec'));

		// Aufruf der Einzelansicht
		$this->internal['singleID'] = intval($this->piVars['showUid']);

		// loading var for displaying a form for creation of a new filter state
		$this->internal['newFilter'] = strip_tags(t3lib_div::_GP('newFilter'));

		// getting the incoming treeID
		$this->internal['incomingtreeID'] = intval(t3lib_div::_GP('treeID'));

		// Selection Mode
		$this->internal['slectionMode'] = intval($this->piVars['selectionMode']);

		// values for searching
		// Setting new values
 		if ($this->internal['viewID'] == 1  ) {
			$this->initList();
			$this->initFilter();
 		}
		if ($this->internal['viewID'] == 5) {
			$this->initFilter();
		}
 	}

	/**
	 * loads the data from the flexform into the internal array
	 *
	 * @return	void
	 */
	function loadFlexForm() {
		// getting values from flexform
		$this->pi_initPIflexForm();
		$flexform = $this->cObj->data['pi_flexform'];

		$this->internal['viewID'] = intval($this->pi_getFFvalue($flexform, 'viewID'));
//		debug($this->pi_getFFvalue($flexform, 'catMounts', sSelection));
		$this->internal['catMounts'] = explode(',',$this->pi_getFFvalue($flexform, 'catMounts', 'sSelection'));

		$this->internal['treeName'] = strip_tags($this->pi_getFFvalue($flexform, 'treeName', 'sSelection'));
		$this->internal['treeID'] = $this->cObj->data['uid'];
		
		$this->internal['useStaticCatSelection'] = strip_tags($this->pi_getFFvalue($flexform, 'useStaticCatSelection', 'sSelection'));
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
		//check if a fe_user is logged in
		$this->init();
		$this->loadFlexForm();
		$this->convertPiVars();

		$this->filterState->filterTable = 'tx_damfrontend_filterStates';

		// Executing some primary tests on the catgeory logic
		// if ($this->internal['viewID'] == 2) $this->test();

		// Processing and distribution of input data
		// Mapping input parameters to actions
		if ($this->internal['viewID'] == 2 && $this->internal['treeID'] == $this->internal['incomingtreeID'] ) {
			if ($this->internal['catPlus']) {
				// first check, if the user has access to the category
				//if ($this->catLogic->checkCategoryAccess($GLOBALS['TSFE']->fe_user->user['uid'],$this->internal['catPlus'])) {
					$this->catList->op_Plus($this->internal['catPlus'], $this->internal['incomingtreeID']);
				//}
			}
			else if ($this->internal['catMinus']) {
				$this->catList->op_Minus($this->internal['catMinus'], $this->internal['incomingtreeID']);
			}
			else if ($this->internal['catEquals']) {
				$this->catList->op_Equals($this->internal['catEquals'], $this->internal['incomingtreeID']);
			}
			else if ($this->internal['catMinus_Rec']) {
				$subs = $this->catLogic->getSubCategories($this->internal['catMinus_Rec']);
				foreach ($subs as $sub) {
					$this->catList->op_Minus($sub['uid'], $this->internal['incomingtreeID']);
				}
			}
			else if ($this->internal['catPlus_Rec']) {
				$subs = $this->catLogic->getSubCategories($this->internal['catPlus_Rec']);
				foreach ($subs as $sub) {
					$this->catList->op_Plus($sub['uid'], $this->internal['incomingtreeID']);
				}
			}
		}
		if ($this->internal['useStaticCatSelection']) {
			$this->catList->unsetAllCategories();
			if (is_array($this->internal['catMounts'])) {
				foreach ($this->internal['catMounts'] as $catMount) {
					if (strlen($catMount)) {
						$subs = $this->catLogic->getSubCategories($catMount);
						foreach ($subs as $sub) {
							$this->catList->op_Plus($sub['uid'], $this->internal['incomingtreeID']);
						}
					}
				}
			}
		}
		//debug($this->catList->getCatSelection());

		// Mapping the ViewIds - selected in the Flexform to the content
		// that shall be rendered
		//debug($this->internal['viewID']);
		switch ($this->internal['viewID']) {
			case 1:
				$content = $this->fileList();
				break;
			case 2:
				$content = $this->catTree();
				break;
			case 3:
				$content = $this->catSelection();
				break;
			case 4:
				$content = $this->singleView();
				break;
			case 5:
				$content = $this->filterView();
				break;
			case 6:
				$content = $this->filterList();
				break;
			case 7:
				$content = $this->uploadForm();
			default:
				$content = 'no view selected - nothing is displayed';
				break;
		}
		// select the view to be created
		return $this->pi_wrapInBaseClass($content);
	}


	/**
	 * renderes an category tree
	 *
	 * @return	html		category tree - ready for display
	 */
	function catTree() {
		##### Adding a treeview to the output
		$tree = t3lib_div::makeInstance('tx_damfrontend_catTreeView');
		$tree->init($this->internal['treeID']);
		$tree->title = $this->internal['treeName'];
		$selCats  = $this->catList->getCatSelection();
		$tree->selectedCats = $selCats[$this->internal['treeID']];
		if (is_array($this->internal['catMounts'])) $tree->MOUNTS = $this->internal['catMounts'];
		return  '<div class="cattree" >'.$tree->getBrowsableTree().'</div>';
	}


	/*
	function getTree($mount= '') {

		$tree = t3lib_div::makeInstance('tx_damfrontend_catTreeView');
		$tree->init();
		$tree->selectedCats = $this->catList->getCatSelection();

		if ($mount != '') $tree->MOUNTS;
	}
	*/


	/**
	 * fist this function gets the list of selected categories from the session vars
	 * after that it retrievs a list of all documents from the doclogic
	 * all documents are contained, which nat filtered and the user has
	 * After that, the list is converted to an html view, rendered by the renderer instance
	 *
	 * @return	html		HTML - list of all selected documents
	 */
	function fileList() {
		$cats = $this->catList->getCatSelection();
		$hasCats = false; // true if any category has been selected yet
		if (count($cats)) {
			foreach($cats as $catList) {
				if (count($catList)) $hasCats = true;
			}
		}

		if ($hasCats ) {
			/***************************
			 *
			 *    search and sorting values are transfered to the user
			 *
			 ***************************/

			if (is_array($this->internal['filter'])) {
				$this->internal['filterError'] = $this->docLogic->setFilter($this->internal['filter']);
			}
			$this->docLogic->orderBy = $this->internal['list']['sorting'];
			$this->docLogic->limit = $this->internal['list']['limit'];
			$this->docLogic->categories = $cats;
			$this->docLogic->selectionMode = $this->internal['selectionMode'];
			$files = $this->docLogic->getDocumentList();
			if (is_array($files)) {
				//get the html from the renderer
				$rescount = $this->docLogic->resultCount;
				$content = $this->renderer->renderFileList($files, $rescount, $this->internal['list']['pointer'], $this->internal['list']['listLength']);
			}
			else {
				// render error
				$content = $this->renderer->renderError('noDocInCat');
			}
		}
		else {
			//render error
			$content = $this->renderer->renderError('noCatSelected');
		}
		return $content;
	}

	/**
	 * Renders the filter view
	 *
	 * @return	[string]		$content html auf filterview
	 */
	function filterView() {
		$content = $this->renderer->renderFilterView($this->internal['filter'], $this->internal['filterError']);
		return $content;
	}


	/**
	 * retrieves all selected catID's from the session and creates a list of category - records
	 * by this list. After that, this list is given to the frontend - renderer to render a list of all
	 * selected categories
	 *
	 * @return	html		rendered list, ready for display, of selected categories
	 */
	function catSelection() {
		// getting the complete category selection
		$selection = $this->catList->getCatSelection();
		if (is_array($selection)) {
			foreach($selection as $cat) {
				$catList[] = $this->catLogic->getCategory($cat[0]);
			}
			if (is_array($catList))
			{
				$content = $this->renderer->renderCatSelection($catList);
			}
			else {
				$content = $this->renderer->renderError('noCatSelected');
			}

		}
		else {
			$content = $this->renderer->renderError('noCatSelected');
		}
		return $content;
	}


	/**
	 * controller function dor the rendering of the single view
	 * the internal singleID must be set
	 *
	 * @return	html		rendered content
	 */
	function singleView() {
		$singleID = intval($this->internal['singleID']);
		if ($this->docLogic->checkAccess($singleID, 1)) {
			if (intval($singleID) && $singleID != 0) {
				$record = $this->docLogic->getDocument($singleID);
				$content = $this->renderer->renderSingleView($record);

				if ($this->docLogic->checkAccess($singleID, 2)) {
					$_SESSION['fileRef'] = $record['file_path'].$record['file_name'];
				}
				return $content;
			}
			else {
				return $this->renderer->renderError('noSingleID');
			}
		}
		else {
			return $this->renderer->renderError('noDocAccess');
		}

	}


	/**
	 * function is responsible for rendering of a list with all availible filters
	 *
	 * @return	string		html list of all availible filters
	 */
	function filterList() {
		if (is_array($GLOBALS['TSFE']->fe_user->user)) {
			$filterList = $this->filterState->getFilterList($GLOBALS['TSFE']->fe_user->user['uid']);
			if (is_array($filterList) && count($filterList)) {
				$content =$this->renderer->renderFilterList($filterList);
			}
			else {
				$content = $this->renderer->renderError('noFilterStates');
			}

			if ($this->internal['newFilter'] != '') {
				$content .= $this->renderer->renderFilterCreationForm();
			}
			else {
				$content .= $this->renderer->renderFilterCreationLink();
			}
		}
		else {
			$content = $this->renderer->renderError('noUserLoggedIn');
		}
		return $content;
	}

	/**
	 * Shows the upload form
	 *
	 * @return	[string]		$content the html code of the form
	 */
	function uploadForm() {
		$content = $this->renderer->renderUploadForm();
		return $content;
	}

}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/dam_frontend/pi1/class.tx_damfrontend_pi1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/dam_frontend/pi1/class.tx_damfrontend_pi1.php']);
}

?>