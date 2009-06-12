<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2007 Martin Baum <martin_baum@gmx.net>
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
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

require_once(PATH_tslib.'class.tslib_pibase.php');
require_once(PATH_t3lib . 'class.t3lib_befunc.php');
require_once(PATH_t3lib . 'class.t3lib_iconworks.php');


/**
 * Plugin 'DAM Frontend Filelist' for the 'dam_frontend' extension.
 * based on dam_downloadlist by Davide Principi <d.principi@provincia.ps.it
 *
 * @author	Stefan Busemann <typo3in2form.com>
 * @package	TYPO3
 * @subpackage	tx_damfrontend
 */
class tx_damfrontend_pi2 extends tslib_pibase {
	var $prefixId = 'tx_damfrontend_pi2';		// Same as class name
	var $scriptRelPath = 'pi2/class.tx_damfrontend_pi2.php';	// Path to this script relative to the extension dir.
	var $extKey = 'dam_frontend';	// The extension key.
	var $pi_checkCHash = TRUE;

	var $fieldDamUidList = 'tx_damdownloadlist_records';
	var $damUidList = null;
	var $iconBaseAddress = null;

	var $template = '';
	var $templateItem = '';
	var $templateItemFile = '';

	var $groupCriteriaConf = 'title,file_path';
	var $group = null;

	var $pi_USER_INT_obj = TRUE;

	/**
	 * Plugin Entry-Point
	 *
	 * @param	[type]		$content: ...
	 * @param	[type]		$conf: ...
	 * @return	[type]		...
	 */
	function main($content, $conf) {
		$this->conf = $conf;
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();
		if ($GLOBALS['TSFE']->beUserLogin
			||  $GLOBALS['TSFE']->isUserOrGroupSet()) {
			// TODO: use USER_INT instead of using set_no_cache!
			//$GLOBALS['TSFE']->set_no_cache();
		}

		// init grouping data structures:
		$this->group = array();

		#checkIfFileExist
		$this->groupCriteria = t3lib_div::trimExplode(',', $this->conf['groupCriteria']);

		// get date format
		if ($this->conf['dateConf']) {
			$this->dateConf = $this->conf['dateConf'];
		}

			// get icon base address
		$this->iconBaseAddress = $this->conf['iconBaseAddress'];

		$templateFile = t3lib_div::getFileAbsFileName($this->conf['template']); //getting filename of the template

		if ($templateFile && @is_file($templateFile)) {
		  $this->template = $this->cObj->getSubpart($this->cObj->fileResource($this->conf['template']), '###TEMPLATE_LIST###');
		  $this->templateItem = $this->cObj->getSubPart($this->template, '###TEMPLATE_LIST_ITEM###');
		  $this->templateItemFile = $this->cObj->getSubPart($this->template, '###TEMPLATE_LIST_ITEM_FILE###');

			  // starts rendering:
		  $content = $this->renderContent();
		} else {
			  // no template file found. Print an error message.
		  $content = "<p style='background: yellow; color: black; padding: 0.1em; border: 1px solid black;'>NO TEMPLATE FILE FOUND: Check <code>template</code> property in TS template record.</p>";
		}

		$content = $this->cObj->stdWrap($content, $conf['stdWrap.']);

		return $this->pi_wrapInBaseClass($content);
  	}

	/**
	 * Generate HTML code
	 *
	 * @return	[string]		html content
	 */
	function renderContent() {
		$content = "\n";
		$renderedItem = "";

		$records = $this->selectDamRecords(); // loading the IDs of the stored DAM Files

		foreach($this->damUidList as $uid) {
			if (array_key_exists($uid, $records)) {
				$groupKey = $records[$uid];
				$renderedItem = $this->renderGroup($groupKey, $records);
				$content .= $renderedItem;
			  }
		}
		return $content;
	}


