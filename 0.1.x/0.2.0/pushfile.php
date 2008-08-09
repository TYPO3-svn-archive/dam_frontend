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

if (!$_REQUEST['docID']) die ('<h1>Error</h1><p>You have no access to download a file. In this case no DocID was given!</p>');

error_reporting(E_ERROR);

define('TYPO3_OS', stristr(PHP_OS,'win')&&!stristr(PHP_OS,'darwin')?'WIN':'');
define('TYPO3_MODE','FE');
define('PATH_thisScript',str_replace('//','/', str_replace('\\','/', (php_sapi_name()=='cgi'||php_sapi_name()=='isapi' ||php_sapi_name()=='cgi-fcgi')&&($_SERVER['ORIG_PATH_TRANSLATED']?$_SERVER['ORIG_PATH_TRANSLATED']:$_SERVER['PATH_TRANSLATED'])? ($_SERVER['ORIG_PATH_TRANSLATED']?$_SERVER['ORIG_PATH_TRANSLATED']:$_SERVER['PATH_TRANSLATED']):$_SERVER['SCRIPT_FILENAME'])));
define('PATH_site',preg_replace("/(typo3conf|typo3)\/ext\/DAM_Frontend/i", '', dirname(PATH_thisScript)));
define('PATH_t3lib', PATH_site.'t3lib/');
define('PATH_tslib', PATH_site.'typo3/sysext/cms/tslib/');
define('PATH_typo3conf', PATH_site.'typo3conf/');

require_once(PATH_t3lib.'class.t3lib_div.php');
require_once(PATH_t3lib.'class.t3lib_extmgm.php');
require_once(PATH_t3lib.'class.t3lib_db.php');
require_once(PATH_t3lib.'config_default.php');
require_once(PATH_tslib."class.tslib_fe.php");
require_once(PATH_t3lib."class.t3lib_cs.php");
require_once(PATH_t3lib."class.t3lib_userauth.php");
require_once(PATH_tslib."class.tslib_feuserauth.php");
require_once(PATH_t3lib."class.t3lib_befunc.php");
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
// test für den Zugriff auf eine Datei
$docID = intval(t3lib_div::GPvar('docID'));
if ($docID==0) {
	die('<h1>Error</h1><p>You have no access to download a file. In this case no correct DocID was given!</p>');
}
if ($docLogic->checkAccess($docID, 1)) {
	$doc = $docLogic->getDocument($docID);
	$filePath = PATH_site.$doc['file_path'].$doc['file_name'];
	if (file_exists($filePath)){
		$filesize = filesize($filePath);
		header("Content-type: application/force-download");
		header("Content-Transfer-Encoding: Binary");
		header("Content-length: ".$filesize);
		header("Content-disposition: attachment; filename=\"".basename($filePath)."\"");
		$fp = fopen($filePath,"rb");
		echo fread($fp, $filesize);
		fclose($fp);
	}
	else{
		die ('<h1>Error</h1><p>The requested file was not found! Please contact the adminstrator and tell him that the id: '.$docID .' was not found');
	}
} else {
	die('<h1>Error</h1><p>You have no access to download this file.');
}
?>
