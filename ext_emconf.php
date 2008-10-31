<?php

########################################################################
# Extension Manager/Repository config file for ext: "dam_frontend"
#
# Auto generated 25-10-2008 13:01
#
# Manual updates:
# Only the data in the array - anything else is removed by next write.
# "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'DAM Frontend',
	'description' => 'Shows the dam category tree in frontend, so users can select files by combining dam categories. Files are delivered by a pushfile, so that the fileadmin directory can be secured by a .htaccess file. A second plugin shows selected (by an author) dam files. Please read the manual for more informations.',
	'category' => 'plugin',
	'shy' => 0,
	'version' => '0.2.1',
	'dependencies' => '',
	'conflicts' => '',
	'priority' => '',
	'loadOrder' => '',
	'module' => '',
	'state' => 'alpha',
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => '',
	'clearcacheonload' => 0,
	'lockType' => '',
	'author' => 'Martin Baum, Stefan Busemann',
	'author_email' => 'typo3@in2form.com',
	'author_company' => 'in2form.com',
	'CGLcompliance' => '',
	'CGLcompliance_note' => '',
	'constraints' => array(
		'depends' => array(
			'php' => '4.0.0-0.0.0',
			'typo3' => '4.0.0-0.0.0',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'_md5_values_when_last_written' => 'a:71:{s:9:"ChangeLog";s:4:"7a75";s:10:"README.txt";s:4:"447a";s:12:"ext_icon.gif";s:4:"69ef";s:17:"ext_localconf.php";s:4:"89fc";s:15:"ext_php_api.dat";s:4:"9bb6";s:14:"ext_tables.php";s:4:"eca0";s:14:"ext_tables.sql";s:4:"92e4";s:15:"flexform_ds.xml";s:4:"1b7e";s:24:"flexform_ds_disabled.xml";s:4:"8bd6";s:16:"locallang_db.xml";s:4:"5682";s:17:"locallang_tca.xml";s:4:"ed8b";s:12:"pushfile.php";s:4:"ebba";s:7:"tca.php";s:4:"422e";s:19:"tx_dam_flexFunc.php";s:4:"70c1";s:43:"DAL/class.tx_damfrontend_DAL_categories.php";s:4:"441a";s:42:"DAL/class.tx_damfrontend_DAL_documents.php";s:4:"9f8e";s:44:"DAL/class.tx_damfrontend_baseSessionData.php";s:4:"157a";s:36:"DAL/class.tx_damfrontend_catList.php";s:4:"fd11";s:36:"DAL/class.tx_damfrontend_docList.php";s:4:"f337";s:40:"DAL/class.tx_damfrontend_filterState.php";s:4:"c7c7";s:38:"DAL/class.tx_damfrontend_listState.php";s:4:"910b";s:14:"doc/manual.sxw";s:4:"3185";s:19:"doc/wizard_form.dat";s:4:"87c9";s:20:"doc/wizard_form.html";s:4:"26f6";s:45:"frontend/class.tx_damfrontend_catTreeView.php";s:4:"a0bc";s:52:"frontend/class.tx_damfrontend_categorisationTree.php";s:4:"92cc";s:43:"frontend/class.tx_damfrontend_rendering.php";s:4:"e10f";s:32:"pi1/class.tx_damfrontend_pi1.php";s:4:"c576";s:17:"pi1/locallang.xml";s:4:"870c";s:13:"pi1/style.css";s:4:"842c";s:17:"pi1/template.html";s:4:"f8c6";s:24:"pi1/static/editorcfg.txt";s:4:"c15d";s:32:"pi2/class.tx_damfrontend_pi2.php";s:4:"1a5b";s:17:"pi2/locallang.xml";s:4:"c5e0";s:24:"pi2/static/editorcfg.txt";s:4:"375a";s:20:"pi2/static/setup.txt";s:4:"bc30";s:11:"res/ddl.css";s:4:"1f06";s:13:"res/style.css";s:4:"0eb9";s:18:"res/tmpl_list.html";s:4:"e599";s:31:"res/ico/application_default.png";s:4:"b307";s:27:"res/ico/application_pdf.png";s:4:"8b7e";s:19:"res/ico/default.png";s:4:"ce6f";s:18:"res/ico/hidden.png";s:4:"5894";s:25:"res/ico/image_default.png";s:4:"c549";s:24:"res/ico/text_default.png";s:4:"cf7d";s:20:"static/constants.txt";s:4:"04a8";s:16:"static/setup.txt";s:4:"53a8";s:14:"temp/ChangeLog";s:4:"2e2a";s:15:"temp/README.txt";s:4:"447a";s:19:"temp/ext_emconf.php";s:4:"6bbc";s:17:"temp/ext_icon.gif";s:4:"69ef";s:22:"temp/ext_localconf.php";s:4:"89fc";s:20:"temp/ext_php_api.dat";s:4:"9bb6";s:19:"temp/ext_tables.php";s:4:"eca0";s:19:"temp/ext_tables.sql";s:4:"92e4";s:20:"temp/flexform_ds.xml";s:4:"1b7e";s:29:"temp/flexform_ds_disabled.xml";s:4:"8bd6";s:21:"temp/locallang_db.xml";s:4:"5682";s:22:"temp/locallang_tca.xml";s:4:"ed8b";s:17:"temp/pushfile.php";s:4:"ebba";s:12:"temp/tca.php";s:4:"422e";s:24:"temp/tx_dam_flexFunc.php";s:4:"70c1";s:29:"temp/pi1/static/editorcfg.txt";s:4:"c15d";s:29:"temp/pi2/static/editorcfg.txt";s:4:"375a";s:25:"temp/pi2/static/setup.txt";s:4:"bc30";s:36:"temp/res/ico/application_default.png";s:4:"b307";s:32:"temp/res/ico/application_pdf.png";s:4:"8b7e";s:24:"temp/res/ico/default.png";s:4:"ce6f";s:23:"temp/res/ico/hidden.png";s:4:"5894";s:30:"temp/res/ico/image_default.png";s:4:"c549";s:29:"temp/res/ico/text_default.png";s:4:"cf7d";}',
	'suggests' => array(
	),
);

?>