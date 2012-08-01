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

	public $db					= null;
	public $logUid				= null;
	public $s3					= null;

	// additional fields
	public $uploadS3			= null;
	public $storagePid			= null;
	public $importLimit			= null;
	public $s3AccessKey			= null;
	public $s3SecretKey			= null;
	public $s3Bucket			= null;

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
		if ( ! $this->connectS3() ) {
			return false;
		}

		// get media records
		$medias					= $this->getMediaRecords();

		foreach ( $medias as $media ) {
			if ( $this->push2s3( $media ) ) {
				$url						= $this->getS3Link( $media );
				$media['tx_damfrontend_s3']	= $url;
				t3lib_div::devLog( true, __FUNCTION__, 0, $media );	
				// update media record with S3 address
				// $this->updateMediaRecord( $media );
			}
		}

		return true;
	}

	// looks like
	// https://s3-eu-west-1.amazonaws.com/cubeware/Lizenzverfahren_von_MIS_Alea_4.1.pdf
	private function getS3Link( $media ) {
		t3lib_div::devLog( true, __FUNCTION__, 0, func_get_args() );
		$info					= $this->s3->getObjectInfo( $this->s3Bucket, $media['file_name'] );
		t3lib_div::devLog( true, __FUNCTION__, 0, array($info) );	

		// TODO return real link like https://s3-eu-west-1.amazonaws.com/cubeware/Lizenzverfahren_von_MIS_Alea_4.1.pdf
		return $info->link;
	}

	private function connectS3() {
		$success				= false;

		// making S3 instance
		if ( is_readable( t3lib_extMgm::extPath('dam_frontend') . 'lib/S3.php' ) ) {
			require_once( t3lib_extMgm::extPath('dam_frontend') . 'lib/S3.php' );
			$this->s3			= t3lib_div::makeInstance( 'S3', $this->s3AccessKey, $this->s3SecretKey );
			$success			= is_object( $this->s3 );
		}

		return $success;
	}

	private function getMediaRecords() {
		$select					= "
			m.uid,
			m.file_name,
			CONCAT(m.file_path, m.file_name) file_path
		";
		$from					= 'tx_dam m';
		$where					= 'NOT m.deleted
			AND NOT m.hidden
			AND NOT m.hidden
			AND m.tx_damfrontend_uses3
		';
		$where					.= ' AND m.pid = ' . $this->storagePid;
		$group					= '';
		$order					= '';
		$limit					= $this->importLimit;

		$records				= $this->db->exec_SELECTgetRows( $select, $from, $where, $group, $order, $limit );
		// $query					= $this->db->SELECTquery( $select, $from, $where, $group, $order, $limit );
		// t3lib_div::devLog( $query, __FUNCTION__, 0, $records );	

		if ( empty( $records ) )
			return false;

		return $records;
	}

	private function push2s3( $media ) {
		$file_path				= PATH_site . $media['file_path'];
		$result					= $this->s3->putObjectFile( $file_path, $this->s3Bucket, $media['file_name'] );

		// TODO fixme
		// $success				= $result ? true : false;
		$success				= true;

		return $success;
	}

	private function updateMediaRecord( $media ) {
		$dataWhere				= 'uid = ' . $media['uid'];
		$dataRecord				= array(
			'tx_damfrontend_s3'		=> $media['tx_damfrontend_s3']
		);

		$dataUpdate				= $this->db->exec_UPDATEquery(
			'tx_dam',
			$dataWhere,
			$dataRecord
		);

		return $dataUpdate;
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