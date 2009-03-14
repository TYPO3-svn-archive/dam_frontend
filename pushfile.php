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




//error_reporting(E_ERROR);

require_once(t3lib_extMgm::extPath('dam_frontend').'/DAL/class.tx_damfrontend_DAL_documents.php');

$userObj = tslib_eidtools::initFeUser(); // Initialize FE user object
$GLOBALS['TSFE']->fe_user = $userObj;
$userObj->fetchGroupData();
tslib_eidtools::connectDB();
$docLogic = t3lib_div::makeInstance('tx_damfrontend_DAL_documents');
$docLogic->feuser = $userObj;

require_once(PATH_t3lib.'class.t3lib_stdGraphic.php');
require_once(PATH_t3lib.'class.t3lib_page.php');
require_once(PATH_tslib.'class.tslib_gifbuilder.php');




$prefixId = 'tx_damfrontend_pi1';
if (!$_REQUEST['docID']
	&& !$_POST[$prefixId]) {
	die ('<h1>Error</h1><p>You have no access to download a file. In this case no DocID was given!</p>');
}

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
	 *
	 */
	function sendMail() {
		// TODO: implement me:)
		// TODO: don't place that here
		// use t3lib_htmlmail
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
		if (!file_exists($filePath)) { return false; }
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



// test for access to a file
$docID = intval(t3lib_div::GPvar('docID'));

if ($docID==0) {
	die('<h1>Error</h1><p>You have no access to download a file. In this case no correct DocID was given!</p>');
}

// TODO checkDocumentAccess must be included too!
if (!$docLogic->checkAccess($docID, 1)) {
	die('<h1>Error</h1><p>You have no access to download this file.');
}
$doc = $docLogic->getDocument($docID);

$filePath = PATH_site.$doc['file_path'].$doc['file_name'];
if (!sendFile($filePath, $doc['file_name'])) {
	die ('<h1>Error</h1><p>The requested file was not found! Please contact the adminstrator and tell him that the id: '.$docID .' was not found');
}




?>
