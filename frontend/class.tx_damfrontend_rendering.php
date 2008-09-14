<?php
 require_once(PATH_tslib.'class.tslib_content.php');
 require_once(PATH_tslib.'class.tslib_pibase.php');

/***************************************************************
*  Copyright notice
*
*  (c) 2006-2007 BUS Netzwerk (typo3@in2form.com)
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
 *   89:     function init()
 *  100:     function setFileRef($filePath)
 *  115:     function renderFileList($list, $resultcount, $pointer, $listLength)
 *  193:     function renderBrowseResults($resultcount, $pointer, $listLength)
 *  219:     function renderSortLink($key)
 *  237:     function renderCatSelection($list)
 *  265:     function renderSingleView($record)
 *  291:     function renderError($errormsg = 'deflaut')
 *  324:     function recordToMarkerArray($record)
 *  348:     function renderFilterView($filterArray, $errorArray = '')
 *  390:     function renderFileTypeList($filetype)
 *  434:     function renderFilterError($errorCode)
 *  445:     function renderUploadForm()
 *  457:     function renderFilterList($filterList)
 *  478:     function renderFilterCreationForm()
 *  491:     function renderFilterCreationLink()
 *  503:     function getFileIconHref($mimeType, $mimeSubType)
 *  530:     function getIconBaseAddress()
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


	/**
	 * inits this class (loading the locallang file)
	 *
	 * @return	[void]		...
	 */
	function init() {
		$this->pi_loadLL();
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
	 * @return	[type]		...
	 */
 	function renderFileList($list, $resultcount, $pointer, $listLength) {

 		if(!is_array($list)) die($this->pi_getLL('error_renderFileList_emptyArray'));
		if(!intval($resultcount) || $resultcount < 1) die($this->pi_getLL('error_renderFileList_resultcount'));
		if(!intval($pointer) || $pointer < 1 ) $pointer = 1;
		if(!intval($listLength) || $listLength < 1 ) $listLength = 10;
		if (!isset($this->fileContent)) return $this->pi_getLL('error_renderFileList_template');

 		// reading the filecontent just one time
 		$record_Code = tslib_CObj::getSubpart($this->fileContent,'###FILELIST_RECORD###');
 		$list_Code = tsLib_CObj::getSubpart($this->fileContent,'###FILELIST###');
 		foreach ($list as $elem) {
 			// converting timestamps
 			$elem['tstamp'] = date('d.m.Y', $elem['tstamp']);
 			$elem['crdate'] = date('d.m.Y', $elem['crdate']);

 			$markerArray = $this->recordToMarkerArray($elem);
 			// changes in the content of the marker arrays @todo what ist done here?
 			$this->piVars['showUid'] = $elem['uid'];
 			#$markerArray['###TITLE###'] = $this->pi_linkTP_keepPiVars($elem['title']);
 			$markerArray['###TITLE###'] = '<a href="typo3conf/ext/dam_frontend/pushfile.php?docID='.$elem['uid'].'" >'. $elem['title'] .'</a>';
 			// replacing the  filesize by an readable output
//			$filesize = $elem['file_size'];
// 			if ($filesize > 1048576) {
//		      $order = "M";
//		      $filesize = (float) $filesize / 1048576.0;
//		    }
//		    else if ($filesize > 1024) {
//		      $order = "K";
//		      $filesize = (float) $filesize / 1024.0;
//		    }

 			$markerArray['###FILE_SIZE###'] = t3lib_div::formatSize($filesize,' bytes | kb| mb| gb');
 			#$filesize.' '.$order;

 			// adding Markers for links to download and single View
 			$markerArray['###LINK_SINGLE###'] = $this->pi_linkTP_keepPiVars('<img src="'.$this->iconPath.'zoom.gif'.'" style="border-width: 0px"/>');
 			$markerArray['###LINK_DOWNLOAD###'] = '<a href="typo3conf/ext/dam_frontend/pushfile.php?docID='.$elem['uid'].'" ><img src="'.$this->iconPath.'clip_pasteafter.gif" style="border-width: 0px"/></a>';
 			$markerArray['###FILEICON###'] = '<img src="'.$this->getFileIconHref($elem['file_mime_type'],$elem['file_mime_subtype'] ).'" title="'.$elem['title'].'"  alt="'.$elem['title'].'"/>';

 			$newcontent = $record_Code;
 			$rows .= tslib_cObj::substituteMarkerArray($newcontent, $markerArray);
 			$sortlinks = array();
 		}
 		$content = tslib_cObj::substituteMarker($list_Code, '###FILELIST_RECORDS###', $rows);
		$content = tslib_cObj::substituteMarker($content, '###LISTLENGTH###', $listLength);
		$content = tslib_cObj::substituteMarker($content, '###TOTALCOUNT###', $resultcount);

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
 	function renderCatSelection($list) {
 		$wholeCode = tslib_CObj::getSubpart($this->fileContent,'###CATSELECTION###');
 		$listCode = '';
 		foreach ($list as $category) {
 			$listElem = tslib_CObj::getSubpart($this->fileContent,'###CATLIST###');
 			$urlVars = array(
				'catPlus' => null,
				'catMinus_Rec' => null,
				'catMinus' => $category['uid'],
				'catPlus_Rec' => null,
				'catEquals' => null
			);
			$url = t3lib_div::linkThisScript($urlVars);
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
 		// Formating Timefields
 		$record['tstamp'] = date('d.m.Y', $record['tstamp']);
 		$record['crdate'] = date('d.m.Y', $record['crdate']);
 		$markerArray = $this->recordToMarkerArray($record);
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
 		return $content;
 	}


	/**
	 * Renders an error message
	 *
	 * @param	string		$errormsg: ...
	 * @return	[type]		...
	 */
 	function renderError($errormsg = 'deflaut') {
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
 			default:
 				$message = $this->pi_getLL('standardErrorMessage');
 		}

 		$content = tslib_CObj::getSubpart($this->fileContent,'###ERROR###');
 		$content = tslib_CObj::substituteMarker($content, '###ERRORMESSAGE###', $message);
 		return tslib_CObj::substituteMarker($content, '###ERROR_HEADER###', $this->pi_getLL('error_header'));
 	}

	/**
	 * converts an associative array to an Marker Array in the form ### $KEY ### => value
	 *
	 * @param	array		$record: array that shall be converted
	 * @return	array		Markerarray ready for substitution
	 */
 	function recordToMarkerArray($record) {
 		if (!is_array($record)) die ('Parameter error in class.tx_damfrontend_rendering in function recordToMarkerArray: $record is no Array. Please inform your administrator.');
 		else {
 			foreach ($record as $key=>$value) {
 				If(strip_tags($value)=='') {
 					$valueReturn='&nbsp;';
 				}
 				else {
 					$valueReturn=strip_tags($value);
 				}
 				$markerArray['###'.strtoupper($key).'###'] = $valueReturn ;
	 		}
	 		return $markerArray;
 		}
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
 		$markerArray  = $this->recordToMarkerArray($filterArray);

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
	 *
	 * @return	[type]		...
	 * @todo function is not finished
	 */
 	function renderUploadForm() {

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
 		# ###TITLE_HEADER###
 		# ###DESCRIPTION_HEADER###:
 		$formCode = tslib_CObj::getSubpart($this->fileContent, '###NEWFILTER###');
 		return $formCode;
 	}


	/**
	 * Renders a filter creation creation link
	 *
	 * @return	[type]		...
	 */
 	function renderFilterCreationLink() {
 		$formCode = tslib_CObj::getSubpart($this->fileContent, '###NEWFILTER_LINK###');
 		return $formCode;
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
      return $BACK_PATH . $this->iconBaseAddress;
    } else {
      return $BACK_PATH . t3lib_extMgm::siteRelPath($this->extKey) . 'res/ico/';
    }
  }
 }
?>