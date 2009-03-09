<?php

########################################################################
# Extension Manager/Repository config file for ext: "dam_frontend"
#
# Auto generated 18-01-2009 15:30
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
	'version' => '0.3.1-rc',
	'dependencies' => 'static_info_tables,dam,dam_catedit,fileupload',
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
			'dam_index' =>'1.1.0-0.0.0',
			'fileupload' => '1.1.0-0.0.0',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'_md5_values_when_last_written' => 'a:52:{s:9:"ChangeLog";s:4:"d7cb";s:10:"README.txt";s:4:"bd68";s:12:"ext_icon.gif";s:4:"69ef";s:17:"ext_localconf.php";s:4:"89fc";s:15:"ext_php_api.dat";s:4:"9bb6";s:14:"ext_tables.php";s:4:"eca0";s:14:"ext_tables.sql";s:4:"5b68";s:15:"flexform_ds.xml";s:4:"1656";s:24:"flexform_ds_disabled.xml";s:4:"4608";s:16:"locallang_db.xml";s:4:"5682";s:17:"locallang_tca.xml";s:4:"23e3";s:12:"pushfile.php";s:4:"ebba";s:7:"tca.php";s:4:"422e";s:19:"tx_dam_flexFunc.php";s:4:"70c1";s:43:"DAL/class.tx_damfrontend_DAL_categories.php";s:4:"0ae8";s:42:"DAL/class.tx_damfrontend_DAL_documents.php";s:4:"45dd";s:44:"DAL/class.tx_damfrontend_baseSessionData.php";s:4:"ddad";s:36:"DAL/class.tx_damfrontend_catList.php";s:4:"5295";s:36:"DAL/class.tx_damfrontend_docList.php";s:4:"f337";s:40:"DAL/class.tx_damfrontend_filterState.php";s:4:"c7c7";s:38:"DAL/class.tx_damfrontend_listState.php";s:4:"910b";s:14:"doc/manual.sxw";s:4:"5ac4";s:19:"doc/wizard_form.dat";s:4:"87c9";s:20:"doc/wizard_form.html";s:4:"26f6";s:45:"frontend/class.tx_damfrontend_catTreeView.php";s:4:"b88f";s:52:"frontend/class.tx_damfrontend_categorisationTree.php";s:4:"c620";s:43:"frontend/class.tx_damfrontend_rendering.php";s:4:"9667";s:32:"pi1/class.tx_damfrontend_pi1.php";s:4:"50cf";s:17:"pi1/locallang.xml";s:4:"605f";s:13:"pi1/style.css";s:4:"842c";s:17:"pi1/template.html";s:4:"c65d";s:24:"pi1/static/editorcfg.txt";s:4:"c15d";s:32:"pi2/class.tx_damfrontend_pi2.php";s:4:"2441";s:17:"pi2/locallang.xml";s:4:"c5e0";s:24:"pi2/static/editorcfg.txt";s:4:"375a";s:20:"pi2/static/setup.txt";s:4:"d534";s:11:"res/ddl.css";s:4:"1f06";s:13:"res/style.css";s:4:"0eb9";s:18:"res/tmpl_list.html";s:4:"e599";s:31:"res/ico/application_default.png";s:4:"b307";s:27:"res/ico/application_pdf.png";s:4:"8b7e";s:19:"res/ico/default.png";s:4:"ce6f";s:19:"res/ico/edit_fe.gif";s:4:"336a";s:19:"res/ico/garbage.gif";s:4:"90c6";s:18:"res/ico/hidden.png";s:4:"5894";s:20:"res/ico/icon_ok2.gif";s:4:"4852";s:25:"res/ico/image_default.png";s:4:"c549";s:24:"res/ico/text_default.png";s:4:"cf7d";s:21:"res/ico/turn_left.gif";s:4:"73a5";s:20:"static/constants.txt";s:4:"04a8";s:16:"static/setup.txt";s:4:"9d74";s:28:"tests/rendering_testcase.php";s:4:"e08d";}',
	'suggests' => array(
	),
);

?>