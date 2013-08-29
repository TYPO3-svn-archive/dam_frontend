<?php
/***************************************************************
 *  Copyright notice
 *  (c) 2007-2010 Stefan Busemann <info@in2code.de>
 *  (c) 2012 Marcus Schwemer <marcus.schwemer@in2code.de>
 *  All rights reserved
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

require_once(PATH_tslib . 'class.tslib_pibase.php');
require_once(PATH_t3lib . 'class.t3lib_befunc.php');
require_once(PATH_t3lib . 'class.t3lib_iconworks.php');

/**
 * Plugin 'DAM Frontend Filelist' for the 'dam_frontend' extension.
 * based on dam_downloadlist by Davide Principi <d.principi@provincia.ps.it
 *
 * @author    Stefan Busemann <info@in2code.de>
 * @author    Marcus Schwemer <marcus.schwemer@in2code.de>
 * @package    TYPO3
 * @subpackage    tx_damfrontend
 */
class tx_damfrontend_pi2 extends tslib_pibase {

	var $prefixId = 'tx_damfrontend_pi2'; // Same as class name
	var $scriptRelPath = 'pi2/class.tx_damfrontend_pi2.php'; // Path to this script relative to the extension dir.
	var $extKey = 'dam_frontend'; // The extension key.
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

	var $templateName = '';

