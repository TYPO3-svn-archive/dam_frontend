<?php
/**
 *  S3 DAM helper for file operations
 *
 *  @author Michael Cannon <michael@typo3vagabond.com>
 */

class tx_dam_frontend_s3 {
	public function processPostTrigger( $action, $info = null ) {
		// t3lib_div::devLog( $action, __FUNCTION__, 0, $info );	
		$db						= $GLOBALS['TYPO3_DB'];

		switch( $action ) {
		case 'copyFile':
		case 'moveFile':
		case 'replaceFile':
			// FIXME no versioning done

			$uid				= $info['uid'];

			$dataWhere			= 'uid = ' . $uid;
			$dataWhere			.= ' AND tx_damfrontend_s3 != ""';
			$dataRecord			= array(
				'tx_damfrontend_s3'		=> "",
			);

			// $dataUpdate			= $db->UPDATEquery(
			$dataUpdate			= $db->exec_UPDATEquery(
				'tx_dam',
				$dataWhere,
				$dataRecord
			);
			// t3lib_div::devLog( $dataUpdate, __FUNCTION__, 0, false );	
			break;

		default:
			break;
		}
	}
}

?>
