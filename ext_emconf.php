<?php

########################################################################
# Extension Manager/Repository config file for ext: "dam_frontend"
#
# Auto generated 01-06-2009 21:37
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
	'version' => '0.4.0-dev20',
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
	'_md5_values_when_last_written' => 'a:85:{s:9:"ChangeLog";s:4:"d00b";s:10:"README.txt";s:4:"e34b";s:12:"ext_icon.gif";s:4:"69ef";s:17:"ext_localconf.php";s:4:"3a70";s:15:"ext_php_api.dat";s:4:"9bb6";s:14:"ext_tables.php";s:4:"5809";s:14:"ext_tables.sql";s:4:"5842";s:15:"flexform_ds.xml";s:4:"0ebe";s:16:"locallang_db.xml";s:4:"7411";s:17:"locallang_tca.xml";s:4:"36e3";s:12:"pushfile.php";s:4:"29b5";s:7:"tca.php";s:4:"1fb0";s:19:"tx_dam_flexFunc.php";s:4:"2181";s:43:"DAL/class.tx_damfrontend_DAL_categories.php";s:4:"e196";s:42:"DAL/class.tx_damfrontend_DAL_documents.php";s:4:"129c";s:44:"DAL/class.tx_damfrontend_baseSessionData.php";s:4:"9591";s:36:"DAL/class.tx_damfrontend_catList.php";s:4:"4de5";s:40:"DAL/class.tx_damfrontend_filterState.php";s:4:"c7c7";s:38:"DAL/class.tx_damfrontend_listState.php";s:4:"910b";s:14:"doc/manual.sxw";s:4:"dda0";s:19:"doc/wizard_form.dat";s:4:"87c9";s:20:"doc/wizard_form.html";s:4:"26f6";s:45:"frontend/class.tx_damfrontend_catTreeView.php";s:4:"0e6e";s:53:"frontend/class.tx_damfrontend_catTreeViewAdvanced.php";s:4:"92c7";s:52:"frontend/class.tx_damfrontend_categorisationTree.php";s:4:"c82f";s:43:"frontend/class.tx_damfrontend_rendering.php";s:4:"04b2";s:14:"pi1/ce_wiz.gif";s:4:"1a4b";s:32:"pi1/class.tx_damfrontend_pi1.php";s:4:"2480";s:40:"pi1/class.tx_damfrontend_pi1_wizicon.php";s:4:"73af";s:17:"pi1/locallang.xml";s:4:"86c4";s:13:"pi1/style.css";s:4:"842c";s:17:"pi1/template.html";s:4:"4d94";s:22:"pi1/template_tree.html";s:4:"2d8e";s:24:"pi1/static/constants.txt";s:4:"1e37";s:24:"pi1/static/editorcfg.txt";s:4:"c15d";s:20:"pi1/static/setup.txt";s:4:"1be0";s:14:"pi2/ce_wiz.gif";s:4:"96ad";s:32:"pi2/class.tx_damfrontend_pi2.php";s:4:"868b";s:40:"pi2/class.tx_damfrontend_pi2_wizicon.php";s:4:"3dee";s:17:"pi2/locallang.xml";s:4:"ff5b";s:24:"pi2/static/editorcfg.txt";s:4:"375a";s:20:"pi2/static/setup.txt";s:4:"36c7";s:11:"res/ddl.css";s:4:"1f06";s:38:"res/sample_additionally_langlabels.xml";s:4:"7e3c";s:13:"res/style.css";s:4:"0eb9";s:18:"res/tmpl_list.html";s:4:"9f3d";s:31:"res/ico/application_default.png";s:4:"b307";s:27:"res/ico/application_pdf.png";s:4:"8b7e";s:27:"res/ico/clip_pasteafter.gif";s:4:"c759";s:19:"res/ico/default.png";s:4:"ce6f";s:16:"res/ico/down.gif";s:4:"b8a8";s:19:"res/ico/edit_fe.gif";s:4:"336a";s:23:"res/ico/edit_rtewiz.gif";s:4:"d8b4";s:19:"res/ico/garbage.gif";s:4:"90c6";s:18:"res/ico/hidden.png";s:4:"5894";s:20:"res/ico/icon_ok2.gif";s:4:"4852";s:25:"res/ico/image_default.png";s:4:"c549";s:24:"res/ico/text_default.png";s:4:"cf7d";s:21:"res/ico/turn_left.gif";s:4:"73a5";s:14:"res/ico/up.gif";s:4:"822e";s:16:"res/ico/zoom.gif";s:4:"d07c";s:28:"res/ico/tree/arrowbullet.gif";s:4:"dd1b";s:22:"res/ico/tree/blank.gif";s:4:"9f3a";s:20:"res/ico/tree/cat.gif";s:4:"0e9a";s:26:"res/ico/tree/catequals.gif";s:4:"fcb9";s:26:"res/ico/tree/catfolder.gif";s:4:"a16b";s:25:"res/ico/tree/catminus.gif";s:4:"5f98";s:24:"res/ico/tree/catplus.gif";s:4:"80dd";s:25:"res/ico/tree/halfline.gif";s:4:"6a75";s:21:"res/ico/tree/join.gif";s:4:"86ea";s:27:"res/ico/tree/joinbottom.gif";s:4:"3822";s:24:"res/ico/tree/jointop.gif";s:4:"211c";s:21:"res/ico/tree/line.gif";s:4:"d3d7";s:22:"res/ico/tree/minus.gif";s:4:"dd7a";s:28:"res/ico/tree/minusbottom.gif";s:4:"a1b6";s:28:"res/ico/tree/minusbullet.gif";s:4:"8336";s:26:"res/ico/tree/minusonly.gif";s:4:"1c3b";s:25:"res/ico/tree/minustop.gif";s:4:"2218";s:21:"res/ico/tree/plus.gif";s:4:"86da";s:27:"res/ico/tree/plusbottom.gif";s:4:"6ac4";s:27:"res/ico/tree/plusbullet.gif";s:4:"d11c";s:25:"res/ico/tree/plusonly.gif";s:4:"8cdc";s:24:"res/ico/tree/plustop.gif";s:4:"e7ae";s:25:"res/ico/tree/quadline.gif";s:4:"22bb";s:24:"res/ico/tree/stopper.gif";s:4:"1424";}',
	'suggests' => array(
	),
);

?>