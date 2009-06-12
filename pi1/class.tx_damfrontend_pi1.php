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
*  (c) 2006-2009 in2form.com (typo3@in2form.com)
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
 * @author Martin Baum, Stefan Busemann, Martin Holtz <typo3@in2form.com>
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
 *  104: class tx_damfrontend_pi1 extends tslib_pibase
 *  139:     function init()
 *  167:     function initFilter()
 *  235:     function initList()
 *  311:     function initUpload()
 *  320:     function convertPiVars()
 *  411:     function loadFlexForm()
 *  439:     function main($content,$conf)
 *  513:     function getInputTree()
 *  594:     function catTree()
 *  646:     function fileListBasicFuncionality ()
 *  771:     function fileList($useRequestForm)
 *  842:     function filterView()
 *  858:     function catSelection()
 *  888:     function singleView()
 *  917:     function filterList()
 *
 *              SECTION: FILE UPLOAD AND CATEGORISATION
 *  956:     function uploadForm()
 * 1098:     function dropDown()
 * 1133:     function isSubNodeOf($arr,$node,$subnode)
 * 1156:     function getSubNodesCount($arr,$parent)
 * 1172:     function getSubNodes($arr,$parent)
 * 1193:     function getSubNodesOptions($arr,$parent,$selVal=-1)
 * 1211:     function versioningForm()
 * 1225:     function handleUpload()
 * 1400:     function loadFileUploadTS()
 * 1416:     function getIncomingDocData()
 * 1454:     function categoriseForm($docData='')
 * 1515:     function saveCategories($docID,$upload=true)
 * 1551:     function get_FEUserList ($fe_group=0,$currentUser ='')
 * 1593:     function myFiles ()
 * 1638:     function handleVersioning($code)
 * 1661:     function editForm ($uid=0)
 * 1699:     function overRide2()
 * 1715:     function overRide()
 * 1744:     function getTreeMap()
 * 1806:     function getTree($mount= '')
 * 1819:     function testCase ()
 * 1829:     function saveMetaData ($saveUID)
 *
 * TOTAL FUNCTIONS: 37
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

	var $formData; // stores the data of the Anforderungsform
	var $userLoggedIn; // determines if an user is logged in or not
	var $userUID; //UID of the User which is logged in
	var $upload; // determines, if an incoming upload shall be handeled
	var $uploadedFile; // contains the file, which should be uploaded
	var $documentData; // contains the incoming data from the user
	var $categorise; // determines, if the categorisation view shall be shown
	var $saveCategorisation; // true if the categorisation selection of an uploaded document shall be saved
	var $saveMetaData; //true if the meta data of an uploaded document shall be saved

	var $versionate; // true if a file is already present and the versioning option shall be shown

	var $pid; //Page ID
	/**
	 * Inits this class and instanceates all nescessary classes
	 *
	 * @return	[void]		...
	 */
	function init() {
		// instanciate the references to the DAL
		$this->docLogic = t3lib_div::makeInstance('tx_damfrontend_DAL_documents');
		$this->docLogic->setFullTextSearchFields($this->conf['filterView.']['searchwordFields']);
		$this->docLogic->conf = $this->conf;
		$this->catLogic= t3lib_div::makeInstance('tx_damfrontend_DAL_categories');
		$this->catList = t3lib_div::makeInstance('tx_damfrontend_catList');
		$this->renderer = t3lib_div::makeInstance('tx_damfrontend_rendering');
		$this->renderer->setFileRef($this->conf['templateFile']);
		$this->renderer->piVars = $this->piVars;
		$this->renderer->conf = $this->conf;
		$this->renderer->cObj = $this->cObj;
		$this->renderer->init();

		$this->filterState = t3lib_div::makeInstance('tx_damfrontend_filterState');

		$this->listState = t3lib_div::makeInstance('tx_damfrontend_listState');

		$this->pid = $this->cObj->data['pid'];
		$this->versioning = strip_tags(t3lib_div::_GP('version_method'));
		$this->docLogic->setFullTextSearchFields($this->conf['filterView.']['searchwordFields']);
	}

	/**
	 * Receiving filter information from the pluging view "filter_list"
	 *
	 * @return	[void]		...
	 */
	function initFilter() {
		if (t3lib_div::_GP('resetFilter')){
			$this->filterState->resetFilter();
		}

		//variables for setting filters for the current category selection
 		$this->internal['filter']['from_day'] = intval(t3lib_div::_GP('von_tag'));
 		$this->internal['filter']['from_month'] =intval(t3lib_div::_GP('von_monat'));
 		$this->internal['filter']['from_year'] = intval(t3lib_div::_GP('von_jahr'));

 		$this->internal['filter']['to_day'] = (int)t3lib_div::_GP('bis_tag');
 		$this->internal['filter']['to_month'] = intval(t3lib_div::_GP('bis_monat'));
 		$this->internal['filter']['to_year'] = intval(t3lib_div::_GP('bis_jahr'));

 		// clear all 0 - values - now they are not shown in the frontend form
 		foreach ($this->internal['filter'] as $key => $value) {
 			if ($value == '0') {
 				$this->internal['filter'][$key] = '';
 			}
 		}

 		$this->internal['filter']['filetype'] = strip_tags(t3lib_div::_GP('filetype'));
		$this->internal['filter']['searchword'] = strip_tags(t3lib_div::_GP('searchword'));


		// if all categories should be searched
		if (t3lib_div::_GP('dam_fe_allCats')=='true') {
			$this->internal['filter']['searchAllCats'] = true;
		}
		else {
			$this->internal['filter']['searchAllCats'] = false;
		}



		$this->internal['filter']['LanguageSelector'] = strip_tags(t3lib_div::_GP('LanguageSelector'));
		$this->internal['filter']['creator'] = strip_tags(t3lib_div::_GP('creator'));
		$this->internal['filter']['owner'] = strip_tags(t3lib_div::_GP('owner'));



		if (!count($this->filterState->getFilterFromSession())) {
			$emptyArray = $this->internal['filter'];
			foreach ($emptyArray as $key => $value) $emptyArray[$key] = ' ';
			$this->filterState->setFilter($emptyArray);
		}
		if (t3lib_div::_GP('setFilter')) {
			$this->filterState->setFilter($this->internal['filter']);
		}
		$this->internal['filter'] = $this->filterState->getFilterFromSession();
		$this->internal['filter']['listOfOwners']=$this->get_FEUserList($this->conf['FilterUserGroup'],$this->internal['filter']['owner']);


		# This filter must set regardless the filter is resetet, because this setting is independ of the normal filters or filter view
		if ($this->conf['filelist.']['security_options.']['showOnlyFilesWithPermission']==1) {
			$this->internal['filter']['showOnlyFilesWithPermission']=1;
		} else {
			$this->internal['filter']['showOnlyFilesWithPermission']=0;
		}

		$this->docLogic->setFilter($this->internal['filter']);
	}

	/**
	 * this function is preparing the listview
	 *
	 * @return	[void]		...
	 */
	function initList() {

		// setting internal values for pagebrowsing from the incoming request

		$this->internal['list']['pointer'] =  $this->piVars['pointer'] != null ? intval($this->piVars['pointer']) :0;

 		if (t3lib_div::_GP('setListLength')) {
			$this->internal['list']['listLength'] = t3lib_div::_GP('listLength') != null ? intval(t3lib_div::_GP('listLength')) : 10;
			$listLengthArr = array();
			$listLengthArr['listLength'.$this->cObj->data['uid']]=$this->internal['list']['listLength'];
			$this->listState->setArrayToUser($listLengthArr);
 		} else {
 			$listLengthArr = $this->listState->getArrayFromUser();
 			$this->internal['list']['listLength']=$listLengthArr['listLength'.$this->cObj->data['uid']];
 		}

 		if (!isset($this->internal['list']['listLength'])) {
			if ($this->conf['filelist.']['defaultLength']) {
				$this->internal['list']['listLength'] = $this->conf['filelist.']['defaultLength'];
			}
			else {
				$this->internal['list']['listLength'] = 10;
			}
		}
		else {
			if ($this->internal['list']['listLength']==0) $this->internal['list']['listLength']=10;
		}



		// setting the internal values for sorting
		foreach ($this->piVars as $postvar => $postvalue) {
 			// clearing SQL Injection
 			if ($postvalue == 'DESC' || $postvalue == 'ASC') {
 				if (substr($postvar, 0, 5) == 'sort_') {
 					$this->internal['list']['sorting'] = strip_tags(substr($postvar, 5).' '.$postvalue);
 				}
 			}
		}
		#pre defined sorting is only used, as long a user did not sort by himself
		if (!$this->internal['list']['sorting'] ) {
			if ($this->conf['filelist.']['orderBy']) {
				$this->internal['list']['sorting']= $this->conf['filelist.']['orderBy']; 	# example ['filelist.']['orderBy'] = crdate DESC
			}
		}

		$this->listState->syncListState($this->internal['list']);





		/*
		 * if a filter criteria is changed, the pagebrowsing is reseted to the beginning value
		 */
		if (	t3lib_div::_GP('setFilter') ||
				!empty($this->internal['catPlus']) ||
				!empty($this->internal['catPlus']) ||
				!empty($this->internal['catMinus']) ||
				!empty($this->internal['catEquals']) ||
				!empty($this->internal['catPlus_Rec']) ||
				!empty($this->internal['catMinus_Rec']) ||
				t3lib_div::_GP('listLength')
		){
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
		$this->internal['catPlus'] = intval($this->piVars['catPlus']);
		$this->internal['catMinus'] = intval($this->piVars['catMinus']);
		$this->internal['catEquals'] = intval($this->piVars['catEquals']);
		$this->internal['catPlus_Rec'] = intval($this->piVars['catPlus_Rec']);
		$this->internal['catMinus_Rec'] = intval($this->piVars['catMinus_Rec']);
		$this->internal['catAll'] = intval($this->piVars['catAll']);
		$this->internal['catClear'] = intval($this->piVars['catClear']);

		// Aufruf der Einzelansicht
		$this->internal['singleID'] = intval($this->piVars['showUid']);

		// loading var for displaying a form for creation of a new filter state
		$this->internal['newFilter'] = strip_tags($this->piVars['newFilter']);

		// getting the incoming treeID
		$this->internal['incomingtreeID'] = intval($this->piVars['treeID']);

		// check if we are still on the same page. If we are at a different page,
		$incommingPID = $GLOBALS['TSFE']->id;

		// Selection Mode
		$this->internal['selectionMode'] = intval($this->piVars['selectionMode']);

		// Requstform
		$this->internal['showRequestform'] = intval($this->piVars['showRequestform']);
		$this->internal['docID'] = intval($this->piVars['docID']);

		//gets Data - If a requestform must be rendered
		$this->internal['sendRequestform'] = intval(t3lib_div::_POST('sendRequestform'));

		//editing of dam records
		$this->internal['confirmDeleteUID'] = intval($this->piVars['confirmDeleteUID']);
		$this->internal['deleteUID'] = intval(t3lib_div::_POST('deleteUID'));
		$this->internal['editUID'] = intval($this->piVars['editUID']);
		$this->internal['saveUID'] = intval(t3lib_div::_POST('saveUID'));
		$this->internal['catEditUID'] = intval($this->piVars['catEditUID']);

		$this->internal['backPid'] = intval($this->piVars['backPid']);

		$this->internal['filter']['searchAllCats'] = 0;
		// values for searching
		// Setting new values
 		if ($this->internal['viewID'] == 1  ) {
			$this->initList();
			$this->initFilter();
 		}
		if ($this->internal['viewID'] == 5) {
			$this->initFilter();
		}

		if ($this->internal['viewID'] == 6) {
			$this->initList();
			$this->initFilter();
		}

		// iniitalisation of an Upload
		if (t3lib_div::_POST('upload_file')) {
			$this->upload = true;
		}

		// cancel category editing
		if (t3lib_div::_POST('cancelCatEdit')) {
			$this->internal['catEditUID']=null;
			$this->internal['cancelCatEdit']=true;
		}
		// cancel category editing
		if (t3lib_div::_POST('cancelEdit')) {
			$this->internal['editUID']=null;
			$this->internal['cancelEdit']=true;
		}

		if (t3lib_div::_POST('CANCEL_DELETION')) {
			$this->internal['deleteUID']=null;
			$this->internal['confirmDeleteUID']=null;
		}
		// incoming command of saving the current category selection
		$this->saveCategorisation = strip_tags(t3lib_div::_POST('catOK')) != '' ? true : false;

		// if the session var for categorisation is set, render set the categorise var
		$this->categorise = $GLOBALS['TSFE']->fe_user->getKey('ses','categoriseID') != '' ? true:false;
		$this->saveMetaData = $GLOBALS['TSFE']->fe_user->getKey('ses','saveID') != '' ? true:false;
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
		$this->internal['catMounts']= array();
		$this->internal['catMounts'] = explode(',',$this->pi_getFFvalue($flexform, 'catMounts', 'sSelection'));

		$this->internal['treeName'] = strip_tags($this->pi_getFFvalue($flexform, 'treeName', 'sSelection'));
		$this->internal['treeID'] = $this->cObj->data['uid'];
		$this->internal['useStaticCatSelection'] = strip_tags($this->pi_getFFvalue($flexform, 'useStaticCatSelection', 'sOptions'));
		$this->conf['enableDeletions'] = strip_tags($this->pi_getFFvalue($flexform, 'enableDeletions', 'sOptions'));
		$this->conf['enableEdits'] = strip_tags($this->pi_getFFvalue($flexform, 'enableEdits', 'sOptions'));
		$this->conf['FilterUserGroup'] = strip_tags($this->pi_getFFvalue($flexform, 'FilterUserGroup', 'sOptions'));
		$this->internal['uploadCatSelection'] =strip_tags($this->pi_getFFvalue($flexform, 'uploadMounts', 'sUploadSettings'));
		$this->internal['catPreSelection'] =explode(',',$this->pi_getFFvalue($flexform, 'catPreSelection', 'sPreSelectSettings'));
		$this->conf['useLatestList'] = strip_tags($this->pi_getFFvalue($flexform, 'useLatestList', 'sOptions'));
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
		$this->loadFlexForm();
		$this->init();
		$this->convertPiVars();

		$this->filterState->filterTable = 'tx_damfrontend_filterStates';
		$this->overRide();

			// check if an user is logged in or not
		$user = $GLOBALS['TSFE']->fe_user;
		if (is_array($user->user)) {
			$this->userLoggedIn = true;
			$this->userUID = $user->user['uid'];
		}
		else {
			$this->userLoggedIn = false;
		}

			// Processing and distribution of input data
			// Mapping input parameters to actions
		$this->getInputTree();

			// Mapping the ViewIds - selected in the Flexform to the content
			// that shall be rendered
		switch ($this->internal['viewID']) {
			case 1:
				$content .= $this->fileList(false);
				break;
			case 2:
				$content .= $this->catTree();
				break;
			case 3:
				$content .= $this->catSelection();
				break;
			case 4:
				$content .= $this->singleView();
				break;
			case 5:
					// Searchbox
				$content .= $this->filterView();
				break;
			case 6:
				$content .= $this->myFiles();
				break;
			case 7:
				$content .= $this->uploadForm();
				break;
			case 8:
				$content .= $this->fileList(true);
				break;
			case 9:
				$content .= $this->testCase();
			case 99:
				$content = $this->dropDown();
				break;
			default:
				$content .= 'no view selected - nothing is displayed';
				break;
		}
		// select the view to be created
		return $this->pi_wrapInBaseClass($content);
	}

	/**
	 * handles the incoming data from the treeview (plus, minus, equals) operation
	 *
	 * @return	[type]		...
	 */
	function getInputTree() {

		if (is_array($this->piVars['dropdown'])) {
			$this->internal['incomingtreeID'] = 999;
			$count = count($this->piVars['dropdown']);

			$lastID = $this->piVars['dropdown'][$count-1];
			while($lastID == -1) {
				$count --;
				$lastID = $this->piVars['dropdown'][$count-1];
			}
			$this->catList->op_Equals($lastID,999);
		}
		else {
			if ($this->internal['catClear']) {
				$this->catList->clearCatSelection($this->internal['incomingtreeID']);
				return true;
			}

			if ($this->internal['catAll']) {
				foreach ($this->internal['catMounts'] as $catMount) {

					if (!$catMount) {
						$catMount=0;
					}
					$subs = $this->catLogic->getSubCategories($catMount);
					if (is_array($subs)){
						foreach ($subs as $sub) {
							$this->catList->op_Plus($sub['uid'], $this->internal['incomingtreeID']);
						}
					}
				}
				return true;
			}
			if ($this->internal['catPlus']) {
				$this->catList->op_Plus($this->internal['catPlus'], $this->internal['incomingtreeID']);
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


		if ($this->internal['useStaticCatSelection']==1) {
			$this->internal['incomingtreeID'] = $this->internal['treeID'];
			$this->catList->unsetAllCategories();
			if (is_array($this->internal['catMounts'])) {
				foreach ($this->internal['catMounts'] as $catMount) {
					if (strlen($catMount)) {
						$subs = $this->catLogic->getSubCategories($catMount);
						foreach ($subs as $sub) {
							$this->catList->op_Plus($sub['uid'], $this->internal['treeID']);
						}
					}
				}
			}
		}
	}



	/**
	 * renderes an category tree
	 *
	 * @return	html		category tree - ready for display
	 */
	function catTree() {
		if ($this->internal['catPreSelection']) {

			$currentCats = $this->catList->getCatSelection($this->internal['treeID']);

			if (empty($currentCats[$this->internal['treeID']])){
				// if a preselection is activated and no cat is selected yet, the preselected cats will be loaded

				if (is_array($this->internal['catPreSelection'])) {
					foreach ($this->internal['catPreSelection'] as $catMount) {

						if (strlen($catMount)) {
							if ($this->conf['categoryTree']['preSelectChildCategories']==1) {
								$subs = $this->catLogic->getSubCategories($catMount);
								foreach ($subs as $sub) {
									$this->catList->op_Plus($sub['uid'], $this->internal['treeID']);
								}
							}
							else {
								$this->catList->op_Plus($catMount, $this->internal['treeID']);
							}
						}
					}
				}
			}
		}
		##### Adding a treeview to the output


		$tree = t3lib_div::makeInstance('tx_damfrontend_catTreeView');

		$tree->init($this->internal['treeID'], $this);
		$tree->title = $this->internal['treeName'];
		$selCats  = $this->catList->getCatSelection($this->internal['treeID']);

		$tree->selectedCats = $selCats[$this->internal['treeID']];
		if (is_array($this->internal['catMounts'])) $tree->MOUNTS = $this->internal['catMounts'];
		$tree->expandTreeLevel($this->conf['categoryTree.']['expandTreeLevel']);
		return  $this->cObj->stdWrap($tree->getBrowsableTree(), $this->conf['categoryTree.']['stdWrap.']);
	}


	/**
	 * @return	[type]		...
	 * @author stefan
	 */
	function fileListBasicFuncionality () {

		$hasCats = false; // true if any category has been selected yet
		if ($this->conf['enableDeletions']==1) {
			if ($this->userLoggedIn == true) {

					// only if a user is logged in and the UserUID of the uploaded doc is equal to fe_user, then operations can be done
					// if the pi var confirmDeleteUID is set, a document should be deleted =>
				if ($this->internal['confirmDeleteUID']>0){

						// ==> check permission
					$docData = $this->docLogic->getDocument($this->internal['confirmDeleteUID']);
					if ($docData['tx_damfrontend_feuser_upload']==$this->userUID) {
							// render Confirm Message
						return $this->renderer->renderFileDeletion($docData);
					} else {
						return $this->renderer->renderError('custom','You are not allowed to delete this file!');
					}
				} else {
					if ($this->internal['deleteUID']>0){
							// if the pi var DeleteUID is set, a document must be deleted
						$docData = $this->docLogic->getDocument($this->internal['deleteUID']);
							// ==> check permission: document must be uploaded by the fe_user
						if ($docData['tx_damfrontend_feuser_upload']==$this->userUID) {
								// ==> set DAM Entry to deleted
							$deleteFile = $this->conf['security_options.']['deleteFilesOfDeletedRecords']=1?1:0;
							if ($this->docLogic->delete_document($this->internal['deleteUID'],$deleteFile,$this->userUID)==true) {
									// ==> Succes Message
								return $this->renderer->renderFileDeletionSuccess();
							} else {
								return $this->renderer->renderError('custom','Error: The deletion of the dam file was not sucessful. Reason is a database problem. Please report, that there was a problem with dam uid: '. $this->internal['deleteUID']);
							}
						} else {
							return $this->renderer->renderError('custom','You are not allowed to delete this file!');
						}
					}
				}
			}
		}
		if ($this->conf['enableEdits']==1) {
			if ($this->userLoggedIn == true) {
					// only if a user is logged in and the UserUID of the uploaded doc is equal to fe_user, then operations can be done

				if ($this->internal['saveUID'] > 0){
					$docData = $this->docLogic->getDocument($this->internal['saveUID']);
					# ==> check permission (only the owner is allowed to edit)
					if ($docData['tx_damfrontend_feuser_upload']==$this->userUID) {
						$returnCode = $this->saveMetaData($this->internal['saveUID']);
						$this->internal['editUID']=null;
						if ($returnCode<>true) {
							return $returnCode;
						}
						else {
							#clear session data
						}
					}
					else {
						return $this->renderer->renderError('custom','You are not allowed to edit this file!');
					}

				}

					// if the pi var editUID is set, a document should be edited =>
				if ($this->internal['editUID']>0){
					$docData = $this->docLogic->getDocument($this->internal['editUID']);
					# ==> check permission (only the owner is allowed to edit)
					if ($docData['tx_damfrontend_feuser_upload']==$this->userUID) {
						return $this->renderer->renderFileEdit($docData);
					} else {
						return $this->renderer->renderError('custom','You are not allowed to edit this file!');
					}
				}


				if ($this->saveCategorisation==1) {
					// check access if fe_user is allowed to edit the cat, if allow
					$docData = $this->docLogic->getDocument($this->internal['catEditUID']);
						// ==> check permission (only the owner is allowed to edit)
						// TODO expand support for groups
					if ($docData['tx_damfrontend_feuser_upload']==$this->userUID) {
						$this->saveCategories($this->internal['catEditUID'],false);
						$this->internal['catEditUID']=null;
					}
					else {
						return $this->renderer->renderError('custom','You are not allowed to edit this file!');
					}
				}
					// if the pi var catEditUID is set, a document should be categorized =>
				if ($this->internal['catEditUID']>0){
					if ($this->internal['catEditUID']<>$GLOBALS['TSFE']->fe_user->getKey('ses','categoriseID')) {
						$GLOBALS['TSFE']->fe_user->setKey('ses','categoriseID',$this->internal['catEditUID']);
						$this->catList->clearCatSelection(-1);
					}

						// check access if fe_user is allowed to edit the cat, if allow
					$docData = $this->docLogic->getDocument($this->internal['catEditUID']);
						// ==> check permission (only the owner is allowed to edit)
						// TODO expand support for groups
					if ($docData['tx_damfrontend_feuser_upload']==$this->userUID) {
						return $this->categoriseForm($docData);
					}
					else {
						return $this->renderer->renderError('custom','You are not allowed to edit this file!');
					}
				}
			}
		}

		if ($this->conf['useLatestList']==1) {
			$this->docLogic->conf['useLatestList'] = true;
			$this->docLogic->conf['latestField'] = ($this->conf['filelist.']['latestField']) ? $this->conf['filelist.']['latestField'] : 'crdate';
			$this->docLogic->conf['latestLimit'] = ($this->conf['filelist.']['latestLimit']) ? $this->conf['filelist.']['latestField'] : 30;
		}
		return true;
	}

	/**
	 * fist this function gets the list of selected categories from the session vars
	 * after that it retrievs a list of all documents from the doclogic
	 * all documents are contained, which nat filtered and the user has
	 * After that, the list is converted to an html view, rendered by the renderer instance
	 *
	 * @param	[type]		$useRequestForm: ...
	 * @return	html		HTML - list of all selected documents
	 */
	function fileList($useRequestForm) {

		$result =$this->fileListBasicFuncionality();
		if ($result<>1) {
			return  $result;
		}

		if (is_array($this->piVars['dropdown'])) {
			$cats = $this->catList->getCatSelection(999,0);
		}
		else {
			$cats = $this->catList->getCatSelection(0,$this->pid);
		}

		if (count($cats)) {
			foreach($cats as $catList) {
				if (count($catList)) $hasCats = true;
			}
		}
		if ($hasCats===true || $this->internal['filter']['searchAllCats']===true ) {

			/***************************
			 *
			 *    search and sorting values are transfered to the user
			 *
			 ***************************/

			if (is_array($this->internal['filter'])) {
				$this->internal['filterError'] = $this->docLogic->setFilter($this->internal['filter']);
			}
			#t3lib_div::debug($cats);
			$this->docLogic->orderBy = $this->internal['list']['sorting'];
			$this->docLogic->limit = $this->internal['list']['limit'];
			$this->docLogic->categories = $cats;
			$this->docLogic->selectionMode = $this->internal['selectionMode'];
			$files = $this->docLogic->getDocumentList($this->userUID);
			if (is_array($files)) {

				$rescount = $this->docLogic->resultCount;
					// check if pointer is ok
				$limiter=0;
				if ($this->internal['list']['listLength']==1) $limiter = 1;
				$noOfPages = intval($rescount / $this->internal['list']['listLength'])-$limiter;

				if($this->internal['list']['pointer'] >$noOfPages) {
					// set pointer to max value
					return  $this->renderer->renderError('noPointerError');
				}

				 //get the html from the renderer
				$content = $this->renderer->renderFileList($files, $rescount, $this->internal['list']['pointer'], $this->internal['list']['listLength'],$useRequestForm);
			}
			else {
				// render error
				// @TODO: IMHO it is not an error to have an empty category!
				$content = $this->renderer->renderError('noDocInCat');
			}
		}
		else {
			//render error
			// TODO: should ignore categories instead rendering an error message
			$content = $this->renderer->renderError('noCatSelected');
		}
		return $content;
	}

	/**
	 * Renders the search box
	 *
	 * @return	[string]		$content html auf filterview
	 */
	function filterView() {
		$content = $this->renderer->renderFilterView($this->internal['filter'], $this->internal['filterError']);
		if ($this->internal['filter']['searchAllCats'] ==true) {
			$content=str_replace("name=\"dam_fe_allCats\"","name=\"dam_fe_allCats\" checked",$content);
		}
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
		$selection = $this->catList->getCatSelection(0,$GLOBALS['TSFE']->id );

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
				$record['backPid']= $this->internal['backPid'];
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


	/*********************************
	 *
	 *
	 * 	FILE UPLOAD AND CATEGORISATION
	 *
	 *
	 *********************************/



	/**
	 * Shows the upload form
	 *
	 * @return	[string]		$content the html code of the form
	 */
	function uploadForm() {

		$step = 1;

		if ($this->internal['cancelCatEdit']==1) {
				//user wants to cancel the save of cat data
				// TODO show warning
			$GLOBALS['TSFE']->fe_user->setKey('ses','saveID',$GLOBALS['TSFE']->fe_user->getKey('ses','categoriseID') );
			$GLOBALS['TSFE']->fe_user->setKey('ses','categoriseID','');
			$this->catList->clearCatSelection(-1);
			$this->saveCategorisation=false;
			$this->saveMetaData=true;
			$this->categorise=false;
			$this->upload=false;
			$step = 2;
		}

		if ($this->saveMetaData==1 && $this->internal['cancelEdit']==1) {
				// user wants to cancel the save of meta data
				// TODO show warning
			$this->internal['saveUID'] =null;
			$GLOBALS['TSFE']->fe_user->setKey('ses','saveID','');
			$GLOBALS['TSFE']->fe_user->setKey('ses','versioningOverrideID','');
			$GLOBALS['TSFE']->fe_user->setKey('ses','versioning','');
			$GLOBALS['TSFE']->fe_user->setKey('ses','versioningNewVersionID','');
			$this->saveCategorisation=false;
			$this->saveMetaData=false;
			$this->categorise=false;
			$this->upload=false;
				// TODO delete file & metadata
			$step = 1;
		}

			// works only if a user is logged on -> use access rights for the content element to set the access rights for upload
			// TODO implement upload rights
		if (is_array($GLOBALS['TSFE']->fe_user->user)) {
			// todo maybe built an option to restrict uploads for users

			if ($this->upload) {
				$returnCode = $this->handleUpload();

				if (intval($returnCode) != 0) {
						// -- UPLOAD SUCCESSFUL CATEGORISATION OR VERSIONING --

						// upload was successful - proceeding with categorisation
					$newID = $returnCode;

						// File exists - show versioning options
					if ($this->versionate) {
						return $this->versioningForm();
					}
					else {
						$this->getIncomingDocData();
						$step = 2;
					}
				}
				else {
					// -- UPLOAD NOT SUCCESSFUL --

					// rendering of an error message - messages from the upload extension
					return $returnCode . '<br><br>' . $this->renderer->renderUploadForm();
				}
			}

				// saving edition of meta data
			if ($this->internal['saveUID'] > 0){
				$returnCode = $this->saveMetaData($this->internal['saveUID']);
				if ($returnCode<>true) {
					$content=$returnCode;
				}
				else {
						#if saving of the metadata was successful, next step for upload form must be prepared
						#first check, if the saved id is the id of the uploaded file
					if ($this->internal['saveUID'] ==$GLOBALS['TSFE']->fe_user->getKey('ses','saveID')) {

							#set categoriseID, that next step of upload form function is processed
						$GLOBALS['TSFE']->fe_user->setKey('ses','categoriseID', $this->internal['saveUID']);

							#delete saveID, that edit form is not shown anymore
						$GLOBALS['TSFE']->fe_user->setKey('ses','saveID', '');

							#set internal control to true
						$this->categorise=true;
					}
				}
			}

				// UPLOAD DONE OR EDIT of meta data - > show file edit form
			if ($this->saveMetaData==1) {
				$step = 2;
			}

				// -- META Data saved DONE - SHOW CATEGORISATION --
			if ($this->categorise==1) {
				$step = 3;
			}

			if($this->saveCategorisation==1) {
				$docID = intval($GLOBALS['TSFE']->fe_user->getKey('ses','categoriseID'));
				$this->saveCategories($docID);
				$this->categorise=false;
				$step = 4;
			}
		}
		else {
			// no user currently logged in - upload feature is disabled
			return $this->renderer->renderError('noUserLoggedIn');
		}

			// render the html output
		switch ($step) {
			case 1:
				$GLOBALS['TSFE']->fe_user->setKey('ses','categoriseID','');
				return $this->renderer->renderUploadForm();
				break;
			case 2:
				return $this->editForm(intval($GLOBALS['TSFE']->fe_user->getKey('ses','uploadID')));
				break;
			case 3:
				return $this->categoriseForm();
				break;
			case 4:
				return $this->renderer->renderUploadSuccess();
				break;
			default:
				return 'no step!';
				break;
		}
	}





	/**
	 * undocumented function
	 * // TODO: write documentation
	 * 
	 *
	 * @return	void
	 * @author Martin Baum
	 */
	function dropDown() {
		$v=unserialize($GLOBALS['TSFE']->fe_user->getKey('ses','tx_damdownloads_treeState'));
		$treeMap=$this->getTreeMap();

		$post=$_POST["tx_damfrontend_pi1"];
		$levelState=$this->piVars["dropdown"];
		for ($i=0;$i<count($levelState);$i++) {
			if (!($levelState[$i]>0)) $levelState[$i]=-1;
		}
		$formSt="<form id='formDamSelect' name='formDamSelect' action='' method='POST'>";
		$formHidden="<input type='hidden' name='tx_damfrontend_pi1[treeID]' value='999'><input type='hidden' name='treeID' value='14'>";
		$optString=array();
		$optString[]="<select name='tx_damfrontend_pi1[dropdown][0]' id='level1' onChange='document.formDamSelect.submit();'>".$this->getSubNodesOptions($treeMap,0,$levelState[0])."</select><br>";
		$i=0;
		while ((isset($levelState[$i]))&&(count($this->getSubNodes($treeMap,$levelState[$i]))>0)) {
			$optString[]="<select name='tx_damfrontend_pi1[dropdown][".($i+1)."]' onChange='document.formDamSelect.submit();'>".($this->getSubNodesOptions($treeMap,$levelState[$i],$levelState[$i+1]))."</select><br>";
			$i++;
		}
		$formEnd="</form>";
		return 'Sorry: This function is currently disabled: Please check for newer version.';
		#return $formSt.implode("",$optString).$formHidden.$formEnd;
	}


	/**
	 * undocumented function
	 *
	 * 
	 *
	 * @param	[type]		$arr: ...
	 * @param	[type]		$node: ...
	 * @param	[type]		$subnode: ...
	 * @return	void
	 * @author Martin Baum
	 */
	function isSubNodeOf($arr,$node,$subnode) {
		$subs=$this->getSubNodes($arr,$node);
		$found=false;
		for ($i=0;$i<count($subs);$i++) {
  			if ($subs[$i]==$subnode) return true;
  			if ($this->isSubNodeOf($arr,$subs[$i],$subnode)) return true;
  		}
  		return false;
	}




	/**
	 * undocumented function
	 *
	 * 
	 *
	 * @param	[type]		$arr: ...
	 * @param	[type]		$parent: ...
	 * @return	void
	 * @author Martin Baum
	 */
	function getSubNodesCount($arr,$parent) {
		return count($this->getSubNodes($arr,$parent));
	}



	/**
	 * undocumented function
	 *
	 * 
	 *
	 * @param	[type]		$arr: ...
	 * @param	[type]		$parent: ...
	 * @return	void
	 * @author Martin Baum
	 */
	function getSubNodes($arr,$parent) {
		$retArr=array();
		foreach ($arr as $nr=>$val) {
			if ($val["parent"]==$parent) $retArr[]=$nr;
		}
		return $retArr;
	}



	/**
	 * undocumented function
	 *
	 * 
	 *
	 * @param	[type]		$arr: ...
	 * @param	[type]		$parent: ...
	 * @param	[type]		$selVal: ...
	 * @return	void
	 * @author Martin Baum
	 */
	function getSubNodesOptions($arr,$parent,$selVal=-1) {
		$ret="";
		foreach ($arr as $nr=>$val) {
			if ($val["parent"]==$parent) $ret.="<option value='$nr' ".(($selVal==$nr)?"selected":"").">".$val["title"]."</option>";
		}
		if(strlen($ret)>0) $ret="<option value='-1' ".(($selVal==-1)?"selected":"").">Please select...</option>".$ret;
		return $ret;
	}





	/**
	 * renders a form for versioning of a file
	 *
	 * @return	[type]		...
	 */
	function versioningForm() {
		return $this->renderer->renderVersioningForm();
	}





	/**
	 * ******************
	 * calls the handle Upload Extension and outputs the error messages to the frontend
	 *
	 * @return	int		the ID of uploaded file in the dam table, if there is an error, the error message is returned
	 */
		function handleUpload() {
			global $TYPO3_CONF_VARS;
			// make Instance of the class for fileupload handling
			if (!t3lib_extMgm::isLoaded('fileupload')) {
				return $this->renderer->renderError('uploadExtensionNotInstalled');
			}
			else {
					// creating the Object of the upload handler
					// getting TS for the Extension
					// creating an instance
				$uploadHandler = t3lib_div::getUserObj('EXT:fileupload/pi1/class.tx_fileupload_pi1.php:&tx_fileupload_pi1');

				$fileUploadTS = is_array($this->conf['upload.']['conf.']) ? $this->conf['upload.']['conf.'] : $this->loadFileUploadTS();

					// retrieving the path of the uploaded file
				if($fileUploadTS['path']){
					$path=$this->cObj->stdWrap($fileUploadTS['path'],$fileUploadTS['path.']);
				}
				if (is_dir($path)) {
					$uploaddir = $path;
				}
				else {
						// try to get default path of the BE
					if (is_dir($TYPO3_CONF_VARS['BE']['fileadminDir'])) {
						$uploaddir = $TYPO3_CONF_VARS['BE']['fileadminDir'];
					}
					else {
						return  $this->pi_getLL('upload_path_error');
					}

				}

					// temp upload folder for dam_frontend (default =  dam_frontend_upload)
				if($fileUploadTS['uploadTempDir']){
					$uploadTempDir=$this->cObj->stdWrap($fileUploadTS['uploadTempDir'],$fileUploadTS['uploadTempDir.']);
				}

				// check if the temp folder exists
				if (!is_dir($uploaddir.$uploadTempDir)){
					$uploadTempDir='';
					t3lib_div::syslog('﻿Upload temp path is not configured correctly. The path does not exist: '.$uploaddir.$uploadTempDir. ' Please set the value uploadTempDir in plugin.dam_frontend.upload.conf ','dam_frontend',3);
				}

				if($fileUploadTS['FEuserHomePath']==1){
					if($fileUploadTS['FEuserHomePath.']['field']){
						$feuploaddir=$GLOBALS["TSFE"]->fe_user->user[$fileUploadTS['FEuserHomePath.']['field']].'/';
					}
					else {
						$feuploaddir=$GLOBALS["TSFE"]->fe_user->user["uid"].'/';
					}
						// disable FEuserHomePath of fileupload, because dam_fe handles itself
					$fileUploadTS['FEuserHomePath']=0;
				}
				else {
					$feuploaddir='';
				}

				$fileUploadTS['path'] = $uploaddir.$uploadTempDir;

				$uploadHandler->cObj = $this->cObj;
				$uploadHandler->main('',$fileUploadTS);
				$_FILES[$uploadHandler->prefixId] = $_FILES['file'];

					// set the complete filename & path where the file should be stored in the last step of the upload process
				$uploadfile = PATH_site.$uploaddir.$feuploaddir.$_FILES[$uploadHandler->prefixId]['name'];
					// store the filename of the final destination in the session, that the file can be copied in the last step form temp upload dir to final destination
				$GLOBALS['TSFE']->fe_user->setKey('ses','uploadFileName',$_FILES[$uploadHandler->prefixId]['name']);
				$GLOBALS['TSFE']->fe_user->setKey('ses','uploadFilePath',$uploaddir.$feuploaddir);

				if(!is_dir($uploaddir.$feuploaddir)){
					if(!mkdir($uploaddir.$feuploaddir)){
						// todo add localized error
						return 'error: can not create target folder [upload]';
					}
				}

					// check if the current file is already present  - preparing for versioning
				if (is_file($uploadfile)) {
					$this->versionate = true;
				}

					// set values for the dam record
				$this->documentData['file_dl_name']=$_FILES[$uploadHandler->prefixId]['name'];
				$this->documentData['title'] = $_FILES[$uploadHandler->prefixId]['name'];
				$this->documentData['tx_damfrontend_feuser_upload'] = $this->userUID;

					// *************************************************
					// overwrite TS Setting of the fileupload Extention
					//

					// set a temp filename and path for the first upload
				$uploadTimeStamp = time();
				$GLOBALS['TSFE']->fe_user->setKey('ses','uploadTimeStamp',$uploadTimeStamp);
				$_FILES[$uploadHandler->prefixId]['name'] = $GLOBALS["TSFE"]->fe_user->user["uid"].'_'.$uploadTimeStamp.'_'.$_FILES[$uploadHandler->prefixId]['name'];

					// store in tempDir
				$fileUploadTS['path'] = $uploaddir.$uploadTempDir;

				$uploadfile=PATH_site.$uploaddir.$uploadTempDir.$_FILES[$uploadHandler->prefixId]['name'];

					//
					// end overwirting of ts settings
					//
					//*************************************************

					// delete existing file in temp upload
				if (is_file($uploadfile)) {
					unlink($uploadfile);
				}

					// set Document to delted, that it is not shown in the frontend
				$this->documentData['deleted']=1;

					// set fe_user group
				if ($this->conf['upload.']['autoAsignFEGroups']==1){
					// fetch the usergroups the fe_user is belonging and put them into the access field
					$userGroups=$GLOBALS['TSFE']->fe_user->groupData['uid'];
					if (is_array($userGroups)) $this->documentData['fe_group']= implode(',',$userGroups);
				}
					// autofill fe_user data
				if ($this->conf['upload.']['autoFillFEUserData.']){
					// fetch the usergroups the fe_user is belonging and put them into the access field
					foreach ($this->conf['upload.']['autoFillFEUserData.'] as $key=>$value) {
						$this->documentData[$key] = $GLOBALS['TSFE']->fe_user->user[$value];
					}

				}
					// final upload
				$uploadHandler->handleUpload();

					// adding the uploaded file to the DAM System, if no error occured

				if (is_file($uploadfile)) {
					$newID = $this->docLogic->addDocument($uploadfile, $this->documentData);
						// add predefined category setting: TODO discuss: should there only categories passible the fe user has access to?
					if ($this->conf['upload.']['enableCategoryPreSelection']==1) {
						if (is_array($this->internal['catPreSelection'])) {
							$catArr = array();
							foreach ($this->internal['catPreSelection'] as $catMount) {
								if (strlen($catMount)) {
									if ($this->conf['upload.']['preSelectChildCategories']==1) {
										$subs = $this->catLogic->getSubCategories($catMount);
										foreach ($subs as $sub) {
											$catArr[]= $sub['uid'];
										}
									}
									else {
										$catArr[] = $catMount;
									}
								}
							}
							$returnCode = $this->docLogic->categoriseDocument($newID,$catArr);
						}
					}
					$GLOBALS['TSFE']->fe_user->setKey('ses','uploadID',$newID);
					$GLOBALS['TSFE']->fe_user->setKey('ses','saveID',$newID);
					return $newID;
				}
				else {
					$errorContent = '';
					foreach ($uploadHandler->status as $error) {
						$errorContent .= $error;
					}
					$GLOBALS['TSFE']->fe_user->setKey('ses','uploadID','');

					return $this->renderer->renderError('custom',$errorContent);
				}
			}
		}

	/**
	 * [load the default typoscript of the upload extension]
	 *
	 * @return	[type]		...
	 */
	function loadFileUploadTS() {
	   $sysPageObj = t3lib_div::makeInstance('t3lib_pageSelect');
	   $rootLine = $sysPageObj->getRootLine($this->cObj->data['pid']);
	   $TSObj = t3lib_div::makeInstance('t3lib_tsparser_ext');
	   $TSObj->tt_track = 0;
	   $TSObj->init();
	   $TSObj->runThroughTemplates($rootLine);
	   $TSObj->generateConfig();
	   return $TSObj->setup['plugin.']['tx_fileupload_pi1.'];
	}

	/**
	 * get the data of the upload form and validate them
	 *
	 * @return	[string]		errormessage / TRUE if there was no error
	 */
	function getIncomingDocData() {
		// conversion of incoming data from the creation of an new document
		$this->documentData['title'] = strip_tags(t3lib_div::_POST('title'));
		$this->documentData['creator'] = strip_tags(t3lib_div::_POST('creator')); #45
		$this->documentData['description'] = strip_tags(t3lib_div::_POST('description')); #65000
		$this->documentData['copyright'] = strip_tags(t3lib_div::_POST('copyright')); #128
		$this->documentData['language'] = strip_tags(t3lib_div::_POST('LanguageSelector')); #128
		if ($this->documentData['language']=='nosel') $this->documentData['language']='';
		if(strlen($this->documentData['language'])>3) {
			return ($this->renderer->renderError('uploadFormFieldError','title','255'));
		}

		if(strlen($this->documentData['title'])>255) {
			return ($this->renderer->renderError('uploadFormFieldError','title','255'));
		}

		if(strlen($this->documentData['creator'])>45) {
			return $this->renderer->renderError('uploadFormFieldError','creator','45');
		}

		if(strlen($this->documentData['description'])>65000) {
			return $this->renderer->renderError('uploadFormFieldError','description','65000');
		}

		if(strlen($this->documentData['copyright'])>128) {
			return $this->renderer->renderError('uploadFormFieldError','copyright','45');
		}
		return true;
	}



	/**
	 * Show the categoriseform
	 *
	 * @param	array		$docData if set: an existing document is categorized new
	 * @return	[html]		...
	 */
	function categoriseForm($docData='') {

		if (is_array($docData)) {
			$docID = $docData['uid'];
			$versioning = 'editCats';
		}
		else {
			$docID = intval($GLOBALS['TSFE']->fe_user->getKey('ses','categoriseID'));
			$docData = $this->docLogic->getDocument($docID);
			$versioning = t3lib_div::_GP('version_method');
		}

			// check if a category is allready selected, if not if will tried to load the categories out of the database
		$cats = $this->catList->getCatSelection(-1,0);
		if ($cats==null) {
				// if no cats are given by an user, search in the database for cats (this should usually done only once)
			switch ($GLOBALS['TSFE']->fe_user->getKey('ses','versioning')){
				case 'override':
						// get the cats of the doc which should be overwritten
					$cats=$this->docLogic->getCategoriesbyDoc($GLOBALS['TSFE']->fe_user->getKey('ses','versioningOverrideID'),true);
					break;
				case 'new_version':
						// get the cats of the doc which should get a new version
					$cats=$this->docLogic->getCategoriesbyDoc($GLOBALS['TSFE']->fe_user->getKey('ses','versioningNewVersionID'),true);
					break;
				default:
					$cats=$this->docLogic->getCategoriesbyDoc($docID,true);
					break;
			}

				// store cat selection in the user array
			if (is_array($cats)) {
				$catarray[-1] = $cats;
 				$this->catList->setArrayToUser($catarray);
 				$cats = $this->catList->getCatSelection(-1,0);
			}
		}

		#get all allowed categories
		$uploadCats = $this->internal['uploadCatSelection'];
		#t3lib_div::debug($cats);
		if (is_array($cats)) {
			foreach($cats as $cat) {
				$catData[] = $this->catLogic->getCategory($cat);
			}
			$content =  $this->renderer->renderCategorisationForm($docData,$catData,$uploadCats,$versioning);
		}
		else {
			$content = $this->renderer->renderCategorisationForm($docData,'',$uploadCats,$versioning);
		}
		return $content;
	}

	/**
	 * Save the categories of a uploaded file
	 *
	 * @param	int		$docID id of the dam record that should be categorized / saved
	 * @param	[type]		$upload: ...
	 * @return	[boolean]		true if saving was sucessful
	 * @p
	 */
	function saveCategories($docID,$upload=true) {
		if ($this->userLoggedIn==true) {
			if ($this->docLogic->checkOwnerRights($docID, $this->userUID)==true){

				$cats = $this->catList->getCatSelection(-1,0);
					// fixme check upload categories, allow only cats a user has rights to
				if (is_array($cats)) $this->docLogic->categoriseDocument($docID, $cats);

				if ($upload==true) {
					$this->docLogic->storeDocument($docID);
					$GLOBALS['TSFE']->fe_user->setKey('ses','uploadID','');
					$GLOBALS['TSFE']->fe_user->setKey('ses','saveID', '');
				}
					// finisch up with cleaning
				$this->catList->clearCatSelection(-1);
				$GLOBALS['TSFE']->fe_user->setKey('ses','categoriseID','');
					// set record to visible
				return $this->renderer->renderUploadSuccess();
			}
			else {
				return $this->renderer->renderError('no_access');
			}
		}
		else {
			return $this->renderer->renderError('noUser');
		}
	}

	/**
	 * returns an array of users, if current user is given the user is selected in this array
	 *
	 * @param	int		$fe_group uid of the fe_group
	 * @param	string		$currentUser Username or name of the user
	 * @return	array		all fe_users which shoud be selected
	 * @author stefan
	 */
	function get_FEUserList ($fe_group=0,$currentUser ='') {
		if ($fe_group>0) {
			#get uid of the given fe_user_group
			$SELECT = 'recuid';
			$FROM = 'sys_refindex';
			$WHERE = 'tablename ="fe_users" AND ref_table = "fe_groups" AND ref_uid in ('.$fe_group .')' ;
			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery($SELECT, $FROM, $WHERE);
			while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
				$recuid[]=$row['recuid'];
			}
			#get all direct user of fe_user_group uid

			#todo resolve all subgroups of the given
			# Build a Where Clause
			$userListOfFEGroups=' AND uid in('. implode(',',$recuid) .') ';
		}
		$SELECT = '*';
		$FROM = 'fe_users';
		$WHERE = 'deleted = 0 AND disable = 0 AND starttime <' .time() . ' AND (endtime = 0 OR endtime > '. time().')' . $userListOfFEGroups;
		$ORDERBY = 'name, username';
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery($SELECT, $FROM, $WHERE,'' , $ORDERBY);
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			if ($currentUser<>'' AND ($currentUser == $row['name'] or $currentUser == $row['username'] or $currentUser == $row['uid'])) {
				$row['selected']=1;
			}
			else {
				$row['selected']=0;
			}
			$feUserList[]=$row;
		}
		return ($feUserList);
	}




	/**
	 * Renders the myFile view
	 *
	 * @return	html		the list of files of a user or an error string
	 * @author stefan
	 */
	function myFiles () {
		$this->renderer->piVars = $this->piVars;
		if ($this->userLoggedIn==false){
			return $this->renderer->renderError('noUserLoggedIn');
		}

		$result =$this->fileListBasicFuncionality();

		if ($result<>1) {
			return  $result;
		}

		/***************************
		 *
		 *    search and sorting values are transfered to the user
		 *
		 ***************************/
		$this->internal['filter']['owner']=$this->userUID;
		if (is_array($this->internal['filter'])) {
			$this->internal['filterError'] = $this->docLogic->setFilter($this->internal['filter']);
		}
		$this->docLogic->orderBy = $this->internal['list']['sorting'];
		$this->docLogic->limit = $this->internal['list']['limit'];
		$this->docLogic->selectionMode = $this->internal['selectionMode'];
		$files = $this->docLogic->getDocumentList($this->userUID);
		if (is_array($files)) {
			//get the html from the renderer
			$rescount = $this->docLogic->resultCount;
			# if a request form should be rendered
			$useRequestForm=false;
			$content = $this->renderer->renderFileList($files, $rescount, $this->internal['list']['pointer'], $this->internal['list']['listLength'],$useRequestForm);
		}
		else {
			// render error
			$content = $this->renderer->renderError('noDocInCat');
		}
		return $content;
	}

	/**
	 * [Describe function...]
	 *
	 * @param	[type]		$code: ...
	 * @return	[type]		...
	 */
	function handleVersioning($code) {
		$trigger = t3lib_div::_GP('version_method');
		$docID = $GLOBALS['TSFE']->fe_user->getKey('ses','uploadID');
		if (!isset($trigger)) die('call of this function (handleversioning) without url - parameter');

		switch ($trigger) {
			case 'override':
				$GLOBALS['TSFE']->fe_user->setKey('ses','versioning','override');
				return $this->docLogic->versioningOverridePrepare($docID);
				break;
			case 'new_version':
				$GLOBALS['TSFE']->fe_user->setKey('ses','versioning','new_version');
				return $this->docLogic->versioningCreateNewVersionPrepare($docID);
				break;
		}
	}

	/**
	 * @param	int		$uid UID of the DAM record which should be edited
	 * @param	boolean		$checkAccess if true, access is check with categories
	 * @return	string		html of the editing form
	 * @author stefan
	 */
	function editForm ($uid=0) {

		if (intval($uid)>0) {
			if ($this->versioning != '') {
				if ($this->docLogic->checkOwnerRights($uid,$this->userUID)==true){
					$uid = $this->handleVersioning($this->versioning);
					$GLOBALS['TSFE']->fe_user->setKey('ses','saveID', $uid);
					$GLOBALS['TSFE']->fe_user->setKey('ses','uploadID',$uid);
				}
				else {
					return $this->renderer->renderError('no_access');
				}
			}

			$docData = $this->docLogic->getDocument($uid);
			# ==> check permission (only the owner is allowed to edit)
			if ($docData['tx_damfrontend_feuser_upload']==$this->userUID) {
				return $this->renderer->renderFileEdit($docData);
			} else {
				return $this->renderer->renderError('no_access');
			}
		}
		else {
			$GLOBALS['TSFE']->fe_user->setKey('ses','saveID',Null) ;
			return $this->renderer->renderError('custom','No ID given. ','Edit DAM Metadata (editForm): Please take care, that an uid is given to this function');
		}
	}



	/**
	 * undocumented function
	 *
	 * 
	 *
	 * @return	void
	 * @author Martin Baum
	 */
	function overRide2() {
		$x=$this->catList->getCatSelection($this->internal['incomingtreeID']);
		if (count($x[$this->internal['incomingtreeID']])<1) {
		}
	}



	/**
	 * undocumented function
	 *
	 * 
	 *
	 * @return	void
	 * @author Martin Baum
	 */
	function overRide() {

		$levelState=$this->piVars["dropdown"];
		$isDropSel=(isset($levelState[0])&&($levelState[0]>0));
		if ($isDropSel) {
			for ($i=0;$i<count($levelState);$i++) {
				if (!($levelState[$i]>0)) $levelState[$i]=-1;
			}

			$treeMap=$this->getTreeMap();
			$this->internal['catEquals'] = $levelState[0];
			$i=1;
			while((isset($levelState[$i]))&&($levelState[$i]>0)&&($this->isSubNodeOf($treeMap,$levelState[$i-1],$levelState[$i]))) {
				$this->internal['catEquals'] = $levelState[$i];
				$i++;
			}
		}

	}


	/**
	 * undocumented function
	 *
	 * 
	 *
	 * @return	void
	 * @author Martin Baum
	 */
	function getTreeMap() {
		$tree = t3lib_div::makeInstance('tx_damfrontend_catTreeView');

		$tree->init($this->internal['treeID'], $this);
		$tree->sessionVar="fb_blubb";
		$tree->title = $this->internal['treeName'];
		$selCats  = $this->catList->getCatSelection($this->internal['treeID']);
		$tree->selectedCats = $selCats[$this->internal['treeID']];
		if (is_array($this->internal['catMounts'])) $tree->MOUNTS = $this->internal['catMounts'];
		$tree->initializePositionSaving();
		$treeArr=array();

		foreach($tree->MOUNTS as $idx => $uid)	{
			$tree->bank=$idx;
			$isOpen = $tree->stored[$idx][$uid] || $tree->expandFirst;
			$tree->expandAll=1;
			$curIds = $tree->ids;
			$tree->reset();
			$tree->ids = $curIds;
			$cmd=$tree->bank.'_'.($isOpen?"0_":"1_").$uid.'_'.$tree->treeName;
			if ($uid)	{
				$rootRec = $tree->getRecord($uid);
			} else {
				$rootRec = $tree->getRootRecord($uid);
			}

			if (is_array($rootRec))	{
				$uid = $rootRec['uid'];		// In case it was swapped inside getRecord due to workspaces.

					// Add the root of the mount to ->tree
				$tree->tree[]=array('row'=>$rootRec);
					// If the mount is expanded, go down:
				if (true||$isOpen)	{
					if ($tree->addSelfId)	$this->ids[] = $uid;
					//@TODO: $depthD is not available
					$tree->getTree($uid,999,$depthD,'',$rootRec['_SUBCSSCLASS']);
				}
				$treeArr=array_merge($treeArr,$tree->tree);
			}
		}
		$treeMap=array();
		foreach($tree->tree as $nodeNr=>$node) {
			if ($nodeNr>0) {
				$data=$node["row"];
				$title=$data["title"];
				$parent=$data["parent_id"];
				$treeMap[$data["uid"]]=array("title"=>$title,"parent"=>$parent);
			}
		}
		return $treeMap;
	}


	/**
	 * undocumented function
	 *
	 * 
	 *
	 * @param	[type]		$mount: ...
	 * @return	void
	 * @author Martin Baum
	 */
	function getTree($mount= '') {
		$tree = t3lib_div::makeInstance('tx_damfrontend_catTreeView');
		$tree->init();
		$tree->selectedCats = $this->catList->getCatSelection();
		if ($mount != '') $tree->MOUNTS;
		return $tree;
	}

	/**
	 * [Describe function...]
	 *
	 * @return	[type]		...
	 */
	function testCase () {
		#$this->catLogic->
		#return $content;
	}

	/**
	 * @param	[type]		$saveUID: ...
	 * @return	[type]		...
	 * @author stefan
	 */
	 function saveMetaData ($saveUID) {
		if ($this->userLoggedIn==true) {
			if ($this->docLogic->checkOwnerRights($saveUID, $this->userUID)==true){
				#set edit UID to zero, so the edit form isnot shown anymore
				$this->internal['editUID']=0;


				# get the data from the edit form
				$returnCode = $this->getIncomingDocData();
				if ($returnCode==true) {
					$this->docLogic->saveMetaData($saveUID,$this->documentData);
					return true;
				}
				else {
					return $returnCode;
				}
			}
			else {
				return $this->renderer->renderError('no_access');
			}
		}
		else {
			return $this->renderer->renderError('noUser');
		}
	}
}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/dam_frontend/pi1/class.tx_damfrontend_pi1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/dam_frontend/pi1/class.tx_damfrontend_pi1.php']);
}

?>