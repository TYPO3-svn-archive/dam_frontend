<?php
/*
 * Register necessary class names with autoloader
 *
 * $Id$
 */
$extensionPath = t3lib_extMgm::extPath('dam_frontend');
return array(
	'tx_damfrontend_uploader' => $extensionPath . 'scheduler/class.tx_damfrontend_uploader.php',
	'tx_dam_frontend_uploader_additionalfieldprovider' => $extensionPath . 'scheduler/class.tx_damfrontend_uploader_additionalfieldprovider.php',
);
?>