<?php

########################################################################
# Extension Manager/Repository config file for ext: "dam_frontend"
#
# Auto generated 09-03-2009 22:27
#
# Manual updates:
# Only the data in the array - anything else is removed by next write.
# "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'DAM Frontend',
	'description' => 'Download, upload and edit DAM files via frontend. Shows the dam category tree in frontend, so users can select files by combining dam categories. Files are delivered by a pushfile, so that the fileadmin directory can be secured by a .htaccess file. A second plugin shows selected (by an author) dam files. Please read the manual for more informations.',
	'category' => 'plugin',
	'shy' => 0,
	'version' => '0.4.0.0',
	'dependencies' => 'static_info_tables,dam,dam_catedit,dam_index,fileupload',
	'conflicts' => '',
	'priority' => '',
	'loadOrder' => '',
	'module' => '',
	'state' => 'beta',
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => '',
	'clearcacheonload' => 0,
	'lockType' => '',
	'author' => 'Team DAM Frontend',
	'author_email' => 'typo3@in2form.com',
	'author_company' => 'in2form.com',
	'CGLcompliance' => '',
	'CGLcompliance_note' => '',
	'constraints' => array(
		'depends' => array(
			'php' => '5.2.0-0.0.0',
			'typo3' => '4.2.6-0.0.0',
			'static_info_tables' => '2.1.0-0.0.0',
			'dam' => '1.1.0-0.0.0',
			'dam_catedit' => '1.1.0-0.0.0',
			'dam_index' => '1.1.0-0.0.0',
			'fileupload' => '1.1.0-0.0.0',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'_md5_values_when_last_written' => 'a:51:{s:9:"ChangeLog";s:4:"d7cb";s:10:"README.txt";s:4:"e34b";s:12:"ext_icon.gif";s:4:"69ef";s:17:"ext_localconf.php";s:4:"89fc";s:15:"ext_php_api.dat";s:4:"9bb6";s:14:"ext_tables.php";s:4:"39df";s:14:"ext_tables.sql";s:4:"9bd9";s:15:"flexform_ds.xml";s:4:"5504";s:16:"locallang_db.xml";s:4:"7411";s:17:"locallang_tca.xml";s:4:"56c8";s:12:"pushfile.php";s:4:"ce14";s:7:"tca.php";s:4:"422e";s:19:"tx_dam_flexFunc.php";s:4:"2181";s:43:"DAL/class.tx_damfrontend_DAL_categories.php";s:4:"aabd";s:42:"DAL/class.tx_damfrontend_DAL_documents.php";s:4:"dd41";s:44:"DAL/class.tx_damfrontend_baseSessionData.php";s:4:"9591";s:36:"DAL/class.tx_damfrontend_catList.php";s:4:"6023";s:40:"DAL/class.tx_damfrontend_filterState.php";s:4:"c7c7";s:38:"DAL/class.tx_damfrontend_listState.php";s:4:"910b";s:14:"doc/manual.sxw";s:4:"320b";s:19:"doc/wizard_form.dat";s:4:"87c9";s:20:"doc/wizard_form.html";s:4:"26f6";s:45:"frontend/class.tx_damfrontend_catTreeView.php";s:4:"62c0";s:52:"frontend/class.tx_damfrontend_categorisationTree.php";s:4:"20b8";s:43:"frontend/class.tx_damfrontend_rendering.php";s:4:"a687";s:32:"pi1/class.tx_damfrontend_pi1.php";s:4:"5d00";s:17:"pi1/locallang.xml";s:4:"9745";s:13:"pi1/style.css";s:4:"842c";s:17:"pi1/template.html";s:4:"a19d";s:24:"pi1/static/editorcfg.txt";s:4:"c15d";s:32:"pi2/class.tx_damfrontend_pi2.php";s:4:"6103";s:17:"pi2/locallang.xml";s:4:"c5e0";s:24:"pi2/static/editorcfg.txt";s:4:"375a";s:20:"pi2/static/setup.txt";s:4:"db49";s:11:"res/ddl.css";s:4:"1f06";s:38:"res/sample_additionally_langlabels.xml";s:4:"7e3c";s:13:"res/style.css";s:4:"0eb9";s:18:"res/tmpl_list.html";s:4:"38fa";s:31:"res/ico/application_default.png";s:4:"b307";s:27:"res/ico/application_pdf.png";s:4:"8b7e";s:19:"res/ico/default.png";s:4:"ce6f";s:19:"res/ico/edit_fe.gif";s:4:"336a";s:19:"res/ico/garbage.gif";s:4:"90c6";s:18:"res/ico/hidden.png";s:4:"5894";s:20:"res/ico/icon_ok2.gif";s:4:"4852";s:25:"res/ico/image_default.png";s:4:"c549";s:24:"res/ico/text_default.png";s:4:"cf7d";s:21:"res/ico/turn_left.gif";s:4:"73a5";s:20:"static/constants.txt";s:4:"5ab0";s:16:"static/setup.txt";s:4:"4530";s:28:"tests/rendering_testcase.php";s:4:"e08d";}',
	'suggests' => array(
	),
);

?>