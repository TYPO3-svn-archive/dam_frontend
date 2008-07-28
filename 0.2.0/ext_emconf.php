<?php

########################################################################
# Extension Manager/Repository config file for ext: "dam_frontend"
#
# Auto generated 25-07-2008 16:08
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
	'version' => '0.1.1',
	'dependencies' => 'dam',
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
			'dam' => '0.0.0-1.0.99',
			'php' => '4.0.0-0.0.0',
			'typo3' => '4.0.0-0.0.0',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'_md5_values_when_last_written' => 'a:45:{s:9:"ChangeLog";s:4:"f422";s:10:"README.txt";s:4:"ce2d";s:12:"ext_icon.gif";s:4:"69ef";s:17:"ext_localconf.php";s:4:"89fc";s:15:"ext_php_api.dat";s:4:"7e27";s:14:"ext_tables.php";s:4:"34ce";s:14:"ext_tables.sql";s:4:"6240";s:15:"flexform_ds.xml";s:4:"33d0";s:16:"locallang_db.xml";s:4:"47f0";s:17:"locallang_tca.xml";s:4:"e4dc";s:12:"pushfile.php";s:4:"ebba";s:7:"tca.php";s:4:"422e";s:19:"tx_dam_flexFunc.php";s:4:"6667";s:43:"DAL/class.tx_damfrontend_DAL_categories.php";s:4:"5c01";s:42:"DAL/class.tx_damfrontend_DAL_documents.php";s:4:"d867";s:44:"DAL/class.tx_damfrontend_baseSessionData.php";s:4:"2b4e";s:36:"DAL/class.tx_damfrontend_catList.php";s:4:"cf39";s:36:"DAL/class.tx_damfrontend_docList.php";s:4:"aed9";s:40:"DAL/class.tx_damfrontend_filterState.php";s:4:"767c";s:38:"DAL/class.tx_damfrontend_listState.php";s:4:"01f3";s:14:"doc/manual.sxw";s:4:"7988";s:19:"doc/wizard_form.dat";s:4:"87c9";s:20:"doc/wizard_form.html";s:4:"26f6";s:45:"frontend/class.tx_damfrontend_catTreeView.php";s:4:"a952";s:43:"frontend/class.tx_damfrontend_rendering.php";s:4:"cb43";s:32:"pi1/class.tx_damfrontend_pi1.php";s:4:"4ea0";s:17:"pi1/locallang.xml";s:4:"530d";s:13:"pi1/style.css";s:4:"842c";s:17:"pi1/template.html";s:4:"a689";s:24:"pi1/static/editorcfg.txt";s:4:"c15d";s:32:"pi2/class.tx_damfrontend_pi2.php";s:4:"b322";s:17:"pi2/locallang.xml";s:4:"c5e0";s:24:"pi2/static/editorcfg.txt";s:4:"375a";s:20:"pi2/static/setup.txt";s:4:"bc30";s:11:"res/ddl.css";s:4:"1f06";s:13:"res/style.css";s:4:"0eb9";s:18:"res/tmpl_list.html";s:4:"e599";s:31:"res/ico/application_default.png";s:4:"b307";s:27:"res/ico/application_pdf.png";s:4:"8b7e";s:19:"res/ico/default.png";s:4:"ce6f";s:18:"res/ico/hidden.png";s:4:"5894";s:25:"res/ico/image_default.png";s:4:"c549";s:24:"res/ico/text_default.png";s:4:"cf7d";s:20:"static/constants.txt";s:4:"04a8";s:16:"static/setup.txt";s:4:"2b12";}',
	'suggests' => array(
	),
);

?>