<?php
/**
 * ************************************************************
 *  Copyright notice
 *
 *  (c) 2006-2010  (Stefan Busemann / martin_baum@gmx.net)
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

#error_reporting(E_ERROR);

require_once(t3lib_extMgm::extPath('dam_frontend').'/DAL/class.tx_damfrontend_DAL_documents.php');

$userObj = tslib_eidtools::initFeUser(); // Initialize FE user object
$GLOBALS['TSFE']->fe_user = $userObj;
$userObj->fetchGroupData();
tslib_eidtools::connectDB();
$docLogic = t3lib_div::makeInstance('tx_damfrontend_DAL_documents');
$docLogic->feuser = $userObj;
require_once(PATH_t3lib.'class.t3lib_stdgraphic.php');
require_once(PATH_t3lib.'class.t3lib_page.php');
require_once(PATH_t3lib.'class.t3lib_div.php');
require_once(PATH_tslib.'class.tslib_gifbuilder.php');




$prefixId = 'tx_damfrontend_pi1';
if (!$_REQUEST['docID']
	&& !$_POST[$prefixId]) {
	die ('<h1>Error</h1><p>You have no access to download a file. In this case no DocID was given!</p>');
}

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
			$filesToSend[] = array('file' => $tmp, 'filename' => $doc['file_name'], 'contenttype' => $doc['file_mime_type'] . '/' . $doc['file_mime_subtype']);
			$archive[]= $doc['file_path']. $doc['file_name'] ;
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

			createZip('download'.date('_Ymd_his').'.zip',$archive,1);
        	exit();
		break;
		case 'sendAsMail':
			if (sendMail($post['mail'],$archive,$post['mailTemplate'])) {
            	redirect($post['pid'],'&tx_damfrontend_pi1[msg]=mail_success');
            } 
            else {
				echo '<h1>Mail sent</h1>';
				echo '<p>Your mail was not successfully sent.</p>';
            } 
			
            exit();
		break;
		case 'sendZippedAsMail':
			$filename='typo3temp/download'.date('_Ymd_his').'.zip'; 
			createZip($filename,$archive,0);
			$file = array($filename);
			sendMail($post['mail'],$file);
			unlink($filename);
			echo '<h1>Mail sent</h1>';
			echo '<p>Your mail was successfully sent.</p>';
			exit();
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
			if (!sendFile($filesToSend[0]['file'], $filesToSend[0]['filename'], $filesToSend[0]['contenttype'])) {
				die ('<h1>Error</h1><p>The requested file was not found! Please contact the adminstrator and tell him that the id: '.$docID .' was not found');
			}
		break;
	}
}

	/**
	 * @return boolean return-value from t3lib_htmlmail -> send which returns
	 * usually return value from php mail()
	 */
	function sendMail($maildata, $attachments,$mailTemplate) {
		if (!$maildata['from']) $maildata['from']='norepley@'.t3lib_div::getIndpEnv('HTTP_HOST')	;
		if (!$maildata['subject']) $maildata['from']='Downloads';
		if (!$maildata['body']) $maildata['body']='Your download:';
		if (!$maildata['to']) die('<h1>Please set a recpient</h1><p>I\' sorry, without a recipient i can\'t send your mail</p>' );
		
		require_once(PATH_t3lib.'class.t3lib_htmlmail.php');
		$mailTemplate = str_replace( array("\r\n","\n","\r"), '<br>', $mailTemplate); // like nl2br() (sh 2010-03-28)
		$mailTemplate = strip_tags($mailTemplate,'<table><tr><td><p><b><br>'); // allow b and br (sh 2010-03-28)
		$maildata['htmlbody'] = str_replace( array("\r\n","\n","\r"), '<br>', $maildata['body'] ); // like nl2br() (sh 2010-03-28)
		$mailTemplate = str_replace('###MAIL_COMMENT###',strip_tags($maildata['htmlbody'],'<br>'),$mailTemplate); // allow br (sh 2010-03-28)
		$html_start='<html><head><title>Downloads</title></head><body>';
		$html_end='</body></html>';
		$htmlMail = t3lib_div::makeInstance('t3lib_htmlmail');
		$htmlMail->start();
		$htmlMail->recipient =strip_tags($maildata['to']);
		$htmlMail->subject =strip_tags($maildata['subject']);
		$htmlMail->from_email =strip_tags($maildata['from']);
		$htmlMail->addPlain(strip_tags($maildata['body']));
		foreach($attachments as $file) {
			$htmlMail->addAttachment($file);
		}
		
		$htmlMail->setHTML($htmlMail->encodeMsg($html_start.$mailTemplate.$html_end));
		
		return $htmlMail->send();
	}

	/**
	 * @return	[type]		...
	 */
	function createZip($filename,$files,$download=1) {
		#t3lib_div::debug($filename);
		require_once(t3lib_extMgm::extPath('dam_frontend').'archive.php');
		
		$zipfile = new zip_file($filename);
		$zipfile->set_options(array('inmemory' => $download, 'recurse' => 0, 'storepaths' => 0));
		$zipfile->add_files($files);
		$zipfile->create_archive();
		if ($download) $zipfile->download_file();
	}
	
	
	/**
	 * Splits configuration
	 * and returns array which could be used in sendFile
	 *
	 * @param	string		configuration
	 * @return	array		configuration Array
	 */
	function configuration2Array($configuration) {
		// x:1024;y:768;dpi:300;ext:jpg
		if ('' == $configuration) return array();
		$config = array();
		foreach (t3lib_div::trimExplode(';', $configuration, true) as $entry) {
			list($param, $value) = t3lib_div::trimExplode(':',$entry,true);
			$config[$param] = $value;
		}
		return $config;
	}

	/**
	 * @param	string		$file Filename including absolute path
	 * @param	[type]		$filename: ...
	 * @param	[type]		$contenttype: ...
	 * @return	[type]		...
	 */
	function sendFile($file, $filename, $contenttype) {
			// hook returns file( path / name), file in an array
		if ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['DAM_FRONTEND']['pushfile_sendFile']) {
			foreach($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['DAM_FRONTEND']['pushfile_sendFile'] as $_funcRef) {
  				if ($_funcRef) {
   					$params['filename']=$filename;
   					$params['file']=$file;
   					t3lib_div::callUserFunction($_funcRef,$params );
  				}
 			}
		}

		$filesize = filesize($file);
		if (!$filesize) {
			return false;
		}
		header("Content-type: ".$contenttype);
		#header("Content-type: application/force-download");
		header("Content-Transfer-Encoding: Binary");
		header("Content-length: ".$filesize);
