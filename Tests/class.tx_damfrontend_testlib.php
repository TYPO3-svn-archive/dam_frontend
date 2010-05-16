<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2010 DAM Frontend Team 
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

require_once(PATH_t3lib.'class.t3lib_div.php');
require_once(PATH_tslib.'class.tslib_pibase.php');
require_once(PATH_tslib.'class.tslib_content.php');


// references to the DAL
require_once(t3lib_extMgm::extPath('dam_frontend').'/DAL/class.tx_damfrontend_DAL_documents.php');
require_once(t3lib_extMgm::extPath('dam_frontend').'/DAL/class.tx_damfrontend_DAL_categories.php');
require_once(t3lib_extMgm::extPath('dam_frontend').'/DAL/class.tx_damfrontend_catList.php');
require_once(t3lib_extMgm::extPath('dam_frontend').'/DAL/class.tx_damfrontend_filterState.php');
require_once(t3lib_extMgm::extPath('dam_frontend') . '/DAL/class.tx_damfrontend_listState.php');
require_once(t3lib_extMgm::extPath('dam_frontend') . '/frontend/class.tx_damfrontend_catTreeView.php');
require_once(t3lib_extMgm::extPath('dam_frontend') . '/frontend/class.tx_damfrontend_catTreeViewAdvanced.php');
require_once(t3lib_extMgm::extPath('dam_frontend') . '/frontend/class.tx_damfrontend_rendering.php');
require_once(t3lib_extMgm::extPath('dam_frontend') . '/pi1/class.tx_damfrontend_pi1.php');


class tx_damfrontend_testlib extends tx_phpunit_testcase { 

	/**
	 * backup of the global variables _GET, _POST, _SERVER
	 *
	 * @var array
	 */
	private $backupGlobalVariables;

	/**
	 * @var tx_damfrontend_pi1
	 */
	private $tx_damfrontend_pi1;

	public function setUp() {
		$this->backupGlobalVariables = array(
			'_GET' => $_GET,
			'_POST' => $_POST,
			'_SERVER' => $_SERVER,
			'TYPO3_CONF_VARS' =>  $GLOBALS['TYPO3_CONF_VARS'],
		);
		$this->tx_damfrontend_pi1 = t3lib_div::makeInstance('tx_damfrontend_pi1');	
	}

	public function tearDown() {
		foreach ($this->backupGlobalVariables as $key => $data) {
			$GLOBALS[$key] = $data;
		}
		unset(
			$this->tx_damfrontend_pi1 
		);
	}
	
	
	/***************************************
	 *
	 *	 Fixtures
	 *
	 ***************************************/

	/**
	 * 
	 */
	/*
	protected function addFixturePathToFilemount () {		
		$filepath = $this->getFixtureFilename();
		$filename = tx_dam::file_basename($filepath);
		$testpath = tx_dam::file_dirname($filepath);
		
		$this->tempSave['fileadminDir'] = $GLOBALS['TYPO3_CONF_VARS']['BE']['fileadminDir'];
		$GLOBALS['TYPO3_CONF_VARS']['BE']['fileadminDir'] = tx_dam::path_makeRelative($testpath);
		
		$GLOBALS['FILEMOUNTS']['__unittest'] = array(
			'name' => (basename($testpath).'/'),
			'path'=> $testpath,
			'type' => '',
		);
	}
	*/	
	
	
	/**
	 * 
	 */
	/*
	protected function removeFixturePathFromFilemount () {
		global $FILEMOUNTS;
		
		$GLOBALS['TYPO3_CONF_VARS']['BE']['fileadminDir'] = $this->tempSave['fileadminDir'];	
		
		unset ($GLOBALS['FILEMOUNTS']['__unittest']);
	}
	*/

}

?>