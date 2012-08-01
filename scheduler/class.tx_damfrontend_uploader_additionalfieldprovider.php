<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2012 Michael Cannon <michael.cannon@in2code.de>
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

/**
 * Aditional fields provider class for usage with the Dam Frontend uploader task
 *
 * @author		Michael Cannon <michael.cannon@in2code.de>
 * @package		TYPO3
 * @subpackage	damfrontend
 *
 * $Id$
 */
class tx_damfrontend_uploader_AdditionalFieldProvider implements tx_scheduler_AdditionalFieldProvider {

	/**
	 * This method is used to define new fields for adding or editing a task
	 * In this case, it adds an source and archive directory and XML Filename fields
	 *
	 * @param	array					$taskInfo: reference to the array containing the info used in the add/edit form
	 * @param	object					$task: when editing, reference to the current task object. Null when adding.
	 * @param	tx_scheduler_Module		$parentObject: reference to the calling object (Scheduler's BE module)
	 * @return	array					Array containg all the information pertaining to the additional fields
	 *									The array is multidimensional, keyed to the task class name and each field's id
	 *									For each field it provides an associative sub-array with the following:
	 *										['code']		=> The HTML code for the field
	 *										['label']		=> The label of the field (possibly localized)
	 *										['cshKey']		=> The CSH key for the field
	 *										['cshLabel']	=> The code of the CSH label
	 */
	public function getAdditionalFields(array &$taskInfo, $task, tx_scheduler_Module $parentObject) {
		$additionalFields		= array();

		if (empty($taskInfo['damfrontend_storagePid'])) {
			if ($parentObject->CMD == 'add') {
				// In case of new task and if field is empty, set default storagePid address
				// $taskInfo['damfrontend_storagePid'] = $GLOBALS['BE_USER']->user['damfrontend_storagePid'];
				$taskInfo['damfrontend_storagePid'] = '';
			} elseif ($parentObject->CMD == 'edit') {
				// In case of edit, and editing a test task, set to internal value if not data was submitted already
				$taskInfo['damfrontend_storagePid'] = $task->storagePid;
			} else {
				// Otherwise set an empty value, as it will not be used anyway
				$taskInfo['damfrontend_storagePid'] = '';
			}
		}

		$fieldID				= 'damfrontend_storagePid';
		$fieldCode				= '<input type="text" name="tx_scheduler[damfrontend_storagePid]" id="' . $fieldID . '" value="' . $taskInfo['damfrontend_storagePid'] . '" size="4" />';
		$additionalFields[$fieldID]	= array(
			'code'				=> $fieldCode,
			'label'				=> 'Storage PID',
			'cshKey'			=> '_MOD_tools_txschedulerM1',
			'cshLabel'			=> $fieldID
		);

		if (empty($taskInfo['damfrontend_importLimit'])) {
			if ($parentObject->CMD == 'add') {
				// In case of new task and if field is empty, set default importLimit address
				// $taskInfo['damfrontend_importLimit'] = $GLOBALS['BE_USER']->user['damfrontend_importLimit'];
				$taskInfo['damfrontend_importLimit'] = '';
			} elseif ($parentObject->CMD == 'edit') {
				// In case of edit, and editing a test task, set to internal value if not data was submitted already
				$taskInfo['damfrontend_importLimit'] = $task->importLimit;
			} else {
				// Otherwise set an empty value, as it will not be used anyway
				$taskInfo['damfrontend_importLimit'] = '';
			}
		}

		$fieldID				= 'damfrontend_importLimit';
		$fieldCode				= '<input type="text" name="tx_scheduler[damfrontend_importLimit]" id="' . $fieldID . '" value="' . $taskInfo['damfrontend_importLimit'] . '" size="4" />';
		$additionalFields[$fieldID]	= array(
			'code'				=> $fieldCode,
			'label'				=> 'Import Limit',
			'cshKey'			=> '_MOD_tools_txschedulerM1',
			'cshLabel'			=> $fieldID
		);

		return $additionalFields;
	}

	/**
	 * This method checks any additional data that is relevant to the specific task
	 * If the task class is not relevant, the method is expected to return true
	 *
	 * @param	array					$submittedData: reference to the array containing the data submitted by the user
	 * @param	tx_scheduler_Module		$parentObject: reference to the calling object (Scheduler's BE module)
	 * @return	boolean					True if validation was ok (or selected class is not relevant), false otherwise
	 */
	public function validateAdditionalFields(array &$submittedData, tx_scheduler_Module $parentObject) {
		// t3lib_div::devLog('submittedData ' . print_r( $submittedData, true ) , 'damfrontend', 0);
		$result					= true;

		$submittedData['damfrontend_storagePid'] = is_numeric( $submittedData['damfrontend_storagePid'] ) ? intval( $submittedData['damfrontend_storagePid'] ) : '';
		$submittedData['damfrontend_importLimit'] = is_numeric( $submittedData['damfrontend_importLimit'] ) ? intval( $submittedData['damfrontend_importLimit'] ) : '';

		if ( empty( $submittedData['damfrontend_storagePid'] ) ) {
			$parentObject->addMessage('What\'s the storage PID?', t3lib_FlashMessage::ERROR);
			$result				= false;
		} elseif ( ! is_int( $submittedData['damfrontend_storagePid'] ) 
			|| 0 > $submittedData['damfrontend_storagePid'] ) {
			$parentObject->addMessage('What\'s the storage PID?', t3lib_FlashMessage::ERROR);
			$result				= false;
		}
	   
		return $result;
	}

	/**
	 * This method is used to save any additional input into the current task object
	 * if the task class matches
	 *
	 * @param	array				$submittedData: array containing the data submitted by the user
	 * @param	tx_scheduler_Task	$task: reference to the current task object
	 * @return	void
	 */
	public function saveAdditionalFields(array $submittedData, tx_scheduler_Task $task) {
		$task->storagePid		= $submittedData['damfrontend_storagePid'];
		$task->importLimit		= $submittedData['damfrontend_importLimit'];
	}
}

if (defined('TYPO3_MODE') && isset($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/dam_frontend/scheduler/class.tx_damfrontend_uploader_additionalfieldprovider.php'])) {
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/dam_frontend/scheduler/class.tx_damfrontend_uploader_additionalfieldprovider.php']);
}

?>