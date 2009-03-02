<?php
/**
 * ************************************************************
 *  Copyright notice
 *
 *  (c) 2006  (martin_baum@gmx.net)
 *  All rights reserved
 *
 *  This script is part of the Typo3 project. The Typo3 project is
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
 * **************************************************************/

$prefixId = 'tx_damfrontend_pi1';
if (!$_REQUEST['docID']
	&& !$_POST[$prefixId]) {
	die ('<h1>Error</h1><p>You have no access to download a file. In this case no DocID was given!</p>');
}
error_reporting(E_ERROR);

define('TYPO3_OS', stristr(PHP_OS,'win')&&!stristr(PHP_OS,'darwin')?'WIN':'');
define('TYPO3_MODE','FE');
define('PATH_thisScript',str_replace('//','/', str_replace('\\','/', (php_sapi_name()=='cgi'||php_sapi_name()=='isapi' ||php_sapi_name()=='cgi-fcgi')&&($_SERVER['ORIG_PATH_TRANSLATED']?$_SERVER['ORIG_PATH_TRANSLATED']:$_SERVER['PATH_TRANSLATED'])? ($_SERVER['ORIG_PATH_TRANSLATED']?$_SERVER['ORIG_PATH_TRANSLATED']:$_SERVER['PATH_TRANSLATED']):$_SERVER['SCRIPT_FILENAME'])));
define('PATH_site',preg_replace("/(typo3conf|typo3)\/ext\/DAM_Frontend/i", '', dirname(PATH_thisScript)));
define('PATH_t3lib', PATH_site.'t3lib/');
define('PATH_tslib', PATH_site.'typo3/sysext/cms/tslib/');
define('PATH_typo3conf', PATH_site.'typo3conf/');

require_once(PATH_t3lib.'class.t3lib_timetrack.php');
$TT = new t3lib_timeTrack;
$TT->start();
$TT->push('','Script start');

require_once(PATH_t3lib.'class.t3lib_div.php');
require_once(PATH_t3lib.'class.t3lib_extmgm.php');
require_once(PATH_t3lib.'class.t3lib_db.php');
require_once(PATH_t3lib.'config_default.php');
require_once(PATH_tslib."class.tslib_fe.php");
require_once(PATH_t3lib."class.t3lib_cs.php");
require_once(PATH_t3lib."class.t3lib_userauth.php");
require_once(PATH_tslib."class.tslib_feuserauth.php");
require_once(PATH_t3lib."class.t3lib_befunc.php");

require_once(PATH_tslib.'class.tslib_content.php');
require_once(PATH_tslib.'class.tslib_gifbuilder.php');

//require_once(t3lib_extMgm::extPath('dam_frontend').'/DAL/class.tx_damfrontend_DAL_categories.php');
require_once(t3lib_extMgm::extPath('dam_frontend').'/DAL/class.tx_damfrontend_DAL_documents.php');

$TYPO3_DB = t3lib_div::makeInstance('t3lib_DB');
$TYPO3_DB->sql_pconnect(TYPO3_db_host, TYPO3_db_username, TYPO3_db_password);
//Create and init $TSFE object (TSFE = TypoScript Front End)
$tempClassName=t3lib_div::makeInstanceClassName("tslib_fe");
$TSFE = new $tempClassName($TYPO3_CONF_VARS,t3lib_div::GPvar("id"),t3lib_div::GPvar("type"), t3lib_div::GPvar("no_cache"),t3lib_div::GPvar("cHash"), t3lib_div::GPvar("jumpurl"),t3lib_div::GPvar("MP"),t3lib_div::GPvar("RDCT"));
$TSFE->connectToMySQL();
$TSFE->initFEuser();
$TSFE->initUserGroups();



$docLogic = t3lib_div::makeInstance('tx_damfrontend_DAL_documents');