#		header("Content-disposition: attachment; filename=\"".rawurlencode($filename)."\"");
		header("Content-disposition: attachment; filename=\"".$filename."\"");
		readfile($file);
		exit();
	}
	
	/**
	 * This function sanitized the value for width/height 
	 * @param string $size the value of width/height f.e. "100c-40", "30", "100m"
	 * @return string empty if it is not valid
	 */
	function sanitizeImageDimension($size) {
		// add whitespace check: 100m / 100c+-/[-100 - +100]
		$matches = array();
		preg_match('/^[0-9]*(m|c\+[0-9]*|c-[0-9]*|c)?$/', $size, $matches);
		return $matches[0];
	}

	/**
	 * @param	[type]		$filePath: ...
	 * @param	[type]		$configuration: ...
	 * @return	[type]		...
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
			// f.e. "jpg", "WEB" etc. - if it is not set, the filetype of the orignal file will be used
			'ext' => htmlspecialchars($configuration['ext']),
			'width' => sanitizeImageDimension($configuration['width']),
			'height' => sanitizeImageDimension($configuration['height']),
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

	function redirect($pid,$params='') {
		header('Location: '. t3lib_div::getIndpEnv('TYPO3_SITE_URL') .'?id='.$pid.$params);
		exit();
	}


// test for access to a file
$docID = intval(t3lib_div::GPvar('docID'));

if ($docID==0) {
	die('<h1>Error</h1><p>You have no access to download a file. In this case no correct DocID was given!</p>');
}

// check if a user has access to the selected categories (a user must have access to all categories that are selected)
if (!$docLogic->checkAccess($docID, 2)) {
	die('<h1>Sorry</h1><p>You do not have the right to download this file.');
}

// get the data of the selected document
$doc = $docLogic->getDocument($docID);

// check if a user has access to the dam record / file
if (!$docLogic->checkDocumentAccess($doc['fe_group'])) {
	die('<h1>Error</h1><p>You have no access to this file.');
}
$filePath = PATH_site.$doc['file_path'].$doc['file_name'];

if (!sendFile($filePath, $doc['file_name'], $doc['file_mime_type'] . '/' . $doc['file_mime_subtype'])) {
	die ('<h1>Sorry, file not found!</h1><p>The requested file was not found! Please contact the adminstrator and tell him that the id: '.$docID .' was not found');
}




?>
