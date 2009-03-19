<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');



/**************************************
 * 
 * Adding the category browser to the page - TCEForm
 * 
 **************************************/
$tempColumns = Array (
	"tx_damtree_dam_cats" => Array (		
		"exclude" => 1,		
		"label" => "LLL:EXT:dam_frontend/locallang_db.xml:pages.tx_damtree_dam_cats",		
		"config" => Array (
			'type' => 'select',
			'form_type' => 'user',
			'userFunc' => 'EXT:dam/lib/class.tx_dam_tcefunc.php:&tx_dam_tceFunc->getSingleField_selectTree',
			'treeViewBrowseable' => true,
			'treeViewClass' => 'EXT:dam/components/class.tx_dam_selectionCategory.php:&tx_dam_selectionCategory', // don't work here: $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['dam']['selectionClasses']['txdamCat']
			'foreign_table' => 'tx_dam_cat',
			'size' => 4,
			'autoSizeMax' => 30,
			'minitems' => 0,
			'maxitems' => 2, // workaround - should be 1
			'default' => '',
			'MM' => 'pages_tx_damtree_dam_cats_mm'
		)
	),
);


t3lib_div::loadTCA("pages");
t3lib_extMgm::addTCAcolumns("pages",$tempColumns,1);
t3lib_extMgm::addToAllTCAtypes("pages","tx_damtree_dam_cats;;;;1-1-1");

// Adding a static file to the script
t3lib_extMgm::addStaticFile($_EXTKEY,"static/","DAM Frontend Static Template");








/***************************************
 * 
 * Adding relations to frontend groups to DAM - Categories TCEForm
 * 
 ***************************************/
$tempColumns = Array (
	"tx_damtree_fe_groups_readaccess" => Array (		
		"exclude" => 1,		
		"label" => "LLL:EXT:dam_frontend/locallang_db.xml:tx_dam_cat.readaccess",
		"config" => Array (
			"items" => Array(
				Array("alle Gruppen freigeben", -1),
				Array("keine Gruppe freigeben", -2),
				Array("-----einzelne Gruppen------", '--div--')
			), 	
			"type" => "select",
			"foreign_table" =>"fe_groups",	
			"internal_type" => "db",	
			"size" => 5,	
			"minitems" => 0,
			"maxitems" => 50,	
			"MM" => "tx_dam_cat_readaccess_mm",
		)
	),
	"tx_damtree_fe_groups_downloadaccess" => Array (		
		"exclude" => 1,		
		"label" => "LLL:EXT:dam_frontend/locallang_db.xml:tx_dam_cat.downloadaccess",		
		"config" => Array (
			"items" => Array(
				Array("alle Gruppen freigeben", -1),
				Array("keine Gruppe freigeben", -2),
				Array("-----einzelne Gruppen------", '--div--')
			), 	
			"type" => "select",	
			"internal_type" => "db",
			"foreign_table" => "fe_groups",	
			"size" => 5,	
			"minitems" => 0,
			"maxitems" => 50,	
			"MM" => "tx_dam_cat_downloadaccess_mm",
		)
	),
);
// adding the configuration to the dam - categorie table
t3lib_div::loadTCA("tx_dam_cat");
t3lib_extMgm::addTCAcolumns("tx_dam_cat",$tempColumns,1);
t3lib_extMgm::addToAllTCAtypes("tx_dam_cat","tx_damtree_fe_groups_readaccess;;;;1-1-1, tx_damtree_fe_groups_downloadaccess");




/********************************* 
 * 
 * 	Adding the filter State table to the $TCA
 * 
 **********************************/
$TCA["tx_damfrontend_filterStates"] = Array (
	"ctrl" => Array (
		'title' => 'LLL:EXT:damfrontend/locallang_db.xml:tx_damfrontend_filterStates',		
		'label' => 'uid',	
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		"default_sortby" => "ORDER BY crdate",	
		"delete" => "deleted",	
		"enablecolumns" => Array (		
			"disabled" => "hidden",
		),
		"dynamicConfigFile" => t3lib_extMgm::extPath($_EXTKEY)."tca.php",
		"iconfile" => t3lib_extMgm::extRelPath($_EXTKEY)."icon_tx_damfrontend_filterStates.gif",
	),
	"feInterface" => Array (
		"fe_admin_fieldList" => "hidden, title, description, from, to, searchword, filetypes, categories",
	)
);


/**********************************************
*
* 	inclusion of classes for displaying of category tree in the backend
*
***********************************************/
include_once(t3lib_extMgm::extPath('dam_frontend').'tx_dam_flexFunc.php');




$tempColumns = Array (
	"tx_damdownloadlist_records" => Array (		
		"exclude" => 0,		
		"label" => "LLL:EXT:dam_frontend/locallang_db.php:tt_content.tx_damdownloadlist_records",		
		"config" => Array (
			"type" => "group",	
			"internal_type" => "db",	
			"allowed" => "tx_dam",	
			"size" => 10,	
			"minitems" => 1,
			"maxitems" => 25,
			"show_thumbs" => 1,
		)
	),
);


t3lib_div::loadTCA("tt_content");
t3lib_extMgm::addTCAcolumns("tt_content",$tempColumns,1);
$TCA["tt_content"]["types"]["list"]["subtypes_excludelist"][$_EXTKEY."_pi2"]="layout,select_key,pages";
$TCA["tt_content"]["types"]["list"]["subtypes_addlist"][$_EXTKEY."_pi2"]="tx_damdownloadlist_records;;;;1-1-1";


/**********************************************
 * 
 * 	Adding frontend - plugin to the system
 *  
 ***********************************************/
t3lib_div::loadTCA('tt_content');
$TCA['tt_content']['types']['list']['subtypes_addlist'][$_EXTKEY.'_pi1'] = 'pi_flexform';
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi1']='layout,select_key, pages, recursive';
t3lib_extMgm::addPlugin(array('LLL:EXT:dam_frontend/locallang_db.xml:tt_content.list_type_pi1', $_EXTKEY.'_pi1'),'list_type');
t3lib_extMgm::addPiFlexFormValue($_EXTKEY.'_pi1', 'FILE:EXT:'.$_EXTKEY.'/flexform_ds.xml');


t3lib_div::loadTCA('tt_content');
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi2']='layout,select_key';
t3lib_extMgm::addPlugin(array('LLL:EXT:dam_frontend/locallang_db.xml:tt_content.list_type_pi2', $_EXTKEY.'_pi2'),'list_type');
t3lib_extMgm::addStaticFile($_EXTKEY,"pi2/static/","DAM Frontend Filelist");
?>