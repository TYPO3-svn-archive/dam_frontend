<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2012 Michael Cannon (michael.cannon@in2code.de)
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
 * Class "tx_damfrontend_uploader" provides Dam Frontend upload procedures
 *
 * @author		Michael Cannon <michael.cannon@in2code.de>
 * @package		TYPO3
 * @subpackage	damfrontend
 *
 * $Id$
 */
class tx_damfrontend_uploader extends tx_scheduler_Task {

	public $storagePid			= -1;
	public $importLimit			= null;

	// local and remote db connections
	public $db					= null;
	public $uploaderLog			= 'tx_damfrontend_uploader_log';
	public $logUid				= null;

	public $importArticles		= null;
	public $importArticleFluff	= null;
	public $importContacts		= null;
	public $importProjects		= null;

	/**
	 * Function executed from the Scheduler.
	 *
	 * @return	boolean	true success
	 */
	public function execute() {
		set_time_limit( 0 );

		$success				= false;
		$this->cruser_id		= $GLOBALS['BE_USER']->user['uid'];
		$this->importLimit		= $this->importLimit ? $this->importLimit : '';

		if ( empty( $this->storagePid ) ) {
			return $success;
		}

		$this->uploadS3			= true;

		try {
			if ( ! $this->connectDbs() )
				return $success;

			if ( $this->uploadS3 ) {
				$result				= $this->uploadS3();
				if ( empty( $result ) )
					return $success;
			}


			$success			= true;
		} catch ( Exception $e ) {
			throw new t3lib_exception( $e->getMessage() );
		}

		return $success;
	}

	public function uploadS3() {
		return true;
	}

	public function logUploaderRelate( $relateUid, $relateTable ) {
		if ( empty( $this->logUid ) && is_numeric( $this->logUid ) )
			return;

		$table					= $this->uploaderLog;
		$dataRecord				= array(
			'old_uid'			=> $relateUid,
			'old_table'			=> $relateTable,
		);

		$updateWhere			= 'uid = ' . $this->logUid;

		$dataUpdate				= $this->db->exec_UPDATEquery(
			$table,
			$updateWhere,
			$dataRecord
		);

		if ( ! $dataUpdate ) {
			$dataUpdate			= $this->db->UPDATEquery(
				$table,
				$updateWhere,
				$dataRecord
			);
			t3lib_div::devLog($dataUpdate, __FUNCTION__, 0);
			t3lib_div::devLog($this->db->sql_error(), __FUNCTION__, 0);
		}
	}

	public function logUploaderInsert( $importUid, $importTable ) {
		$table					= $this->uploaderLog;
		$dataRecord				= array(
			'new_uid'			=> $importUid,
			'new_table'			=> $importTable,
		);

		$dataInsert				= $this->db->exec_INSERTquery(
			$table,
			$dataRecord
		);

		if ( $dataInsert ) {
			$dataUid			= $this->db->sql_insert_id();
		} else {
			$dataInsert			= $this->db->INSERTquery(
				$table,
				$dataRecord
			);
			t3lib_div::devLog($dataInsert, __FUNCTION__, 0);
			t3lib_div::devLog($this->db->sql_error(), __FUNCTION__, 0);
			$dataUid			= false;
		}

		$this->logUid	= $dataUid;
	}

	public function insertOrUpdateQuery( $table, $updateWhere, $dataRecord ) {
		$dataSelect				= $this->db->exec_SELECTgetSingleRow(
			'uid',
			$table,
			$updateWhere
		);

		if ( ! is_null( $dataSelect ) ) {
			// insert
			$dataInsert			= $this->db->exec_INSERTquery(
				$table,
				$dataRecord
			);

			if ( $dataInsert ) {
				$dataUid		= $this->db->sql_insert_id();
				$this->logUploaderInsert( $dataUid, $table );
			} else {
				$dataInsert		= $this->db->INSERTquery(
					$table,
					$dataRecord
				);
				t3lib_div::devLog($dataInsert, __FUNCTION__, 0);
				t3lib_div::devLog($this->db->sql_error(), __FUNCTION__, 0);
				$dataUid		= false;
			}
		} else {
			// update
			$dataUpdate			= $this->db->exec_UPDATEquery(
				$table,
				$updateWhere,
				$dataRecord
			);
			$dataUid			= $dataSelect['uid'];
			$this->logUploaderUid( $dataUid, $table );
		}

		return $dataUid;
	}

	public function logUploaderUid( $importUid, $importTable ) {
		$table					= $this->uploaderLog;
		$select					= 'uid';
		$from					= $table;
		$where					= "new_uid = {$importUid} AND new_table LIKE '{$importTable}'";

		$dataSelect				= $this->db->exec_SELECTgetSingleRow(
			$select,
			$from,
			$where
		);

		if ( $dataSelect ) {
			$dataUid			= $dataSelect['uid'];
		} else {
			$dataSelect			= $this->db->SELECTquery(
				$select,
				$from,
				$where
			);

			t3lib_div::devLog($dataSelect, __FUNCTION__, 0);
			t3lib_div::devLog($this->db->sql_error(), __FUNCTION__, 0);
			$dataUid			= false;
		}

		$this->logUid	= $dataUid;
	}

	public function uploaderLookup( $uid, $table, $lookupNew = true, $type = false ) {
		// t3lib_div::devLog( true, __FUNCTION__, 0, false );	
		if ( $lookupNew ) {
			$select				= 'new_uid';
			$where				= "old_uid = {$uid} AND old_table = '{$table}'";
		} else {
			$select				= 'olduid';
			$where				= "new_uid = {$uid} AND new_table = '{$table}'";
		}

		if ( $type ) {
			$where				.= " AND new_table = '{$type}'";
		}

		$from					= $this->uploaderLog;
		$result					= $this->db->exec_SELECTgetRows( $select, $from, $where );
		// $query					= $this->db->SELECTquery( $select, $from, $where );
		// t3lib_div::devLog( $query, __FUNCTION__, 0, false );	

		// nothing to relate
		if ( ! count( $result ) )
			return false;

		return $result[0][$select];
	}

	public function connectDbs() {
		$this->db				= $GLOBALS['TYPO3_DB'];

		return $this->db->isConnected();
	}

	/**
	 * Shows up underneath scheduler task title
	 *
	 * @return	string	Information to display
	 */
	public function getAdditionalInformation() {
		return '';
	}
}

if (defined('TYPO3_MODE') && isset($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/dam_frontend/scheduler/class.tx_damfrontend_importer.php'])) {
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/dam_frontend/scheduler/class.tx_damfrontend_uploader.php']);
}

?>