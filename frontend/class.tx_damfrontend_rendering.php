<?php
require_once(PATH_tslib.'class.tslib_content.php');
require_once(PATH_tslib.'class.tslib_pibase.php');
require_once(t3lib_extMgm::extPath('dam_frontend').'/frontend/class.tx_damfrontend_categorisationTree.php');

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

 	var $filePath; // contains the physical path to the filereference
 	var $fileContent; // HTML content of the given filepath

	var $iconBaseAddress = null;

	/**
	 * inits this class (loading the locallang file)
	 *
	 * @return	[void]		...
	 */
	function init() {
		$this->pi_loadLL();
		$this->iconBaseAddress = $this->conf['iconBaseAddress'];
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

 		if(!is_array($list)) die($this->pi_getLL('error_renderFileList_emptyArray'));
		if(!intval($resultcount) || $resultcount < 1) die($this->pi_getLL('error_renderFileList_resultcount'));
		if(!intval($pointer) || $pointer < 1 ) $pointer = 1;
		if(!intval($listLength) || $listLength < 1 ) $listLength = $this->cObj->stdWrap($this->conf['filelist.']['defaultLength'],$this->conf['filelist.']['defaultLength.']);
		if (!isset($this->fileContent)) return $this->pi_getLL('error_renderFileList_template');

 		// reading the filecontent just one time
 		$record_Code = tslib_CObj::getSubpart($this->fileContent,'###FILELIST_RECORD###');
 		$list_Code = tsLib_CObj::getSubpart($this->fileContent,'###FILELIST###');
 		$countElement = 1;


 		foreach ($list as $elem) {
			$elem['count_id'] = $countElement++;

 			$markerArray = $this->recordToMarkerArray($elem, 'renderFields');
 			// changes in the content of the marker arrays @todo what is done here?
 			$this->piVars['showUid'] = $elem['uid'];

 			// adding Markers for links to download and single View
 			$markerArray['###LINK_SINGLE###'] = $this->pi_linkTP_keepPiVars('<img src="'.$this->iconPath.'zoom.gif'.'" style="border-width: 0px"/>');



 			// this is a field in the database, if true, then the fe user has
 			// to fill out a request form
 			if ($useRequestForm==1 ) {
 				if ($elem['tx_damfrontend_use_request_form'] == 1) {
	 				$paramAnforderung = array(
	 					'docID' => $elem['uid'],
	 					'showRequestform' => 1
	 				);
	 				$markerArray['###LINK_DOWNLOAD###'] = $this->pi_linkTP('request', $paramAnforderung);
	 			}
	 			else {
	 				$markerArray['###LINK_DOWNLOAD###'] = '<a href="typo3conf/ext/dam_frontend/pushfile.php?docID='.$elem['uid'].'" ><img src="'.$this->iconPath.'clip_pasteafter.gif" style="border-width: 0px"/></a>';
	 			}
 			}
 			else {
 				$markerArray['###LINK_DOWNLOAD###'] = '<a href="typo3conf/ext/dam_frontend/pushfile.php?docID='.$elem['uid'].'" ><img src="'.$this->iconPath.'clip_pasteafter.gif" style="border-width: 0px"/></a>';
 			}

 			$markerArray['###FILEICON###'] = '<img src="'.$this->getFileIconHref($elem['file_mime_type'],$elem['file_mime_subtype'] ).'" title="'.$elem['title'].'"  alt="'.$elem['title'].'"/>';



 			$newcontent = $record_Code;
 			$rows .= tslib_cObj::substituteMarkerArray($newcontent, $markerArray);
 			$sortlinks = array();
 		}
 		$content = tslib_cObj::substituteMarker($list_Code, '###FILELIST_RECORDS###', $rows);
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
 		$content = tsLib_CObj::substituteMarker($content, '###FILENAME_HEADER###', $this->pi_getLL('FILENAME_HEADER'));
 		$content = tsLib_CObj::substituteMarker($content, '###FILETYPE_HEADER###', $this->pi_getLL('FILETYPE_HEADER'));
 		$content = tsLib_CObj::substituteMarker($content, '###CR_DATE_HEADER###', $this->pi_getLL('CR_DATE_HEADER'));

 		// substitute static markers
 		$this->pi_loadLL();
 		$staticMarkers['###SETROWSPERVIEW###'] = $this->pi_getLL('setRowsPerView');
 		$staticMarkers['###LABEL_COUNT###'] = $this->pi_getLL('label_Count');

 		$content = tslib_cObj::substituteMarkerArray($content, $staticMarkers);

		// substitute Links for Sorting
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
	 * @param	array		$list: list of selected elements
	 * @return	html		rendered category selection content element
	 */
 	function renderCatSelection($list, $treeID='') {

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
			$urlVars['treeID'] = $treeID != '' ?   $treeID : null;
			$url = t3lib_div::linkThisScript($urlVars);
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

 		// converting all fields in the record to marker (recordfields and markername must match)
 		$markerArray = $this->recordToMarkerArray($record, 'singleView');

 		$this->pi_loadLL();
 		$content=tslib_cObj::substituteMarkerArray($single_Code, $markerArray);
 		$content = tslib_cObj::substituteMarker($content, '###TITLE_SINGLEVIEW###',$markerArray['###TITLE###']);
 		$content = tslib_cObj::substituteMarker($content, '###CR_DATE_HEADER###',$this->pi_getLL('CR_DATE_HEADER'));
 		$content = tslib_cObj::substituteMarker($content, '###FILE_SIZE_HEADER###',$this->pi_getLL('FILE_SIZE_HEADER'));
 		$content = tslib_cObj::substituteMarker($content, '###CR_DESCRIPTION_HEADER###',$this->pi_getLL('CR_DESCRIPTION_HEADER'));
 		$content = tslib_cObj::substituteMarker($content, '###COPYRIGHT_HEADER###',$this->pi_getLL('COPYRIGHT_HEADER'));
 		$content = tslib_cObj::substituteMarker($content, '###FILETYPE_HEADER###',$this->pi_getLL('FILETYPE_HEADER'));
 		$content = tslib_cObj::substituteMarker($content, '###LINK_HEADER###',$this->pi_getLL('LINK_HEADER'));
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
 				$message = strip_tags($customMessage);
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

		foreach ($record as $key=>$value) {
				// stripHtml = 1 is default - it has to be disabled via stripHtml = 0
			if (!isset($this->conf[$scope.'.'][$key.'.']['stripHtml'])) {
				$this->conf[$scope.'.'][$key.'.']['stripHtml'] = 1;
			}
			$markerArray['###'.strtoupper($key).'###'] = $cObj->stdWrap($value, $this->conf[$scope.'.'][$key.'.']);
 		}
 		return $markerArray;
 	}


	/**
	 * Renders the filter view
	 *
	 * @param	[array]		$filterArray: ...
	 * @param	[array]		$errorArray: ...
	 * @return	[type]		...
	 */
 	function renderFilterView($filterArray, $errorArray = '') {
 		$formCode  = tslib_CObj::getSubpart($this->fileContent, '###FILTERVIEW###');

 		// filling fields with url - vars
 		$markerArray  = $this->recordToMarkerArray($filterArray, 'filterView');

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
 		$markerArray['###PDFFILE###'] = $this->pi_getLL('pdffile');
 		$markerArray['###WORDFILE###'] = $this->pi_getLL('wordfile');
 		$markerArray['###JPEGFILE###'] = $this->pi_getLL('jpegfile');
 		$markerArray['###GIFFILE###'] = $this->pi_getLL('giffile');

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
			'jpeg' => array (
				label => $this->pi_getLL('jpegfile')
			),
			'gif' => array (
				label => $this->pi_getLL('giffile')
			),
			'zip' => array (
				label => $this->pi_getLL('zipfile')
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
 	function renderUploadForm($userInput) {
		$this->pi_loadLL();
		$formCode  = tslib_CObj::getSubpart($this->fileContent, '###UPLOADFORM###');
		$markerArray['###BUTTON_UPLOAD###'] = $this->pi_getLL('BUTTON_UPLOAD');
		$markerArray['###TITLE_FILEUPLOAD###'] = $this->pi_getLL('TITLE_FILEUPLOAD');
		$markerArray['###LABEL_FILE###'] =  $this->pi_getLL('LABEL_FILE');
		$markerArray['###LABEL_TITLE###'] =  $this->pi_getLL('LABEL_TITLE');
		$markerArray['###LABEL_COPYRIGHT###'] = $this->pi_getLL('LABEL_COPYRIGHT');
		$markerArray['###LABEL_AUTHOR###'] =  $this->pi_getLL('LABEL_AUTHOR');
		$markerArray['###LABEL_DESCRIPTION###'] =  $this->pi_getLL('LABEL_DESCRIPTION');
		$markerArray['###VALUE_TITLE###']= $userInput['title'];
		$markerArray['###VALUE_COPYRIGHT###']= $userInput['copyright'];
		$markerArray['###VALUE_AUTHOR###']= $userInput['creator'];
		$markerArray['###VALUE_DESCRIPTION###']= $userInput['description'];

		return tslib_cObj::substituteMarkerArray($formCode, $markerArray);
 	}


	/**
	 * renderFilterList
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
	 			$listCode .= tslib_CObj::substituteMarkerArray($listElem, $markerArray);
	 		}
 		}
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
//	 *
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
	 *
	 */
	function renderCategorisationForm($docData, $selectedCats='') {
		$this->pi_loadLL();
		$formCode  = tslib_CObj::getSubpart($this->fileContent, '###CATEGORISATION###');
		$tree = t3lib_div::makeInstance('tx_damfrontend_categorisationTree');
		#$tree->init('categorisation');
		$tree->init(-1);
		$tree->title = $this->pi_getLL('CATEGORISATION_TREE_NAME');
		$markerArray = $this->recordToMarkerArray($docData);
		$markerArray['###CATTREE###'] = $tree->getBrowsableTree();
		$markerArray['###CATLIST###'] = '';
		$markerArray['###CATEGORISATION_TEXT_HEADER###']=$this->pi_getLL('CATEGORISATION_TEXT_HEADER');
		$markerArray['###CATEGORISATION_TEXT_TITLE###']=$this->pi_getLL('CATEGORISATION_TEXT_TITLE');
		$markerArray['###CATEGORISATION_TEXT_DESCRIPTION###']=$this->pi_getLL('CATEGORISATION_TEXT_DESCRIPTION');
		$markerArray['###CATEGORISATION_TEXT_SEND###']=$this->pi_getLL('CATEGORISATION_TEXT_SEND');
		#t3lib_div::debug($this->pi_getLL('CATEGORISATION_TEXT_HEADER'));
		if (is_array($selectedCats)) {
			#$catCode = $this->renderCatSelection($selectedCats, 'categorisation');
			$catCode = $this->renderCatSelection($selectedCats, -1);
		}
		else {
			$catCode = $this->renderError('noCatSelected');
		}
		$markerArray['###CATLIST###'] = $catCode;
		$formCode = tslib_cObj::substituteMarkerArray($formCode, $markerArray);
		return $formCode;
	}


	function renderUploadSuccess() {
		$this->pi_loadLL();
		return $this->pi_getLL('UPLOAD_SUCCESS');
	}

 }
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/dam_frontend/frontend/class.tx_damfrontend_rendering.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/dam_frontend/frontend/class.tx_damfrontend_rendering.php']);
}

?>
