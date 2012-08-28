<?php
/**
 *  S3 DAM helper for file operations
 *
 *  @author Michael Cannon <michael@typo3vagabond.com>
 */

class tx_dam_frontend_s3 {
	public function filePostTrigger( $action, $id = null ) {
		t3lib_div::devLog( $action, __FUNCTION__, 0, false );
		switch( $action ) {
		case 'upload':
			t3lib_div::devLog( true, __FUNCTION__, 0, $id );	
			break;

		default:
			break;
		}
	}
}

?>