// Formular versendet?
$post = t3lib_div::_POST($prefixId);
if (is_array($post) && count($post) > 0) {
	$filesToSend = array();
	foreach ($post as $docID => $configuration) {
		if (!is_numeric($docID)) {
			continue; // not a file-id
		}
		if (!isset($configuration['submit'])
			&& !isset($post['submit'])) {
			continue;
		}
		if (isset($configuration['submit'])) {
			// one single file was submitted, we should not care for
			// the list configuration
			$post['modus'] = '';
		}
		if (!isset($configuration['convert'])) {
			die('configuration is missing');
		}
		if ('' == $configuration['convert']) {
			// there is no configuration, this file has not been selected
			continue;
		}

		if ($docID <= 0) {
			die('<h1>Error</h1><p>You have no access to download a file. In this case no correct DocID was given!</p>');
		}
		if (!$docLogic->checkAccess($docID, 1)) {
			die('<h1>Error</h1><p>You have no access to download this file.');
		}
		$doc = $docLogic->getDocument($docID);
		$filePath = PATH_site.$doc['file_path'].$doc['file_name'];
		if ($tmp = createFile($filePath, configuration2Array($configuration['convert']))) {
			$filesToSend[] = array('file' => $tmp, 'filename' => $doc['file_name']);
		} else {
			die('<h1>Error</h1><p>File not available...</p>');
		}
	}

	if (0 == count($filesToSend)) {
		die ('<h1>Error</h1><p>There was no file requested.</p>');
	}

	switch ($post['modus']) {
		case 'createZipFile':
			if (count($filesToSend) < 1) {
				die('<h1>Error</h1><p>There should at least one file selected.</p>');
			}
			if (!t3lib_extMgm::isLoaded('nh_archive')) {
				die('The extension nh_archive is needed for creating zip-files.');
			}

			require_once(t3lib_extMgm::extPath('nh_archive').'class.tx_nharchive_zipfile.php');
			$zipfile = t3lib_div::makeInstance('tx_nharchive_zipfile');

			foreach($filesToSend as $params) {
				$zipfile->addfile(file_get_contents($params['file']),$params['filename']);
			}
			$zipfile->addfiles($filesToSend);
			// TODO: filename should be configurable?
			$zipfile->filedownload('download'.date('_Ymd_his').'.zip');
        	exit();
		break;
		case 'sendAsMail':
			die('sorry, not implemented yet');
		break;
		case 'sendZippedAsMail':
			die('sorry, not implemented yet');
		break;
		case 'sendFileLink':
			die('sorry, not implemented yet');
		break;
		case 'sendZippedFileLink':
			die('sorry, not implemented yet');
		break;
		default:
			// no modus select, should be than only one file
			if (1 != count($filesToSend)) {
				die('<h1>Error</h1><p>There should only one file selected.</p>');
			}
			if (!sendFile($filesToSend[0]['file'], $filesToSend[0]['filename'])) {
				die ('<h1>Error</h1><p>The requested file was not found! Please contact the adminstrator and tell him that the id: '.$docID .' was not found');
			}
		break;
	}
}


	/**
	 * Splits configuration
	 * and returns array which could be used in sendFile
	 *
	 * @param string configuration
	 * @return array configuration Array
	 */
	function configuration2Array($configuration) {
		// x:1024;y:768;dpi:300
		if ('' == $configuration) return array();
		$config = array();
		foreach (t3lib_div::trimExplode(';', $configuration, true) as $entry) {
			list($param, $value) = t3lib_div::trimExplode(':',$entry,true);
			$config[$param] = $value;
		}
		return $config;
	}

	/**
	 * @param string $file Filename including absolute path
	 */
	function sendFile($file, $filename) {
		$filesize = filesize($file);
		header("Content-type: application/force-download");
		header("Content-Transfer-Encoding: Binary");
		header("Content-length: ".$filesize);
		header("Content-disposition: attachment; filename=\"".rawurlencode($filename)."\"");
		readfile($file);
		exit();
	}

	/**
	 *
	 */
	function createFile($filePath, $configuration = array()) {
		global $TSFE;
		if (!file_exists($filePath)) {
			return false;
		}
		if (isset($configuration['ORIGINAL'])) { return $filePath; }

		/*
		$cObj = t3lib_div::makeInstance('tslib_cObj');
		$cObj->start(array(), 'tx_dam');
		*/
		// x:1024;y:768;dpi:300"
		$fileArray = array();
		$fileArray['import'] = $filePath;
		// we need an "import." entry
		$fileArray['import.'] = array();
		$fileArray = array(
			'ext' => '',
			'width' => (int)$configuration['width'],
			'height' => (int)$configuration['height'],
		);
		// more params: added to the command line
		// http://www.imagemagick.org/script/command-line-options.php
		if ((int)$configuration['resample'] > 0) {
			$fileArray['params'] .= ' -resample '.(int)$configuration['resample'].' ';
		}
		$theImage = $filePath;
		$options = null;
		$gifCreator = t3lib_div::makeInstance('tslib_gifbuilder');
		/* @var $gifCreator tslib_gifbuilder */
		$gifCreator->absPrefix = PATH_site;
		$gifCreator->init();
		$filePath = $gifCreator->imageMagickConvert($theImage,$fileArray['ext'],$fileArray['width'],$fileArray['height'],$fileArray['params'],$fileArray['frame'],$options, true);
		return $filePath[3];
	}



// test f�r den Zugriff auf eine Datei
$docID = intval(t3lib_div::GPvar('docID'));

if ($docID==0) {
	die('<h1>Error</h1><p>You have no access to download a file. In this case no correct DocID was given!</p>');
}
if (!$docLogic->checkAccess($docID, 1)) {
	die('<h1>Error</h1><p>You have no access to download this file.');
}
$doc = $docLogic->getDocument($docID);
$filePath = PATH_site.$doc['file_path'].$doc['file_name'];
if (!sendFile($filePath, $doc['file_name'])) {
	die ('<h1>Error</h1><p>The requested file was not found! Please contact the adminstrator and tell him that the id: '.$docID .' was not found');
}




?>