	/**
	 * Plugin Entry-Point
	 *
	 * @param    [type]        $content: ...
	 * @param    [type]        $conf: ...
	 * @return    [type]        ...
	 */
	function main($content, $conf) {

		$this->conf = $conf;

		$this->conf['templates.']['default.']['renderFields.'] = array_merge_recursive($this->conf['templates.']['default.']['renderFields.'], $this->conf['renderFields.']);

		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();
		$this->pi_initPIflexForm('pi_flexform');
		if ($GLOBALS['TSFE']->beUserLogin
			|| $GLOBALS['TSFE']->isUserOrGroupSet()
		) {
			// TODO: use USER_INT instead of using set_no_cache!
			//$GLOBALS['TSFE']->set_no_cache();
		}

		// init grouping data structures:
		$this->group = array();
		$this->groupCriteria = t3lib_div::trimExplode(',', $this->conf['groupCriteria']);

		// get date format
		if ($this->conf['dateConf']) {
			$this->dateConf = $this->conf['dateConf'];
		}

		// get icon base address
		$this->iconBaseAddress = $this->conf['iconBaseAddress'];

		if ('' != trim($this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'templateFile', 'sDEF'))) {
			$templateFile = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'templateFile', 'sDEF');
			$this->conf['template'] = $this->conf['templatePath'] . '/' . $templateFile;
		}

		$templateFile = t3lib_div::getFileAbsFileName($this->conf['template']); //getting filename of the template

		if ($templateFile && @is_file($templateFile)) {

			$this->templateName = $this->getTemplateName($templateFile);

			if (!$this->conf['templates.'][$this->templateName . '.']) {
				$this->templateName = 'default';
			}

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
	 * Creates the the template name needed for TypoScript config
	 *
	 * @param string templateFileName
	 * @return string templateName
	 */
	function getTemplateName($templateFileName) {
		$filePathParts = explode('/', $templateFileName);
		$counter = count($filePathParts);
		$last = $filePathParts[$counter - 1];
		$fileNameParts = explode('.', $last);
		return $fileNameParts[0];
	}

	/**
	 * Generate HTML code
	 *
	 * @return    string        html content
	 */
	function renderContent() {
		$content = "\n";
		$renderedItem = "";
		$renderedItemList = '';
		$subpartArray = array();
		$markerArray = array();

		$subpartArray['###TEMPLATE_SUBHEADER###'] = $this->renderSubheader();
		$subpartArray['###TEMPLATE_LIST_LINK###'] = $this->renderLinkList();

		$records = $this->selectDamRecords(); // loading the IDs of the stored DAM Files

		foreach ($this->damUidList as $uid) {
			if (array_key_exists($uid, $records)) {
				$groupKey = $records[$uid];
				$renderedItem = $this->renderGroup($groupKey, $records);
				$subpartArray['###TEMPLATE_LIST_ITEM###'] .= $renderedItem;
			}
		}

		$content = $this->cObj->substituteMarkerArrayCached($this->template, $markerArray, $subpartArray);

		// render static langmarkers
		$content = tslib_cObj::substituteMarkerArray($content, $this->substituteLangMarkers($content));

		// render userDefined TS Markers

		// eliminate remaining markers

		return $content;
	}

	/*
	 * Genereate HTML code for the subheader
	 *
	 * @return HTML code
	 */
	function renderSubheader() {
		$subheader = trim($this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'subheader', 'sDEF'));
		if ($subheader != '') {
			$cObj = t3lib_div::makeInstance('tslib_cObj');
			$subheaderTemplate = $this->cObj->getSubPart($this->template, '###TEMPLATE_SUBHEADER###');
			$subheader = $cObj->stdWrap($subheader, $this->conf['templates.'][$this->templateName . '.']['renderFields.']['subheader.']);
			$subHeaderHtml = $this->cObj->substituteMarker($subheaderTemplate, '###SUBHEADER###', $subheader);
		} else {
			$subHeaderHtml = '';
		}
		return $subHeaderHtml;
	}

	/*
		 * Generates HTML code for the link list
		 *
		 * @return string HTML content
		 */
	function renderLinkList() {

		$linkListHtml = '';

		$links = explode(",", $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'links', 'sLINKS'));
		$header = explode("\n", $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'linkheader', 'sLINKS'));
		$description = explode("\n", $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'linkdescription', 'sLINKS'));
		$text = explode("\n", $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'linktext', 'sLINKS'));

		$templateListLinkItem = $this->cObj->getSubPart($this->template, '###TEMPLATE_LIST_LINK_ITEM###');
		$marker = array();

		$cObj = t3lib_div::makeInstance('tslib_cObj');

		for ($i = 0; $i < count($links); $i++) {
			$marker['###MARK_LINK_TITLE###'] = $cObj->stdWrap(trim($header[$i]), $this->conf['templates.'][$this->templateName . '.']['renderFields.']['linktitle.']);
			$marker['###MARK_LINK_DESCRIPTION###'] = $cObj->stdWrap(trim($description[$i]), $this->conf['templates.'][$this->templateName . '.']['renderFields.']['linkdescription.']);
			$marker['###MARK_LINK_HREF###'] = trim($links[$i]);
			$linkText = trim($text[$i]);
			if ($linkText == '') {
				$row = $GLOBALS['TYPO3_DB']->exec_SELECTgetSingleRow('title', 'pages', 'uid=' . intval($links[$i]));
				$linkText = $row['title'];
			}
			// $marker['###MARK_LINK_TEXT###'] = $cObj->stdWrap($linkText, $this->conf['templates.'][$this->templateName . '.']['renderFields.']['linktext.']);
			$marker['###MARK_LINK_TEXT###'] = $linkText;
			$linkListHtml .= $this->cObj->substituteMarkerArray($templateListLinkItem, $marker);
		}

		return $linkListHtml;
	}

	/**
	 * Generates HTML code for a Group of Files
	 *
	 * @param    [type]        $groupKey:     Grouping criteria
	 * @param    [type]        $records:     records for this grouping criteria
	 * @return    [string]        html content
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
		foreach ($this->damUidList as $uid) {
			if (in_array($uid, $groupDataKeys)) {
				$rec = $records[$groupKey][$uid];

				if ($this->conf['checkIfFileExist'] == 1) {
					if (!file_exists($rec['file_path'] . $rec['file_name'])) {
						continue;
					}
				}
				if ($firstRecord == null) {
					$firstRecord = $rec;
				}
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
	 * @return    [type]        ...
	 */
	function selectDamRecords() {
		$dbh = $GLOBALS['TYPO3_DB'];

		// define language overlay
		$conf = array(
			'sys_language_uid' => $this->sys_language_uid,
			'lovl_mode' => ''
		);

		// fetch DAM UIDs from tt_content -
		$this->damUidList = t3lib_div::intExplode(',', $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'damUid', 'sDEF'));

		$fieldList = array('t3ver_id', 't3ver_state', 't3ver_wsid', 't3ver_count', 'ident', 'description', 'keywords', 'alt_text', 'abstract', 'language', 'publisher', 'copyright', 'date_cr', 'date_mod', 'tx_damfrontend_feuser_upload');

		$damTableName = 'tx_dam';
		$whereClause = 'uid IN (' . implode(', ', $this->damUidList) . ')' .
			'  AND sys_language_uid=' . $GLOBALS['TYPO3_DB']->fullQuoteStr($this->sys_language_uid, 'tx_dam') .
			t3lib_BEfunc::deleteClause($damTableName) . $this->cObj->enableFields($damTableName);

		$fields = tx_dam_db::getMetaInfoFieldList(false, $fieldList);

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
				$record = tx_dam_db::getRecordOverlay('tx_dam', $record, $conf);
				$rows[$groupKey][$record['uid']] = $record;
				$rows[$record['uid']] = $groupKey;
			}
		} else {
			// plain procedure
			while ($record = $dbh->sql_fetch_assoc($resObj)) {
				$record = tx_dam_db::getRecordOverlay('tx_dam', $record, $conf);
				$rows['group_' . $record['uid']] = array($record['uid'] => $record);
			}
		}

		$dbh->sql_free_result($resObj);

		return $rows;
	}

	/**
	 * [Describe function...]
	 *
	 * @return    [type]        ...
	 */
	function groupEnabled() {
		return (count($this->groupCriteria) > 0);
	}

	/**
	 * [Describe function...]
	 *
	 * @param    [type]        $record: ...
	 * @return    [type]        ...
	 */
	function getGroupKey($record) {
		$groupKey = '';
		foreach ($this->groupCriteria as $criteriaSlice) {
			$groupKey .= $record[$criteriaSlice];
		}
		return $groupKey;
	}

	/**
	 * Building the Details for a DAM record
	 *
	 * @param    [array]        $damRecord: DAM database record
	 * @return    [array]        an array containing processed DAM fields
	 */
	function processRecord($damRecord) {

		if (!is_array($damRecord)) {
			return array();
		}

		$cObj = t3lib_div::makeInstance('tslib_cObj');
		$cObj->start($damRecord, '');

		foreach ($damRecord as $key => $value) {
			if (isset($this->conf['templates.'][$this->templateName . '.']['renderFields.'][$key . '.'])) {
				$damRecord[$key] = $cObj->stdWrap($value, $this->conf['templates.'][$this->templateName . '.']['renderFields.'][$key . '.']);
			}
		}
		// Download Link
		$damRecord['file_href'] = $cObj->typoLink('', $this->conf['templates.'][$this->templateName . '.']['renderFields.']['file_href.']);

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
			$markRecord['mark_' . $key] = $value;
		}
		return $markRecord;
	}

	/**
	 * [Describe function...]
	 *
	 * @return    [type]        ...
	 */
	function getIconBaseAddress() {
		if ($this->iconBaseAddress) {
			return $this->iconBaseAddress;
		} else {
			return t3lib_extMgm::siteRelPath($this->extKey) . 'res/ico/';
		}
	}

	/**
	 * Returns the reference to the image file associated to given mime types.
	 *
	 * @param    [string]        $mimeType: mime type.
	 * @param    [string]        $mimeSubType: mime sub type.
	 * @return    [string]        href attribute value, pointing to an image file.
	 */
	function getFileIconHref($mimeType, $mimeSubType) {
		$rootKey = 'mediaTypes.';

		$mimeType .= '.';

		$mimeTypesConf = $this->conf[$rootKey];

		$mimeSubType = str_replace('.','_',$mimeSubType);
		$mimeSubType = str_replace('-','_',$mimeSubType);

		if (!array_key_exists($rootKey, $this->conf)) {
			return "#";
		}
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
	 * Only if debug and TYPO3_DLOG
	 * is set
	 * To use it, set
	 * $TYPO3_CONF_VARS['SYS']['enable_DLOG'] = '1';
	 * via Install-Tool or direct in localconf.php
	 * Then install an devlog Extension. You can watch the logs in the backend
	 * then
	 *
	 * @param    string        $msg
	 * @return    void
	 */
	function log($msg) {
		if (TYPO3_DLOG && $this->conf['debug']) {
			t3lib_div::devLog($msg, $this->extKey, 0);
		}
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
	 * @param    [type]        $msg: ...
	 * @param    [type]        $customMessage: ...
	 * @param    [type]        $customMessage2: ...
	 * @return    [string]        $return:        html of the form
	 * @author stefan
	 */
	function renderMessage($msg = 'default', $customMessage = "", $customMessage2 = "") {
		$this->pi_loadLL();
		$subpart = tslib_CObj::getSubpart($this->fileContent, '###MESSAGE###');

		switch ($msg) {

			case 'custom':
				$message = strip_tags($customMessage . '&nbsp;' . $customMessage2);
				break;
			default:
				$message = $this->pi_getLL('standardErrorMessage');
		}
		$markerArray['###FORM_URL###'] = $this->cObj->typolink('', $this->conf['renderMessage.']['form_url.']);
		$markerArray['###LABEL_MESSAGE###'] = $this->pi_getLL('LABEL_MESSAGE');
		$markerArray['###MESSAGE_TEXT###'] = $this->cObj->stdWrap($message, $this->conf['renderMessage.']['message_text.']);
		$markerArray['###BUTTON_NEXT###'] = '<input name="ok" type="submit" value="' . $this->pi_getLL('BUTTON_NEXT') . '">';
		$content = tslib_cObj::substituteMarkerArray($subpart, $markerArray);
		$content = tslib_cObj::substituteMarkerArray($content, $this->substituteLangMarkers($content));
		#$content=;

		return $content;
	}

	/**
	 * finds markers (###LLL:[markername]###) in given template Code
	 *
	 * @param    string        $templCode        the template code in which the markers should be searched for
	 * @return    array        the found language markers with translation text
	 */
	function substituteLangMarkers($templCode) {
		global $LANG;
		$langMarkers = array();

		if ($this->conf['langFile'] != '') {
			$aLLMarkerList = array();
			preg_match_all('/###LLL:.+?###/Ssm', $templCode, $aLLMarkerList);

			if ($this->conf['debug'] == 1) {
				t3lib_utility_Debug::debug('in class.tx_damfrontend_rendering.php / Found language markers: //');
				t3lib_utility_Debug::debug($aLLMarkerList);
			}

			foreach ($aLLMarkerList[0] as $LLMarker) {
				$llKey = strtoupper(substr($LLMarker, 7, strlen($LLMarker) - 10));
				$marker = $llKey;
				$langMarkers['###LLL:' . strtoupper($marker) . '###'] = $this->cObj->stdWrap(trim($GLOBALS['TSFE']->sL('LLL:' . $this->conf['langFile'] . ':' . $llKey)), $this->conf['templates.'][$this->templateName . '.']['renderFields.'][$marker . '.']);
			}
		}
		return $langMarkers;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/dam_frontend/pi2/class.tx_damfrontend_pi2.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/dam_frontend/pi2/class.tx_damfrontend_pi2.php']);
}

?>