	/**
	 * Generates HTML code for a Group of Files
	 *
	 * @param	[type]		$groupKey: 	Grouping criteria
	 * @param	[type]		$records: 	records for this grouping criteria
	 * @return	[string]		html content
	 */
	function renderGroup($groupKey, &$records) {
		if (empty($records[$groupKey])) {
		  return '';
		}

		$renderedGroup = '';
		$groupDataKeys = array_keys($records[$groupKey]);

		$firstRecord = null;
		if (!is_array($this->damUidList)) {
			return $this->renderMessage('custom', $this->pi_getLL('Error_damUidList'));
		}
		// render records keeping dam uid list order:
		foreach($this->damUidList as $uid) {
			if(in_array($uid, $groupDataKeys)) {
				$rec = $records[$groupKey][$uid];

				if ($this->conf['checkIfFileExist']==1) {
			  		if (!file_exists($rec['file_path'] . $rec['file_name'])) {
			  			continue;
			  		}
				}
				if($firstRecord == null) { $firstRecord = $rec; }
			  	$renderedGroup .= $this->cObj->substituteMarkerArray($this->templateItemFile, $this->processRecord($rec), "###|###", 1);
			}
		}

		unset($records[$groupKey]);

		$renderedGroup = $this->cObj->substituteSubpart($this->templateItem, "###TEMPLATE_LIST_ITEM_FILE###", $renderedGroup);
		// Fill Group-Related Marker
		if (null == $firstRecord) {
			// if there is no record, there should be no output
			return '';
		}
		$renderedGroup = $this->cObj->substituteMarkerArray($renderedGroup, $this->processRecord($firstRecord), "###|###", 1);

		return $renderedGroup;
	}

	/**
	 * Fetch dam records
	 *
	 * @return	[type]		...
	 */
	function selectDamRecords() {
		$dbh = $GLOBALS['TYPO3_DB'];

		// fetch DAM UIDs from tt_content -
		$this->damUidList = t3lib_div::intExplode(',', $this->cObj->data[$this->fieldDamUidList]);

		$fieldList = array('uid','title', 'ident', 'description', 'file_path', 'file_name', 'file_size', 'file_mime_type', 'file_mime_subtype', 'file_type', 'file_mtime');
		$damTableName = 'tx_dam';
		$whereClause = 'uid IN (' . implode(', ', $this->damUidList) . ')' .
			t3lib_BEfunc::deleteClause($damTableName) . $this->cObj->enableFields($damTableName);

		$fields = 'pid,' . t3lib_BEfunc::getCommonSelectFields($damTableName, '') . ',' . implode(', ', $fieldList);

		$resObj = $dbh->exec_SELECTquery($fields,
										 $damTableName,
										 $whereClause);
		$rows = array();
		if ($this->groupEnabled()) {
		  // group procedure
		  $groupKey = '';
		  while ($record = $dbh->sql_fetch_assoc($resObj)) {

			$groupKey = $this->getGroupKey($record);

			if (!array_key_exists($groupKey, $rows)) {
		  		$rows[$groupKey] = array();
			}
			$rows[$groupKey][$record['uid']] = $record;
			$rows[$record['uid']] = $groupKey;
		  }

		} else {
		  // plain procedure
		  while ($record = $dbh->sql_fetch_assoc($resObj)) {
			$rows['group_' . $record['uid']] = array( $record['uid'] => $record);
		  }
		}

		$dbh->sql_free_result($resObj);

		return $rows;
	}

	/**
	 * [Describe function...]
	 *
	 * @return	[type]		...
	 */
	function groupEnabled() {
		return (count($this->groupCriteria) > 0);
	}

	/**
	 * [Describe function...]
	 *
	 * @param	[type]		$record: ...
	 * @return	[type]		...
	 */
	function getGroupKey($record) {
		$groupKey = '';
		foreach($this->groupCriteria as $criteriaSlice) {
		  $groupKey .= $record[$criteriaSlice];
		}
		return $groupKey;
	}

