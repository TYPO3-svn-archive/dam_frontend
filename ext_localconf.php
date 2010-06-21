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
?>