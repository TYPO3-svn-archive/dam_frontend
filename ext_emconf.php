<?php

########################################################################
# Extension Manager/Repository config file for ext "dam_frontend".
#
# Auto generated 30-09-2011 22:03
#
# Manual updates:
# Only the data in the array - everything else is removed by next
# writing. "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'DAM Frontend',
	'description' => 'Download, upload and edit DAM files via frontend. Shows the dam category tree in frontend, so users can select files by combining dam categories. Files are delivered by a pushfile, so that the fileadmin directory can be secured by a .htaccess file.',
	'category' => 'plugin',
	'shy' => 0,
	'version' => '0.9.8',
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
	'author_email' => 'typo3@in2code.de',
	'author_company' => 'in2code',
	'CGLcompliance' => '',
	'CGLcompliance_note' => '',
	'constraints' => array(
		'depends' => array(
			'php' => '5.2.0-0.0.0',
			'typo3' => '4.4.0-4.6.99',
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
	'_md5_values_when_last_written' => 'a:116:{s:9:"ChangeLog";s:4:"eb7d";s:10:"README.txt";s:4:"3de5";s:11:"archive.php";s:4:"39e2";s:20:"class.ext_update.php";s:4:"492f";s:21:"ext_conf_template.txt";s:4:"76df";s:12:"ext_icon.gif";s:4:"69ef";s:17:"ext_localconf.php";s:4:"6300";s:15:"ext_php_api.dat";s:4:"e94e";s:14:"ext_tables.php";s:4:"871b";s:14:"ext_tables.sql";s:4:"f273";s:15:"flexform_ds.xml";s:4:"883e";s:16:"locallang_db.xml";s:4:"2f06";s:17:"locallang_tca.xml";s:4:"18ff";s:12:"pushfile.php";s:4:"4acc";s:10:"stream.php";s:4:"3d4e";s:7:"tca.php";s:4:"c529";s:19:"tx_dam_flexFunc.php";s:4:"2181";s:43:"DAL/class.tx_damfrontend_DAL_categories.php";s:4:"7fd4";s:42:"DAL/class.tx_damfrontend_DAL_documents.php";s:4:"049e";s:44:"DAL/class.tx_damfrontend_baseSessionData.php";s:4:"d283";s:36:"DAL/class.tx_damfrontend_catList.php";s:4:"c776";s:40:"DAL/class.tx_damfrontend_filterState.php";s:4:"6389";s:38:"DAL/class.tx_damfrontend_listState.php";s:4:"910b";s:14:"doc/manual.sxw";s:4:"979a";s:23:"doc/manual_0.4.1_ru.sxw";s:4:"a75e";s:19:"doc/wizard_form.dat";s:4:"87c9";s:20:"doc/wizard_form.html";s:4:"26f6";s:45:"frontend/class.tx_damfrontend_catTreeView.php";s:4:"d6e5";s:53:"frontend/class.tx_damfrontend_catTreeViewAdvanced.php";s:4:"6775";s:52:"frontend/class.tx_damfrontend_categorisationTree.php";s:4:"ace0";s:43:"frontend/class.tx_damfrontend_rendering.php";s:4:"41c2";s:14:"pi1/ce_wiz.gif";s:4:"1a4b";s:32:"pi1/class.tx_damfrontend_pi1.php";s:4:"cf10";s:40:"pi1/class.tx_damfrontend_pi1_wizicon.php";s:4:"5f2e";s:17:"pi1/locallang.xml";s:4:"5aed";s:13:"pi1/style.css";s:4:"842c";s:17:"pi1/template.html";s:4:"2462";s:22:"pi1/template_plain.txt";s:4:"db70";s:22:"pi1/template_tree.html";s:4:"2346";s:24:"pi1/static/constants.txt";s:4:"293b";s:24:"pi1/static/editorcfg.txt";s:4:"c15d";s:20:"pi1/static/setup.txt";s:4:"1668";s:14:"pi2/ce_wiz.gif";s:4:"96ad";s:32:"pi2/class.tx_damfrontend_pi2.php";s:4:"fb23";s:40:"pi2/class.tx_damfrontend_pi2_wizicon.php";s:4:"216a";s:17:"pi2/locallang.xml";s:4:"47ec";s:24:"pi2/static/editorcfg.txt";s:4:"375a";s:20:"pi2/static/setup.txt";s:4:"4fc4";s:39:"pi3/class.tx_damfrontend_basketCase.php";s:4:"41c5";s:48:"pi3/class.tx_damfrontend_basketCaseRendering.php";s:4:"882a";s:32:"pi3/class.tx_damfrontend_pi3.php";s:4:"8c56";s:16:"pi3/flexform.xml";s:4:"f811";s:17:"pi3/locallang.xml";s:4:"1db1";s:13:"pi3/style.css";s:4:"d41d";s:17:"pi3/template.html";s:4:"2c60";s:22:"pi3/gfx/basket_add.png";s:4:"657c";s:25:"pi3/gfx/basket_remove.png";s:4:"1127";s:24:"pi3/static/constants.txt";s:4:"f9bc";s:24:"pi3/static/editorcfg.txt";s:4:"81e9";s:20:"pi3/static/setup.txt";s:4:"2a46";s:25:"res/Update_ReadAccess.jpg";s:4:"79f0";s:11:"res/ddl.css";s:4:"1f06";s:23:"res/default_css_pi1.css";s:4:"4f95";s:38:"res/sample_additionally_langlabels.xml";s:4:"7e3c";s:13:"res/style.css";s:4:"0eb9";s:18:"res/tmpl_list.html";s:4:"356c";s:31:"res/ico/application_default.png";s:4:"b307";s:27:"res/ico/application_pdf.png";s:4:"8b7e";s:27:"res/ico/clip_pasteafter.gif";s:4:"c759";s:19:"res/ico/default.png";s:4:"ce6f";s:15:"res/ico/doc.gif";s:4:"0975";s:16:"res/ico/down.gif";s:4:"b8a8";s:19:"res/ico/edit_fe.gif";s:4:"336a";s:23:"res/ico/edit_rtewiz.gif";s:4:"d8b4";s:19:"res/ico/garbage.gif";s:4:"90c6";s:18:"res/ico/hidden.png";s:4:"5894";s:20:"res/ico/icon_ok2.gif";s:4:"4852";s:25:"res/ico/image_default.png";s:4:"c549";s:24:"res/ico/text_default.png";s:4:"cf7d";s:21:"res/ico/turn_left.gif";s:4:"73a5";s:14:"res/ico/up.gif";s:4:"822e";s:16:"res/ico/zoom.gif";s:4:"d07c";s:28:"res/ico/tree/arrowbullet.gif";s:4:"dd1b";s:22:"res/ico/tree/blank.gif";s:4:"9f3a";s:20:"res/ico/tree/cat.gif";s:4:"0e9a";s:26:"res/ico/tree/catequals.gif";s:4:"fcb9";s:26:"res/ico/tree/catfolder.gif";s:4:"a16b";s:25:"res/ico/tree/catminus.gif";s:4:"5f98";s:24:"res/ico/tree/catplus.gif";s:4:"80dd";s:23:"res/ico/tree/folder.png";s:4:"6951";s:28:"res/ico/tree/folder_open.png";s:4:"0a3f";s:25:"res/ico/tree/halfline.gif";s:4:"6a75";s:21:"res/ico/tree/join.gif";s:4:"86ea";s:27:"res/ico/tree/joinbottom.gif";s:4:"3822";s:24:"res/ico/tree/jointop.gif";s:4:"211c";s:21:"res/ico/tree/line.gif";s:4:"d3d7";s:22:"res/ico/tree/minus.gif";s:4:"dd7a";s:28:"res/ico/tree/minusbottom.gif";s:4:"a1b6";s:28:"res/ico/tree/minusbullet.gif";s:4:"8336";s:26:"res/ico/tree/minusonly.gif";s:4:"1c3b";s:25:"res/ico/tree/minustop.gif";s:4:"2218";s:21:"res/ico/tree/plus.gif";s:4:"86da";s:27:"res/ico/tree/plusbottom.gif";s:4:"6ac4";s:27:"res/ico/tree/plusbullet.gif";s:4:"d11c";s:25:"res/ico/tree/plusonly.gif";s:4:"8cdc";s:24:"res/ico/tree/plustop.gif";s:4:"e7ae";s:25:"res/ico/tree/quadline.gif";s:4:"22bb";s:24:"res/ico/tree/stopper.gif";s:4:"1424";s:23:"res/images/checkbox.gif";s:4:"0e57";s:29:"res/images/checkbox_green.gif";s:4:"813e";s:28:"res/images/checkbox_grey.gif";s:4:"93c2";s:23:"res/images/download.gif";s:4:"01bf";s:24:"res/images/no_access.gif";s:4:"57c5";s:25:"res/images/tree_close.gif";s:4:"62bf";s:24:"res/images/tree_open.gif";s:4:"1c81";s:16:"res/js/xptree.js";s:4:"0b46";}',
	'suggests' => array(
	),
);

?>