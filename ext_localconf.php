<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

  ## Extending TypoScript from static template uid=43 to set up userdefined tag:
t3lib_extMgm::addTypoScript($_EXTKEY,'editorcfg','
	tt_content.CSS_editor.ch.tx_damfrontend_pi1 = < plugin.tx_damfrontend_pi1.CSS_editor
',43);

// Adding a static file to the script
t3lib_extMgm::addStaticFile($_EXTKEY,"static/","DAM Frontend Static Template");

t3lib_extMgm::addPItoST43($_EXTKEY,'pi1/class.tx_damfrontend_pi1.php','_pi1','list_type',1);



  ## Extending TypoScript from static template uid=43 to set up userdefined tag:
t3lib_extMgm::addTypoScript($_EXTKEY,'editorcfg','
    tt_content.CSS_editor.ch.tx_damfrontend_pi2 = < plugin.tx_damfrontend_pi2.CSS_editor
',43);

t3lib_extMgm::addPItoST43($_EXTKEY,'pi2/class.tx_damfrontend_pi2.php','_pi2','list_type',1);

t3lib_extMgm::addPItoST43($_EXTKEY,'pi3/class.tx_damfrontend_pi3.php','_pi3','list_type',1);

// including the eID for the pushfile
$TYPO3_CONF_VARS['FE']['eID_include']['dam_frontend_push'] = t3lib_extMgm::extPath($_EXTKEY).'pushfile.php';

// add hook for pi3
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['DAM_FRONTEND']['RENDER_DAM_RECORD'][]  = 'EXT:dam_frontend/pi3/class.tx_damfrontend_basketCaseRendering.php:tx_damfrontend_basketCaseRendering';
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['DAM_FRONTEND']['RENDER_SINGLE_VIEW'][] = 'EXT:dam_frontend/pi3/class.tx_damfrontend_basketCaseRendering.php:tx_damfrontend_basketCaseRendering'; 

// register uploader for scheduler
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks']['tx_damfrontend_uploader'] = array(
	'extension'			=> $_EXTKEY,
	'title'				=> 'Dam Frontend Uploader',
	'description'		=> 'Upload media record files to Amazon S3',
	'additionalFields'	=> 'tx_damfrontend_uploader_AdditionalFieldProvider'
);

// S3 link remover on file upload
require_once( t3lib_extMgm::extPath( $_EXTKEY ) . 'lib/class.tx_dam_frontend_s3.php' );
$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['dam']['processTriggerClasses'][] = 'tx_dam_frontend_s3';

?>