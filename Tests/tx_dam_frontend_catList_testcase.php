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

require_once(t3lib_extMgm::extPath('dam_frontend').'Tests/class.tx_damfrontend_testlib.php');

/**
 * Testcase 
 *
 * @author	
 * @package 
 * @subpackage 
 */
class tx_dam_frontend_catList_testcase extends tx_damfrontend_testlib {

	/**
	 * @test
	 */
	public function checkTestcase2() {
		$testString = "ja";
		$expectedString = "ja";
		$actualString = "ja";

		$this->assertEquals($expectedString, $actualString);
	}

}

?>