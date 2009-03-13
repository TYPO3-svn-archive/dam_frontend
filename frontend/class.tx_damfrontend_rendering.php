<?php
require_once(PATH_tslib.'class.tslib_content.php');
require_once(PATH_tslib.'class.tslib_pibase.php');
require_once(t3lib_extMgm::extPath('dam_frontend').'/frontend/class.tx_damfrontend_categorisationTree.php');
require_once(t3lib_extMgm::extPath('static_info_tables').'pi1/class.tx_staticinfotables_pi1.php');

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
 * class.tx_damfrontend_rendering.php
 *
 *  Division between the frontend script pi1
 * and the rendering of the content.
 *
 * @package typo3
 * @subpackage tx_dam_frontend
 * @author Martin Baum <typo3@in2form.com>
 * @todo add stdWrap possibilities
 * Some scripts that use this class:	--
 * Depends on:		--
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 *   73: class tx_damfrontend_rendering extends tslib_pibase
 *   90:     function init()
 *  102:     function setFileRef($filePath)
 *  117:     function renderFileList($list, $resultcount, $pointer, $listLength)
 *  186:     function renderBrowseResults($resultcount, $pointer, $listLength)
 *  212:     function renderSortLink($key)
 *  230:     function renderCatSelection($list)
 *  258:     function renderSingleView($record)
 *  284:     function renderError($errormsg = 'deflaut')
 *  317:     function recordToMarkerArray($record)
 *  341:     function renderFilterView($filterArray, $errorArray = '')
 *  383:     function renderFileTypeList($filetype)
 *  427:     function renderFilterError($errorCode)
 *  438:     function renderUploadForm()
 *  450:     function renderFilterList($filterList)
 *  471:     function renderFilterCreationForm()
 *  484:     function renderFilterCreationLink()
 *  496:     function getFileIconHref($mimeType, $mimeSubType)
 *  523:     function getIconBaseAddress()
 *
 * TOTAL FUNCTIONS: 18
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */
 class tx_damfrontend_rendering extends tslib_pibase{
	var $prefixId = 'tx_damfrontend_pi1';		// Same as class name
	var $scriptRelPath = 'pi1/class.tx_damfrontend_pi1.php';	// Path to this script relative to the extension dir.
	var $extKey = 'dam_frontend';	// The extension key.
	var $pi_checkCHash = TRUE;
	var $iconPath = 'typo3/gfx/';
	var $langFile;
 	var $filePath; // contains the physical path to the filereference
 	var $fileContent; // HTML content of the given filepath

	var $iconBaseAddress = null;
	var $staticInfoObj;
	var $debug = 0;

	/**
	 * inits this class (loading the locallang file)
	 *
	 * @return	[void]		...
	 */
	function init() {
		$this->pi_loadLL();
		$this->iconBaseAddress = $this->conf['iconBaseAddress'];
		$this->langFile = $this->conf['langFile'];
		$this->debug = $this->conf['debug'];
		// TODO: check if it is possible to make staticinfotables optional
		$this->staticInfoObj = null;
		if (t3lib_extMgm::isLoaded('static_info_tables')) {
			$this->staticInfoObj = t3lib_div::getUserObj('&tx_staticinfotables_pi1');
			if (!method_exists($this->staticInfoObj, 'needsInit') || $this->staticInfoObj->needsInit())	{
				$this->staticInfoObj->init();
			}
		}
	}


	/**
	 * sets the paths for file references
	 *
	 * @param	[string]		$filePath: ...
	 * @return	[type]		...
	 */
 	function setFileRef($filePath) {
 		$this->filePath = $filePath;
 		$this->fileContent = tsLib_CObj::fileResource($filePath);
 	}


	/**
	 * function renderes a list of all files, which are availible
	 *
	 * @param	array		$list: ...
	 * @param	rowcount		$resultcount: ...
	 * @param	[type]		$pointer: ...
	 * @param	[type]		$listLength: ...
	 * @param	[boolean]	$useRequestForm: if true, a request form will be rendered, where a FE User has to fill out a form
	 * @return	[type]		...
	 */
 	function renderFileList($list, $resultcount, $pointer, $listLength, $useRequestForm) {
 		if(!is_array($list)){
 			//no result is given
 			return $this->pi_getLL('noDocInCat');
 		}

 		if(!intval($resultcount) || $resultcount < 1) {
			if (TYPO3_DLOG)	t3lib_div::devLog('tx_damfrontend_rendering.renderFileList: Invalid resultcount. Counter is less than 1 or null. Allowed values are integer >0','dam_frontend',2,$list);
 			return $this->pi_getLL('noDocInCat');
		}
		if(!intval($pointer) || $pointer < 1 ) $pointer = 1;
		if(!intval($listLength) || $listLength < 1 ) $listLength = $this->cObj->stdWrap($this->conf['filelist.']['defaultLength'],$this->conf['filelist.']['defaultLength.']);
		if (!isset($this->fileContent)) return $this->pi_getLL('error_renderFileList_template');


		// Optionsplit for ###FILELIST_RECORD###
		if (!isset($this->conf['marker.']['filelist_record'])) { $this->conf['marker.']['filelist_record'] = '###FILELIST_RECORD###'; }

		$filelist_record_marker = $GLOBALS['TSFE']->tmpl->splitConfArray(array('cObjNum' => $this->conf['marker.']['filelist_record']), count($list));
 		$list_Code = tsLib_CObj::getSubpart($this->fileContent,'###FILELIST###');
 		$countElement = 0;
		$rows = '';
		$cObj = t3lib_div::makeInstance('tslib_cObj');

 		foreach ($list as $elem) {
			$record_Code = tsLib_CObj::getSubpart($this->fileContent,$filelist_record_marker[$countElement]['cObjNum']);
 			// TODO: correct table-name?
 			$cObj->start($elem, 'tx_dam');
 			$countElement++;
 			$elem['count_id'] =$countElement;
 			if ($pointer>1) $elem['count_id'] = $countElement  + $pointer;
 			$markerArray = $this->recordToMarkerArray($elem, 'renderFields');
 			$markerArray =$markerArray + $this->substituteLangMarkers($record_Code);

 			// TODO changes in the content of the marker arrays
 			// @todo what is done here?
 			//$this->piVars['showUid'] = $elem['uid'];
			// TODO: $this->staticInfoObj does not exists?
			if (!is_null($this->staticInfoObj)) { $markerArray['###LANGUAGE###'] 	= $this->staticInfoObj->getStaticInfoName('LANGUAGES', $elem['language'], '', '', false); }

 			// adding Markers for links to download and single View
 			// $markerArray['###LINK_SINGLE###'] = $this->pi_linkTP_keepPiVars('<img src="'.$this->iconPath.'zoom.gif'.'" style="border-width: 0px"/>',array('showUid'=>$elem['uid'],'confirmDeleteUID'=>'','editUID'=>'','catEditUID'=>''));
			$markerArray['###LINK_SINGLE###'] = $cObj->stdWrap('<img src="'.$this->iconPath.'zoom.gif'.'" style="border-width: 0px"/>',$this->conf['renderFields.']['link_single.']);

 			// this is a field in the database, if true, then the fe user has to fill out a request form
			if ($useRequestForm==1 && $elem['tx_damfrontend_use_request_form'] == 1) {
 				$paramAnforderung = array(
 					'docID' => $elem['uid'],
 					'showRequestform' => 1
 				);
 				$markerArray['###LINK_DOWNLOAD###'] = $this->pi_linkTP('request', $paramAnforderung);

 			} else {
	 			// TODO: create IMAGE from TypoScript
	 			$markerArray['###LINK_DOWNLOAD###'] = $cObj->stdWrap('<img src="'.$this->iconPath.'clip_pasteafter.gif" style="border-width: 0px"/>', $this->conf['renderFields.']['link_download.']);
	 		}
			// TODO: Create from TypoScript
			$markerArray['###LINK_SELECT_DOWNLOAD###'] = '';
			if (is_array($this->conf['filelist.']['link_select_download.'])) {

				$markerArray['###LINK_SELECT_DOWNLOAD###'] .= '<select name="'.$this->prefixId.'['.$elem['uid'].'][convert]">';
				$i = 1;
				while (is_array($this->conf['filelist.']['link_select_download.'][$i.'.'])) {
					$markerArray['###LINK_SELECT_DOWNLOAD###'] .= $cObj->TEXT($this->conf['filelist.']['link_select_download.'][$i.'.']);
					$i++;
				}
				$markerArray['###LINK_SELECT_DOWNLOAD###'] .= '</select>';
				$markerArray['###LINK_SELECT_DOWNLOAD###'] .= '<input type="submit" name="'.$this->prefixId.'['.$elem['uid'].'][submit]" value="ok" />';
			}
 			$markerArray['###FILEICON###'] = $cObj->stdWrap('<img src="'.$this->getFileIconHref($elem['file_mime_type'],$elem['file_mime_subtype'] ).'" title="'.$elem['title'].'"  alt="'.$elem['title'].'"/>',$this->conf['renderFields.']['fileicon.']);

			//render deletion button
			if ($elem['allowDeletion']==1 AND $this->conf['enableDeletions']==1) {
				$markerArray['###BUTTON_DELETE###'] =$this->pi_linkTP_keepPiVars('<img src="'.$this->iconPath.'garbage.gif'.'" style="border-width: 0px"/>',array('showUid'=>'','confirmDeleteUID'=>$elem['uid'],'editUID'=>'','catEditUID'=>''));
			} else {
				$markerArray['###BUTTON_DELETE###'] ='';
			}
			//render edit button
			if ($elem['allowEdit']==1 AND $this->conf['enableEdits']==1) {
				$markerArray['###BUTTON_EDIT###'] =$this->pi_linkTP_keepPiVars('<img src="'.$this->iconPath.'edit_fe.gif'.'" style="border-width: 0px"/>',array('showUid'=>'','confirmDeleteUID'=>'','editUID'=>$elem['uid'],'catEditUID'=>''));
				$markerArray['###BUTTON_CATEDIT###'] =$this->pi_linkTP_keepPiVars('<img src="'.$this->iconPath.'edit_fe.gif'.'" style="border-width: 0px"/>',array('showUid'=>'','confirmDeleteUID'=>'','editUID'=>'','catEditUID'=>$elem['uid']));
			} else {
				$markerArray['###BUTTON_EDIT###'] ='';
				$markerArray['###BUTTON_CATEDIT###'] ='';
			}


 			$rows .= tslib_cObj::substituteMarkerArray($record_Code, $markerArray);
 			$sortlinks = array();
 		}
 		$content = tslib_cObj::substituteMarker($list_Code, '###FILELIST_RECORDS###', $rows);
 		$content = tslib_cObj::substituteMarker($content, '###DOWNLOAD_FORM_URL###', $this->cObj->typolink('', $this->conf['filelist.']['link_select_download.']['typolink.']));
		$content = tslib_cObj::substituteMarker($content, '###LISTLENGTH###', $listLength);
		$content = tslib_cObj::substituteMarker($content, '###TOTALCOUNT###', $resultcount);

		if (!isset($this->conf['filelist.']['form_url.']['parameter'])) {
			$this->conf['filelist.']['form_url.']['parameter'] = $GLOBALS['TSFE']->id;
		}
		$this->conf['filelist.']['form_url.']['returnLast'] = 'url';
		$content = tslib_cObj::substituteMarker($content, '###FORM_URL###', $this->cObj->typolink('', $this->conf['filelist.']['form_url.']));

		// substitute Links for Sorting
 		$record = $list[0];
 		foreach ($record as $key=>$value) {
			$content = tsLib_CObj::substituteMarker($content, '###SORTLINK_'.strtoupper($key).'###', $this->renderSortLink($key));
 		}
 		foreach (array('FILELIST_BATCH_SELECT', 'FILELIST_BATCH_GO', 'FILELIST_BATCH_CREATEZIPFILE', 'FILELIST_BATCH_SENDASMAIL', 'FILELIST_BATCH_SENDZIPPEDFILESASMAIL', 'FILELIST_BATCH_SENDFILELINK', 'FILELIST_BATCH_SENDZIPPEDFILELINK', 'FILENAME_HEADER', 'FILENAME_HEADER', 'FILETYPE_HEADER', 'CR_DATE_HEADER') as $label) {
 			$content = tsLib_CObj::substituteMarker($content, '###'.$label.'###', $this->pi_getLL($label, $label));
 		}

 		$content = tsLib_CObj::substituteMarker($content, '###FILENAME_HEADER###', $this->pi_getLL('FILENAME_HEADER'));
 		$content = tsLib_CObj::substituteMarker($content, '###FILETYPE_HEADER###', $this->pi_getLL('FILETYPE_HEADER'));
 		$content = tsLib_CObj::substituteMarker($content, '###CR_DATE_HEADER###', $this->pi_getLL('CR_DATE_HEADER'));
		$content = tslib_cObj::substituteMarker($content, '###LANGUAGE_HEADER###',$this->pi_getLL('LANGUAGE_HEADER'));
		$content = tslib_cObj::substituteMarker($content, '###OWNER_HEADER###',$this->pi_getLL('OWNER_HEADER'));
		$content = tslib_cObj::substituteMarker($content, '###CREATOR_HEADER###',$this->pi_getLL('CREATOR_HEADER'));

 		// substitute static markers
 		$this->pi_loadLL();
 		$staticMarkers['###SETROWSPERVIEW###'] = $this->pi_getLL('setRowsPerView');
 		$staticMarkers['###LABEL_COUNT###'] = $this->pi_getLL('label_Count');
		$staticMarkers =$staticMarkers + $this->substituteLangMarkers($list_Code);
 		$content = tslib_cObj::substituteMarkerArray($content, $staticMarkers);

		// substitute Links for Browseresult
		$browseresults = $this->renderBrowseResults($resultcount, $pointer, $listLength);
		$content = tsLib_CObj::substituteMarker($content, '###BROWSERESULTS###', $browseresults);
 		return $content;
 	}

	/**
	 * renders a listbrowser which divides the resultlist of the selected files in better viewable parts
	 *
	 * @param	[type]		$resultcount: ...
	 * @param	[type]		$pointer: ...
	 * @param	[type]		$listLength: ...
	 * @return	[type]		...
	 */
	function renderBrowseResults($resultcount, $pointer, $listLength) {
		$listCode = tslib_CObj::getSubpart($this->fileContent, '###BROWSERESULTLIST###');
		$listElem = tslib_CObj::getSubpart($listCode, '###BROWSERESULT_ENTRY###');
		$count = 1;
		for ($z = 0; $z <= $resultcount; $z = $z + $listLength) {
			$this->piVars['pointer'] = $z;
			if ($count == round(($pointer/$listLength)) + 1) {
				#@todo default style ersetzen
				$listElems .=  tslib_CObj::substituteMarker($listElem, '###BROWSELINK###', '<span style="border: 1px solid black">'.$this->pi_linkTP_keepPiVars($count).'</span>');
			}
			else {
				$listElems .=  tslib_CObj::substituteMarker($listElem, '###BROWSELINK###', $this->pi_linkTP_keepPiVars($count));
			}
			$count++;
		}
		$listCode = tslib_CObj::substituteSubpart($listCode, '###BROWSERESULT_ENTRY###', $listElems);
		return $listCode;
	}


	/**
	 * Renders the sortlinks
	 *
	 * @param	int		$key: ...
	 * @return	string		Html	...
	 */
	function renderSortLink($key) {
			$this->pi_loadLL();
			$content = tslib_CObj::getSubpart($this->fileContent, '###SORTLINK###');
			$this->piVars['sort_'.$key] = 'ASC';
			$content = tsLib_CObj::substituteMarker($content, '###SORTLINK_ASC###', $this->pi_linkTP_keepPiVars($this->pi_getLL('asc')));
			$this->piVars['sort_'.$key] = 'DESC';
			$content = tsLib_CObj::substituteMarker($content, '###SORTLINK_DESC###', $this->pi_linkTP_keepPiVars($this->pi_getLL('desc')));
			unset($this->piVars['sort_'.$key]);
			return $content;
	}


	/**
	 * transforms the list of selected categories to an html output
	 *
	 * @param	array		$list: list of selected elements - whole dataset
	 * @return	html		rendered category selection content element
	 */
 	function renderCatSelection($list, $treeID='', $catEditUID=0) {

 		$wholeCode = tslib_CObj::getSubpart($this->fileContent,'###CATSELECTION###');
 		$wholeCode=tslib_cObj::substituteMarker($wholeCode,'###CHOOSEN_CAT_HEADER###',$this->pi_getLL('CHOOSEN_CAT_HEADER'));
 		$listCode = '';
 		foreach ($list as $category) {
 			$listElem = tslib_CObj::getSubpart($this->fileContent,'###CATLIST###');
 			$urlVars = array(
				'tx_damfrontend_pi1[catPlus]' => null,
				'tx_damfrontend_pi1[catMinus_Rec]' => null,
				'tx_damfrontend_pi1[catMinus]' => $category['uid'],
				'tx_damfrontend_pi1[catPlus_Rec]' => null,
				'tx_damfrontend_pi1[catEquals]' => null
			);
			if ($catEditUID>0) {
				$urlVars['tx_damfrontend_pi1[catEditUID]'] =$catEditUID;
			}
			$urlVars['tx_damfrontend_pi1[treeID]'] = $treeID != '' ?   $treeID : null;
			$url = $this->cObj->getTypoLink_URL($GLOBALS['TSFE']->id,$urlVars);
 			// static markers of the list
 			$listElem = tslib_cObj::substituteMarker($listElem, '###DELETE_URL###', $url);
 			$listElem = tslib_cObj::substituteMarker($listElem, '###TITLE###', $category['title']);

 			$markerArray = $this->recordToMarkerArray($category);

 			$listCode .= tslib_cObj::substituteMarkerArray($listElem, $markerArray);
 		}
 		return $wholeCode = tslib_CObj::substituteSubpart($wholeCode, '###CATLIST###', $listCode);
 	}


	/**
	 * Renders the single View
	 *
	 * @param	array		$record: data of the single record, that shall be rendered by this function
	 * @return	void
	 */
 	function renderSingleView($record) {
 		$single_Code = tslib_CObj::getSubpart($this->fileContent,'###SINGLEVIEW###');
		// TODO: clean up
 		// Formating Timefields and filesize
 		// $record['tstamp'] = date('d.m.Y', $record['tstamp']);
 		// $record['crdate'] = date('d.m.Y', $record['crdate']);
 		#$record['file_size'] = t3lib_div::formatSize($record['file_size'],' bytes | kb| mb| gb');

 		// converting all fields in the record to marker (recordfields and markername must match)
 		$this->pi_loadLL();
 		$markerArray = $this->recordToMarkerArray($record,'SingleView');
 		$markerArray =$markerArray + $this->substituteLangMarkers($single_Code);
 		if (!is_null($this->staticInfoObj)) { $markerArray['###LANGUAGE###'] 	= $this->staticInfoObj->getStaticInfoName('LANGUAGES', $record['language'], '', '', false);}
 		$content=tslib_cObj::substituteMarkerArray($single_Code, $markerArray);
 		// TODO: we should do it with foreach on record, so new fields could be easily introduced without editing this lines
 		$content = tslib_cObj::substituteMarker($content, '###TITLE_SINGLEVIEW###',$markerArray['###TITLE###']);
 		$content = tslib_cObj::substituteMarker($content, '###CR_DATE_HEADER###',$this->pi_getLL('CR_DATE_HEADER'));
 		$content = tslib_cObj::substituteMarker($content, '###FILE_SIZE_HEADER###',$this->pi_getLL('FILE_SIZE_HEADER'));
 		$content = tslib_cObj::substituteMarker($content, '###CR_DESCRIPTION_HEADER###',$this->pi_getLL('CR_DESCRIPTION_HEADER'));
 		$content = tslib_cObj::substituteMarker($content, '###COPYRIGHT_HEADER###',$this->pi_getLL('COPYRIGHT_HEADER'));
 		$content = tslib_cObj::substituteMarker($content, '###CATEGORY_HEADER###',$this->pi_getLL('CATEGORY_HEADER'));
 		$content = tslib_cObj::substituteMarker($content, '###FILETYPE_HEADER###',$this->pi_getLL('FILETYPE_HEADER'));
 		$content = tslib_cObj::substituteMarker($content, '###LINK_HEADER###',$this->pi_getLL('LINK_HEADER'));
 		$content = tslib_cObj::substituteMarker($content, '###LANGUAGE_HEADER###',$this->pi_getLL('LANGUAGE_HEADER'));
 		$content = tslib_cObj::substituteMarker($content, '###TITLE_SINGLEVIEW_HEADER###',$this->pi_getLL('TITLE_SINGLEVIEW_HEADER'));
 		return $content;
 	}


	/**
	 * Renders an error message
	 *
	 * @param	string		$errormsg: ...
	 * @return	[type]		...
	 */
 	function renderError($errormsg = 'default', $customMessage="",$customMessage2="" ) {
 		$this->pi_loadLL();

 		switch ($errormsg) {
 			case 'noSingleID':
 				$message = $this->pi_getLL('noSingleID');
 				break;
 			case 'noDocInCat':
 				$message = $this->pi_getLL('noDocInCat');
 				break;
 			case 'noCatSelected':
 				$message = $this->pi_getLL('noCatSelected');
 				break;
 			case 'noUserLoggedIn':
 				$message = $this->pi_getLL('noUserLoggedIn');
 				break;
 			case 'noFilterStates':
 				$message = $this->pi_getLL('noFilterStates');
 				break;
 			case 'uploadFormFieldError':
 				$message = $this->pi_getLL('uploadFormFieldError') . $customMessage . ' '. $this->pi_getLL('uploadFormFieldErrorLength') . ' ' . $customMessage2 ;
 				break;
 			case 'custom':
				// TODO: <br> in strip_tags? makes no sense - perhaps stdWrap? where it is used?
 				$message = strip_tags($customMessage. ' <br>'. $customMessage2);
 				break;
 			default:
 				$message = $this->pi_getLL('standardErrorMessage');
 		}

 		$content = tslib_CObj::getSubpart($this->fileContent,'###ERROR###');
 		$content = tslib_CObj::substituteMarker($content, '###ERRORMESSAGE###', $message);
 		$content = tslib_CObj::substituteMarker($content, '###ERROR_HEADER###', $this->pi_getLL('error_header'));
 		return $content;
 	}

	/**
	 * converts an associative array to an Marker Array in the form ### $KEY ### => value
	 *
	 * @param	array		$record: array that shall be converted
	 * @param   string		$scope: defines which TypoScript sub-configuration
	 * should be used
	 * @return	array		Markerarray ready for substitution
	 */
 	function recordToMarkerArray($record, $scope = 'default') {

 		if (!is_array($record))  { die ('Parameter error in class.tx_damfrontend_rendering in function recordToMarkerArray: $record is no Array. Please inform your administrator.'); }

			// we should be able to have full TypoScript Power
	 	$cObj = t3lib_div::makeInstance('tslib_cObj');
 			// FIXME: table should not be hardcoded
		$cObj->start($record, 'tx_dam');
		if (!is_array($this->conf[$scope.'.'])) { $this->conf[$scope.'.'] = array(); }

		foreach ($record as $key=>$value) {
			if ('' == $key) continue; // empty key
			if (!is_array($this->conf[$scope.'.'][$key.'.'])) { $this->conf[$scope.'.'][$key.'.'] = array(); }
				// htmlSpecialChars = 1 is default - it has to be disabled via htmlSpecialChars = 0
			if (!isset($this->conf[$scope.'.'][$key.'.']['htmlSpecialChars'])) {
				$this->conf[$scope.'.'][$key.'.']['htmlSpecialChars'] = 1;
			}
			$markerArray['###'.strtoupper($key).'###'] = $cObj->stdWrap((string)$value, $this->conf[$scope.'.'][$key.'.']);
 		}
 		return $markerArray;
 	}


	/**
	 * Renders the filter view:
	 *
	 * @param	[array]		$filterArray: contains the filters that are set, can also contain a list of fe-users for the selector box ($listOfCreators)
	 * @param	[array]		$errorArray: ...
	 * @return	[type]		...
	 */
 	function renderFilterView($filterArray, $errorArray = '') {
 		$formCode  = tslib_CObj::getSubpart($this->fileContent, '###FILTERVIEW###');

 		// filling fields with url - vars
 		$markerArray  = $this->recordToMarkerArray($filterArray);
		$markerArray =$markerArray + $this->substituteLangMarkers($formCode);
 		// error handling
 		$markerArray['###ERROR_TO_DATE###'] = $errorArray['error_to_date'] ? $this->pi_getLL('error_renderFilterView_date_to') : '';
 		$markerArray['###ERROR_FROM_DATE###'] = $errorArray['error_from_date'] ? $this->pi_getLL('error_renderFilterView_date_from') : '';

 		//generating filetype list
 		$markerArray['###FILETYPE_LIST###'] = $this->renderFileTypeList($filterArray['filetype']);

 		// inserting static markers
 		$this->pi_loadLL();
 		$markerArray['###SET_FILTER###'] = $this->pi_getLL('setFilter');
 		$markerArray['###RESET_FILTER###'] = $this->pi_getLL('resetFilter');
 		$markerArray['###FILETYPE###'] = $this->pi_getLL('filetype');
 		$markerArray['###LABEL_TODATE###'] = $this->pi_getLL('label_todate');
 		$markerArray['###LABEL_FROMDATE###'] = $this->pi_getLL('label_fromdate');
 		$markerArray['###LABEL_SEARCHWORD###'] = $this->pi_getLL('label_searchword');
 		$markerArray['###LABEL_SEARCHOPS###'] = $this->pi_getLL('label_searchops');
 		$markerArray['###LABEL_RESETFILTER###'] = $this->pi_getLL('label_resetFilter');
 		$markerArray['###NOSELECTION###'] = $this->pi_getLL('noselection');
 		$markerArray['###LABEL_FILETYPE###'] = $this->pi_getLL('label_filetype');
 		$markerArray['###LABEL_SEARCHOPS###'] = $this->pi_getLL('label_searchops');
 		$markerArray['###LABEL_NO_CAT###'] = $this->pi_getLL('LABEL_NO_CAT');
 		$markerArray['###PDFFILE###'] = $this->pi_getLL('pdffile');
 		$markerArray['###WORDFILE###'] = $this->pi_getLL('wordfile');
 		$markerArray['###JPEGFILE###'] = $this->pi_getLL('jpegfile');
 		$markerArray['###GIFFILE###'] = $this->pi_getLL('giffile');
		$markerArray['###LABEL_CREATOR###'] = $this->pi_getLL('CREATOR_HEADER');
		$markerArray['###LABEL_LANGUAGE###'] = $this->pi_getLL('LANGUAGE_HEADER');
		$markerArray['###OWNER_HEADER###'] = $this->pi_getLL('OWNER_HEADER');
		if (is_array($filterArray['listOfOwners'])) {
			$markerArray['###DROPDOWN_OWNER###'] = $this->renderOwnerSelector($filterArray['listOfOwners']);
		} else {
			$markerArray['###DROPDOWN_OWNER###']='';
		}

		$markerArray['###DROPDOWN_LANGUAGE###'] = $this->renderLanguageSelector($filterArray['LanguageSelector']);
 		if (!isset($this->conf['filterview.']['form_url.']['parameter'])) {
			$this->conf['filterview.']['form_url.']['parameter'] = $GLOBALS['TSFE']->id;
		}
		$this->conf['filterview.']['form_url.']['returnLast'] = 'url';
		$markerArray['###FORM_URL###'] = $this->cObj->typolink('', $this->conf['filterview.']['form_url.']);

 		$formCode = tslib_cObj::substituteMarkerArray($formCode, $markerArray);
 		return $formCode;
 	}


	/**
	 * Renders the filetype list
	 *
	 * @param	[string]		$filetype: ...
	 * @return	[type]		...
	 */
	function renderFileTypeList($filetype) {
		if ($filetype == '') $filetype = 'noselection';

		// TODO: use DAM - functions - there are much more file types possible
		$this->pi_loadLL();
		$filetypeArray  = array(
			'pdf' => array (
				label => $this->pi_getLL('pdffile')
			),
			'word' => array (
				label => $this->pi_getLL('wordfile')
			),
			'jpg' => array (
				label => $this->pi_getLL('jpegfile')
			),
			'gif' => array (
				label => $this->pi_getLL('giffile')
			),
			'zip' => array (
				label => $this->pi_getLL('zipfile')
			),
			'eps' => array(
				label => $this->pi_getLL('epsfile')
			),
			'tif' => array(
				label => $this->pi_getLL('tifffile')
			),
			'noselection' => array (
				label => $this->pi_getLL('noselection')
			)
		);

		$filetypeArray[$filetype]['set'] = 1;
		$content = '<select name="filetype">';
		foreach ($filetypeArray as $type => $arr) {
			$arr['set'] == 1 ? $sel = ' selected="selected"': $sel='';
			if ($type == 'noselection') $type = '';
 			$content .= '<option value="'.$type.'"'.$sel.'>'.$arr['label'].'</option>';
		}
		$content .= '</select>';
		return $content;
	}


	/**
	 * Renders a Filter Error
	 *
	 * @param	[type]		$errorCode: ...
	 * @return	[type]		...
	 * @todo 	Function not finished
	 */
 	function renderFilterError($errorCode) {

 	}


	/**
	 * renderUploadForm
	 * @param array userInput If an error happend and the form must be rendered again
	 * @return	[string]	the html code	...
	 *
	 */
 	function renderUploadForm() {
		$this->pi_loadLL();
		$formCode  = tslib_CObj::getSubpart($this->fileContent, '###UPLOADFORM###');
		$markerArray['###BUTTON_UPLOAD###'] = $this->pi_getLL('BUTTON_UPLOAD');
		$markerArray['###TITLE_FILEUPLOAD###'] = $this->pi_getLL('TITLE_FILEUPLOAD');
		$markerArray['###LABEL_FILE###'] =  $this->pi_getLL('LABEL_FILE');
		if (!isset($this->conf['filterview.']['form_url.']['parameter'])) {
			$this->conf['filterview.']['form_url.']['parameter'] = $GLOBALS['TSFE']->id;
		}
		$this->conf['filterview.']['form_url.']['returnLast'] = 'url';
		$markerArray['###FORM_URL###'] = $this->cObj->typolink('', $this->conf['filterview.']['form_url.']);
		$markerArray =$markerArray + $this->substituteLangMarkers($formCode);

		return tslib_cObj::substituteMarkerArray($formCode, $markerArray);
 	}


	/**
	 * renderFilterList: Allow a fe user to store a selection of files
	 *
	 * @param	[type]		$filterList: ...
	 * @return	[type]		...
	 * @todo renderFilterList is not finished yet
	 */
 	function renderFilterList($filterList) {
 		if (!is_array($filterList)) die ('parameter error at renderFilterList');
 		$formCode  = tslib_CObj::getSubpart($this->fileContent, '###FILTERLIST###');
 		$listElem = tslib_CObj::getSubpart($formCode, '###FILTERLIST_ELEM###');

 		if (!is_array($filterList)) {
	 		foreach ($filterList as $filter) {
	 			$markerArray  = $this->recordToMarkerArray($filter);
	 			$markerArray =$markerArray + $this->substituteLangMarkers($listElem);
	 			$listCode .= tslib_CObj::substituteMarkerArray($listElem, $markerArray);
	 		}
 		}
 		$formCode  = tslib_CObj::substituteSubpart($this->fileContent, '###FILTERLIST_ELEM###', $listCode);
 		return $formCode;
 	}


	/**
	 * Renders the Filter Creation form
	 *
	 * @return	[type]		...
	 * @todo complete this function
	 */
 	function renderFilterCreationForm() {
 		$this->pi_loadLL();
 		$markerArray = array();
 		$content = tslib_CObj::getSubpart($this->fileContent, '###NEWFILTER###');

 		$markerList = array('SAVE', 'CANCEL', 'FILTER_TITLE_LABEL', 'FILTER_DESCRIPTION_LABEL');
 		foreach ($markerList as $marker) {
 			$markerArray[$marker] = $this->cObj->stdWrap($this->pi_getLL($marker, $marker, false),$this->conf['newfilter.'][strtolower($marker).'.']);
 		}

 					// FORM URL
 		if (!isset($this->conf['newfilter.']['form_url.']['parameter'])) {
			$this->conf['newfilter.']['form_url.']['parameter'] = $GLOBALS['TSFE']->id;
		}
		$this->conf['newfilter.']['form_url.']['returnLast'] = 'url';
		$markerArray['FORM_URL'] = $this->cObj->typolink('', $this->conf['newfilter.']['form_url.']);

 		$content = tslib_CObj::substituteMarkerArray($content, $markerArray, '###|###');

 		return $content;
 	}


	/**
	 * Renders a filter creation creation link
	 *
	 * @return	[type]		...
	 */
 	function renderFilterCreationLink() {
 		$content = tslib_CObj::getSubpart($this->fileContent, '###NEWFILTER_LINK###');

 		if (!isset($this->conf['newfilter_link.']['form_url.']['parameter'])) {
			$this->conf['newfilter_link.']['form_url.']['parameter'] = $GLOBALS['TSFE']->id;
		}
		$this->conf['newfilter_link.']['form_url.']['returnLast'] = 'url';
		$content = tslib_cObj::substituteMarker($content, '###FORM_URL###', $this->cObj->typolink('', $this->conf['newfilter_link.']['form_url.']));

 		return $content;
 	}

 	/**
 * Returns the reference to the image file associated to given mime types.
 *
 * @param	[string]		$mimeType: mime type.
 * @param	[string]		$mimeSubType: mime sub type.
 * @return	[string]		href attribute value, pointing to an image file.
 */
  function getFileIconHref($mimeType, $mimeSubType) {
    $rootKey = 'mediaTypes.';
    $mimeType .= '.';
    if (!array_key_exists($rootKey, $this->conf)) {
      return "#";
    }

    $mimeTypesConf = $this->conf[$rootKey];

    if (!array_key_exists($mimeType, $mimeTypesConf)) {
      $mimeType = 'DEFAULT.';
    }

    if (!array_key_exists($mimeSubType, $mimeTypesConf[$mimeType])) {
      $mimeSubType = 'DEFAULT';
    }

    $relPath = $this->getIconBaseAddress();
    $relPath .= $mimeTypesConf[$mimeType][$mimeSubType];
    return $relPath;
  }

	/**
	 * Returns the path to Icons
	 *
	 * @return	[type]		...
	 */
  function getIconBaseAddress() {
    if($this->iconBaseAddress) {
      return $this->iconBaseAddress;
    } else {
      return t3lib_extMgm::siteRelPath($this->extKey) . 'res/ico/';
    }
  }


//  	/**
//  	 *
//  	 * rendert das Formular zur Eingabe der Anforderung f�r eine SI
//  	 *
//  	 *
//  	 */
//	function renderRequest($formData, $docArray, $errorArray=''){
//		$docArray = array_merge($formData, $docArray);
//		$formCode  = tslib_CObj::getSubpart($this->fileContent, '###ANFORDERUNG###');
//		$markerArray  = $this->recordToMarkerArray($docArray);
//		$markerArray['###FORM_TARGET###'] = $this->pi_getPageLink($this->cObj->data['pid'],null, array('sendRequestform' => 1));
//
//		// Es traten fehler bei den Formulareingaben auf
//		foreach ($formData as $name => $data) {
//			$markerArray['###ERROR_'.strtoupper($name).'###'] = '';
//		}
//		if (is_array($errorArray)) {
//			if ($errorArray['error_email']) {
//				$markerArray['###ERROR_EMAIL###'] = 'Die Email Adresse wurde nich korrekt angegeben';
//			}
//			if ($errorArray['error_plz']) {
//				$markerArray['###ERROR_PLZ###'] = 'Die Postleitzahl fehlt noch';
//			}
//			if ($errorArray['error_ort']) {
//				$markerArray['###ERROR_ORT###'] = 'Der Ort fehlt noch';
//			}
//			if ($errorArray['error_vorname']) {
//				$markerArray['###ERROR_VORNAME###'] = 'Der Vorname fehlt noch';
//			}
//			if ($errorArray['error_nachname']) {
//				$markerArray['###ERROR_NACHNAME###'] = 'Der Nachname fehlt noch';
//			}
//			if ($errorArray['error_anschrift']) {
//				$markerArray['###ERROR_ANSCHRIFT###'] = 'Die Anschrift fehlt noch';
//			}
//		}
//
//		// F�ge die bisher eingegebenen Daten ein
//		foreach($formData as $name => $data) {
//
//			$markerArray['###'.strtoupper($name).'###'] = $data;
//		}
//		$markerArray['###BACK_URL###'] = $this->pi_GetPageLink($this->cObj->data['pid']);
//
//		$formCode = tslib_cObj::substituteMarkerArray($formCode, $markerArray);
//		return $formCode;
//	}
//
//	/*
//	 *
//	 */
//	function renderMailMessage($formData, $docData)
//	{
//		$formCode  = tslib_CObj::getSubpart($this->fileContent, '###MAIL_ANFORDER###');
//		$wholeData = array_merge($formData, $docData);
//		$markerArray = $this->recordToMarkerArray($wholeData);
//		$formCode = tslib_cObj::substituteMarkerArray($formCode, $markerArray);
//		return $formCode;
//	}
//
//	/*
//	 *
//	 */
//	function renderRequestUser($docArray) {
//		$formCode = tslib_CObj::getSubpart($this->fileContent, '###ANFORDERUNG_USER###');
//		$markerArray = $this->recordToMarkerArray($docArray);
//		$markerArray['###FORM_URL###'] = $this->pi_getPageLink($this->cObj->data['pid'],null, array('sendRequestform' => 1, 'docID' => $docArray['uid']));
//		$markerArray['###BACK_URL###'] = $this->pi_GetPageLink($this->cObj->data['pid']);
//		$formCode = tslib_cObj::substituteMarkerArray($formCode, $markerArray);
//		return $formCode;
//	}
//
//	/*
//	 */
//	function renderAnforderMailUser($userData, $docData) {
//
//		$formCode  = tslib_CObj::getSubpart($this->fileContent, '###MAIL_ANFORDER_USER###');
//		$wholeData = array_merge($userData, $docData);
//		$markerArray = $this->recordToMarkerArray($wholeData);
//		if (t3lib_extMgm::isLoaded('sr_feuser_register')) {
//			$markerArray['###NAME###'] = $wholeData['first_name'] + $wholeData['last_name'];
//		}
//		$formCode = tslib_cObj::substituteMarkerArray($formCode, $markerArray);
//		return $formCode;
//	}

	/*
	 * Renders the categorisation form
	 *
 	 * @param	[array] 	$docdata: 		meta data of the doc, that should be imported to the dam
 	 * @param	[array] 	$docdata: 		meta data of the doc, that should be imported to the dam
	 * @param	[array]		$selectedCats: 	selected categories
	 * @return	[string]	$return:		html of the form
	 * @author  martin baum
	 *
	 */
	function renderCategorisationForm($docData,$selectedCats='',$uploadCats,$versioning='') {
		$this->pi_loadLL();
		$formCode  = tslib_CObj::getSubpart($this->fileContent, '###CATEGORISATION###');

		// initalisation of the treeview
		$tree = t3lib_div::makeInstance('tx_damfrontend_categorisationTree');
		$tree->MOUNTS = explode(',',$uploadCats);
		$tree->init(-1);
		$tree->cObj = $this->cObj;
		if ($this->piVars['catEditUID']>0) {
			$tree->piVars = array('tx_damfrontend_pi1[catEditUID]'=>$docData['uid']);
		}
		$tree->title = $this->pi_getLL('CATEGORISATION_TREE_NAME');
		$markerArray = $this->recordToMarkerArray($docData);

		$markerArray['###BUTTON_CONFIRM###'] = '<input name="catOK" type="submit" value="'.$this->pi_getLL('BUTTON_CONFIRM').'">';
		$markerArray['###CANCEL###'] = $this->pi_linkTP_keepPiVars('<img src="'.$this->iconPath.'turn_left.gif'.'" style="border-width: 0px"/> &nbsp;'.$this->pi_getLL('BUTTON_BACK'),   array('catEditUID'=>''));

		$markerArray['###TITLE_FILEUPLOAD###'] = $this->pi_getLL('TITLE_FILEUPLOAD');
		$markerArray['###LABEL_FILE###'] =  $this->pi_getLL('LABEL_FILE');
		$markerArray['###LABEL_TITLE###'] =  $this->pi_getLL('LABEL_TITLE');
		$markerArray['###LABEL_COPYRIGHT###'] = $this->pi_getLL('LABEL_COPYRIGHT');
		$markerArray['###LABEL_AUTHOR###'] =  $this->pi_getLL('LABEL_AUTHOR');
		$markerArray['###LABEL_DESCRIPTION###'] =  $this->pi_getLL('LABEL_DESCRIPTION');
		$markerArray['###VALUE_TITLE###']= $docData['title'];
		$markerArray['###VALUE_COPYRIGHT###']= $docData['copyright'];
		$markerArray['###VALUE_AUTHOR###']= $docData['creator'];
		$markerArray['###VALUE_DESCRIPTION###']= $docData['description'];
		$markerArray['###LABEL_LANGUAGE###']= $this->pi_getLL('LANGUAGE_HEADER');
		if (!is_null($this->staticInfoObj)) { $markerArray['###VALUE_LANGUAGE###']= $this->staticInfoObj->getStaticInfoName('LANGUAGES', $docData['language'], '', '', false); }
		$markerArray =$markerArray + $this->substituteLangMarkers($formCode);
		$markerArray['###CATTREE###'] = $tree->getBrowsableTree();
		$markerArray['###CATLIST###'] = '';
		$markerArray['###CATEGORISATION_TEXT_HEADER###']=$this->pi_getLL('CATEGORISATION_TEXT_HEADER');
		$markerArray['###CATEGORISATION_TEXT_TITLE###']=$this->pi_getLL('CATEGORISATION_TEXT_TITLE');
		$markerArray['###CATEGORISATION_TEXT_DESCRIPTION###']=$this->pi_getLL('CATEGORISATION_TEXT_DESCRIPTION');
		$markerArray['###CATEGORISATION_TEXT_SEND###']=$this->pi_getLL('CATEGORISATION_TEXT_SEND');


		if (is_array($selectedCats)) {
			$catCode = $this->renderCatSelection($selectedCats, -1,$this->piVars['catEditUID']);
		}
		else {
			$catCode = $this->renderError('noCatSelected');
			$markerArray['###CANCEL###'] = '';
		}

		$markerArray['###HIDDENFIELDS###'] = '';
		if (isset($versioning))  {
			if ($versioning=='editCats') {
				#hide confirm button if edit is active (otherwise the user would see the message 'upload successful')
				$markerArray['###BUTTON_CONFIRM###'] ='';
			}
			else {
				$markerArray['###HIDDENFIELDS###'] = '<input type="hidden" name="version_method" value="'.$versioning.'" />';
			}
		}
		$markerArray['###CATLIST###'] = $catCode;
		$formCode = tslib_cObj::substituteMarkerArray($formCode, $markerArray);
		return $formCode;
	}

	/**
	 * renderUploadSuccess
	 * @author stefan
	 * @return	[string]	$return:		html of the form
	 */
	function renderUploadSuccess() {
		$this->pi_loadLL();
		return $this->pi_getLL('UPLOAD_SUCCESS');
	}

	/**
	 * message to confirm the deletion of a file
	 * @author stefan
	 * @param array $record Array of the dam record which should be deletec
	 * @return	[string]	$return:		html of the form
	 */
	function renderFileDeletion($record) {

		$single_Code = tslib_CObj::getSubpart($this->fileContent,'###FILE_DELETION###');
 		// Formating Timefields and filesize
 		$record['tstamp'] = date('d.m.Y', $record['tstamp']);
 		$record['crdate'] = date('d.m.Y', $record['crdate']);
 		#$record['file_size'] = t3lib_div::formatSize($record['file_size'],' bytes | kb| mb| gb');

 		// converting all fields in the record to marker (recordfields and markername must match)
 		$markerArray = $this->recordToMarkerArray($record);
 		$markerArray =$markerArray + $this->substituteLangMarkers($single_Code);
 		$this->pi_loadLL();
 		$content=tslib_cObj::substituteMarkerArray($single_Code, $markerArray);
 		$content = tslib_cObj::substituteMarker($content, '###TITLE_SINGLEVIEW###',$record['title']);
 		$content = tslib_cObj::substituteMarker($content, '###CR_DATE_HEADER###',$this->pi_getLL('CR_DATE_HEADER'));
 		$content = tslib_cObj::substituteMarker($content, '###FILE_SIZE_HEADER###',$this->pi_getLL('FILE_SIZE_HEADER'));
 		$content = tslib_cObj::substituteMarker($content, '###CR_DESCRIPTION_HEADER###',$this->pi_getLL('CR_DESCRIPTION_HEADER'));
 		$content = tslib_cObj::substituteMarker($content, '###COPYRIGHT_HEADER###',$this->pi_getLL('COPYRIGHT_HEADER'));
 		$content = tslib_cObj::substituteMarker($content, '###FILETYPE_HEADER###',$this->pi_getLL('FILETYPE_HEADER'));
 		$content = tslib_cObj::substituteMarker($content, '###LINK_HEADER###',$this->pi_getLL('LINK_HEADER'));
 		$content = tslib_cObj::substituteMarker($content, '###TITLE_SINGLEVIEW_HEADER###',$this->pi_getLL('TITLE_SINGLEVIEW_HEADER'));
 		$content = tslib_cObj::substituteMarker($content, '###LABEL_WARNING###',$this->pi_getLL('LABEL_WARNING'));
		$content = tslib_cObj::substituteMarker($content, '###MESSAGE_DELETION_WARNING###',$this->pi_getLL('MESSAGE_DELETION_WARNING'));
		$content = tslib_cObj::substituteMarker($content, '###CANCEL_DELETION_UID###', $this->pi_linkTP_keepPiVars('<img src="'.$this->iconPath.'turn_left.gif'.'" style="border-width: 0px"/> &nbsp;'.$this->pi_getLL('BUTTON_BACK'),   array('showUid'=>$record['uid'],'confirmDeleteUID'=>'')));
		$content = tslib_cObj::substituteMarker($content, '###CONFIRM_DELETION_UID###',$this->pi_linkTP_keepPiVars('<img src="'.$this->iconPath.'garbage.gif'.'" style="border-width: 0px"/> &nbsp;'.$this->pi_getLL('BUTTON_CONFIRM'),		array('showUid'=>''            ,'deleteUID'=>$record['uid'], 'confirmDeleteUID'=>'')));
 		return $content;
 }

	/**
	 * Message if the deletion was successful
	 * @author stefan
	 * @return	[string]	$return:		html of the form
	 */
	function renderFileDeletionSuccess() {
		$this->pi_loadLL();
		$subpart = tslib_CObj::getSubpart($this->fileContent,'###MESSAGE###');
 		$markerArray['###LABEL_MESSAGE###']=$this->pi_getLL('LABEL_MESSAGE');
 		$markerArray['###MESSAGE_TEXT###']=$this->pi_getLL('MESSAGE_TEXT_DELETION_SUCESS');
 		$markerArray['###BUTTON_NEXT###']= $this->pi_linkTP_keepPiVars('<img src="'.$this->iconPath.'icon_ok2.gif'.'" style="border-width: 0px"/> &nbsp;'.$this->pi_getLL('BUTTON_NEXT'),array('showUid'=>'','deleteUID'=>'', 'confirmDeleteUID'=>''));
 		$content=tslib_cObj::substituteMarkerArray($subpart, $markerArray);
		return $content;
	}

	/**
	 * finds markers (###LLL:[markername]###) in given template Code
	 * @param		string		$templCode		the template code in which the markers should be searched for
	 * @return		array						the found language markers with translation text
	 */
	function substituteLangMarkers($templCode) {
		global $LANG;
		$langMarkers = array();
		if ($this->langFile != '') {
			$aLLMarkerList = array();
			preg_match_all('/###LLL:.+?###/Ssm', $templCode, $aLLMarkerList);

			if ($this->conf['debug']==1) {
				t3lib_div::debug('in class.tx_damfrontend_rendering.php / Found language markers: //');
				t3lib_div::debug($aLLMarkerList);
			}

			foreach($aLLMarkerList[0] as $LLMarker){
				$llKey =  strtoupper(substr($LLMarker,7,strlen($LLMarker)-10));
				$marker = $llKey;
				$langMarkers['###LLL:'.strtoupper($marker).'###'] = trim($GLOBALS['TSFE']->sL('LLL:'.$this->langFile.':'.$llKey));
			}
		}
	    return $langMarkers;
	}

	/**
	 *
	 * Renders a Language Selector (optional filtered via TYPOSCRIPT)
	 * TS Example: plugin.tx_damfrontend_pi1.allowedLanguages = EN,DE
	 * @author stefan
	 *
	 *	@return string HTML of the Selektorbox
	 */
	function renderLanguageSelector ($currentLanguage ='') {
		if (is_null($this->staticInfoObj)) { return ''; }
		$whereLanguages='';
		$languagesArray = array();
		$languagesArray = explode(",",$this->conf['allowedLanguages']);
		// building where clause to limit the languages
		if (count($languagesArray)>0){
			foreach ($languagesArray as $language) {
				$languages .= '"'.$language.'",';
			}
			//removing the last comma
			$languages = rtrim($languages,',');
			$whereLanguages = 'lg_iso_2 IN ( '.$languages.')';
		}
		$mergeArray = array('nosel'=>'---');
		#t3lib_div::debug( $this->staticInfoObj->buildStaticInfoSelector('LANGUAGES', 'LanguageSelector','',$currentLanguage,'','','','',$whereLanguages,'',1,$mergeArray));

		return  $this->staticInfoObj->buildStaticInfoSelector('LANGUAGES', 'LanguageSelector','',$currentLanguage,'','','','',$whereLanguages,'',1,$mergeArray);
	}

	/**
	 * @author stefan
	 *
	 *	@param array $listOfCreators holds a List of creators (sorted alphabetically, in column 2 there is a value selected)
	 *	@return string html of the selector box
	 */
	function renderOwnerSelector ($listOfOwners) {
		if (is_array($listOfOwners)) {
			foreach ($listOfOwners as $owner) {
				if ($owner['selected'] == 1) {
					$sel = ' selected="selected"';
					$selected = true;
				} else {
					$sel='';
				}
				if ($owner['name']=='') {
					$feUserName =$owner['username'];
				} else {
					$feUserName =$owner['name'];
				}

	 			$content .= '<option value="'.$owner['uid'].'"'.$sel.'>'.$feUserName.'</option>';
			}
			if ($selected==false ){
				$sel = ' selected="selected"';
			} else {
				$sel='';
			}
			$content = '<option value="noselection"'.$sel.'></option>'.$content;
			$content .= '</select>';
			$content = '<select name="owner">'.$content;
		}
		else {
			$content ='<label>'.$this->pi_getLL('NO_OWNERS').'</label>';
		}
		return $content;
	}



	function renderVersioningForm() {
		$this->pi_loadLL();
		$subpart = tslib_CObj::getSubpart($this->fileContent,'###FORM_VERSIONING###');
		$markerArray = array();
		$markerArray['###VERSIONING_FILE_EXISTS###'] =  $this->pi_getLL('VERSIONING_FILE_EXISTS');
		$markerArray['###VERSIONING_OVERWRITES###'] =  $this->pi_getLL('VERSIONING_OVERWRITES');
		$markerArray['###VERSIONING_NEW_VERSION###'] = $this->pi_getLL('VERSIONING_NEW_VERSION');
		$markerArray['###HIDDENFIELDS###'] = '';

 		$content=tslib_cObj::substituteMarkerArray($subpart, $markerArray);
		return $content;
	}


	/**
	 * Renders the edition form. A fe_user can edit the metadata of a file
	 *
	 * 	@author stefan
	 *	@version 1
	 *
	 *	@param array $record array of the dam record
	 *	@return string html of the edit form
	 */
	function renderFileEdit($record){
		$this->pi_loadLL();

		$formCode  = tslib_CObj::getSubpart($this->fileContent, '###EDITFORM###');
		$markerArray['###BUTTON_CONFIRM###'] = $this->pi_getLL('BUTTON_CONFIRM');
		$markerArray['###TITLE_FILEUPLOAD###'] = $this->pi_getLL('TITLE_FILEUPLOAD');
		$markerArray['###LABEL_FILE###'] =  $this->pi_getLL('LABEL_FILE');
		$markerArray['###LABEL_TITLE###'] =  $this->pi_getLL('LABEL_TITLE');
		$markerArray['###LABEL_COPYRIGHT###'] = $this->pi_getLL('LABEL_COPYRIGHT');
		$markerArray['###LABEL_AUTHOR###'] =  $this->pi_getLL('LABEL_AUTHOR');
		$markerArray['###LABEL_DESCRIPTION###'] =  $this->pi_getLL('LABEL_DESCRIPTION');
		$markerArray['###VALUE_TITLE###']= $record['title'];
		$markerArray['###VALUE_COPYRIGHT###']= $record['copyright'];
		$markerArray['###VALUE_AUTHOR###']= $record['creator'];
		$markerArray['###VALUE_DESCRIPTION###']= $record['description'];
		$markerArray['###LABEL_LANGUAGE###']= $this->pi_getLL('LANGUAGE_HEADER');
		$markerArray['###VALUE_LANGUAGE###']=$this->renderLanguageSelector($record['language']);
		$hiddenFields = '<input type="hidden" name="saveUID" value="'.$record['uid'].'" />';
 		$markerArray['###HIDDENFIELDS###'] = $hiddenFields;
		$markerArray =$markerArray + $this->substituteLangMarkers($formCode);
		if ($GLOBALS['TSFE']->fe_user->getKey('ses','saveID')>0) {
			$formCode = tslib_cObj::substituteMarker($formCode, '###CANCEL###','');
		}
		else {
			$formCode = tslib_cObj::substituteMarker($formCode, '###CANCEL###', $this->pi_linkTP_keepPiVars('<img src="'.$this->iconPath.'turn_left.gif'.'" style="border-width: 0px"/> &nbsp;'.$this->pi_getLL('BUTTON_BACK'),   array('editUID'=>'')));
		}
		return tslib_cObj::substituteMarkerArray($formCode, $markerArray);
	}

 }
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/dam_frontend/frontend/class.tx_damfrontend_rendering.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/dam_frontend/frontend/class.tx_damfrontend_rendering.php']);
}

?>