	/**
	 * Building the Details for a DAM record
	 *
	 * @param	[array]		$damRecord: DAM database record
	 * @return	[array]		an array containing processed DAM fields
	 */
	function processRecord($damRecord) {

		if (!is_array($damRecord)) {
			return array();
		}

		$cObj = t3lib_div::makeInstance('tslib_cObj');
		$cObj->start($damRecord, '');

		foreach ($damRecord as $key => $value) {
			if (isset($this->conf['renderFields.'][$key.'.'])) {
				$damRecord[$key] = $cObj->stdWrap($value, $this->conf['renderFields.'][$key.'.']);
			}
		}
		// Download Link
		$damRecord['file_href'] = $cObj->typoLink('',$this->conf['renderFields.']['file_href.']);

		// get icon reference:
		if ($damRecord['hidden']) {
			$imgalt = $this->pi_getLL('hidden');
			$imgsrc = $this->getIconBaseAddress() . 'hidden.png';
			$damRecord['hidden'] = $imgalt;
		} else {
			$mimetype = $damRecord['file_mime_type'];
			$mimesubtype = $damRecord['file_mime_subtype'];
			$imgalt = $mimetype . '/' . $mimesubtype;
			$imgsrc = $this->getFileIconHref($mimetype, $mimesubtype);
			$damRecord['hidden'] = '';
		}

		//add icon img-tag with (optional) edit-icon:
		$editIcon = $this->pi_getEditIcon("", "title,hidden,starttime,endtime,fe_group", $damRecord['title'], $damRecord, "tx_dam");
		$damRecord['icontag'] = $editIcon;
		$damRecord['icontag'] .= "<img border='0' class='" . $this->conf['iconCssClass'] . "' src='{$imgsrc}' alt='{$imgalt}' />";

		//

		$damRecord['file_name'] = $damRecord['file_name'] ? $damRecord['file_name'] : $this->pi_getLL('not_set');
		// ident field:
		$damRecord['ident'] = $damRecord['ident'] ? $damRecord['ident'] : $this->pi_getLL('not_set');


		// rename fields adding "mark_" prefix:
		$markRecord = array();
		foreach ($damRecord as $key => $value) {
			$markRecord['mark_'.$key] = $value;
		}
		return $markRecord;
	}

	/**
	 * [Describe function...]
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
	 * Logs some Debug-Messages via TYPO3 DLOG API
	 *
	 * Only if debug and TYPO3_DLOG
	 * is set
	 *
	 * To use it, set
	 * $TYPO3_CONF_VARS['SYS']['enable_DLOG'] = '1';
	 * via Install-Tool or direct in localconf.php
	 *
	 * Then install an devlog Extension. You can watch the logs in the backend
	 * then
	 *
	 * @param	string		$msg
	 * @return	void
	 */
	function log($msg) {
		if (TYPO3_DLOG && $this->conf['debug'])	{ t3lib_div::devLog($msg,$this->extKey,0); }
	  	/*
		$fileName = t3lib_extMgm::extPath($this->extKey) . $this->extKey . ".log";
		$fh = fopen($fileName, "a");
		fwrite($fh, date("Y-m-d H:i:s") . " - " . $msg . "\n");
		fclose($fh);
		*/
	}
	/**
	 * Message
	 *
	 * @param	[type]		$msg: ...
	 * @param	[type]		$customMessage: ...
	 * @param	[type]		$customMessage2: ...
	 * @return	[string]		$return:		html of the form
	 * @author stefan
	 */
	function renderMessage($msg = 'default', $customMessage="",$customMessage2="") {
		$this->pi_loadLL();
		$subpart = tslib_CObj::getSubpart($this->fileContent,'###MESSAGE###');

		switch ($msg) {

 			case 'custom':
 				$message = strip_tags($customMessage. '&nbsp;'. $customMessage2);
 				break;
 			default:
 				$message = $this->pi_getLL('standardErrorMessage');
 		}
		$markerArray['###FORM_URL###']= $this->cObj->typolink('', $this->conf['renderMessage.']['form_url.']);
 		$markerArray['###LABEL_MESSAGE###']=$this->pi_getLL('LABEL_MESSAGE');
 		$markerArray['###MESSAGE_TEXT###']=$this->cObj->stdWrap($message,$this->conf['renderMessage.']['message_text.']);
 		$markerArray['###BUTTON_NEXT###']= '<input name="ok" type="submit" value="'.$this->pi_getLL('BUTTON_NEXT').'">';
 		$content=tslib_cObj::substituteMarkerArray($subpart, $markerArray);
		return $content;
	}


}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/dam_frontend/pi2/class.tx_damfrontend_pi2.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/dam_frontend/pi2/class.tx_damfrontend_pi2.php']);
}

?>