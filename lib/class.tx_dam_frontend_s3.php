<?php
/**
 *  S3 DAM helper for file operations
 *
 *  @author Michael Cannon <michael@typo3vagabond.com>
 */

class tx_dam_frontend_s3 {
	public function filePostTrigger( $action, $id = null ) {
		// t3lib_div::devLog( $action, __FUNCTION__, 0, $id );	
		$db						= $GLOBALS['TYPO3_DB'];

		switch( $action ) {
		case 'upload':
			// FIXME doesn't work for changed file_names
			// FIXME no versioning done

			$target_file		= $id['target_file'];
			$file_name			= basename( $target_file );
			$file_path			= dirname( $target_file );
			$file_path			= str_replace( PATH_site, '', $file_path );
			$file_path			.= '/';

			$dataWhere			= 'file_name = "' . $file_name . '"';
			$dataWhere			.= ' AND file_path = "' . $file_path . '"';
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
