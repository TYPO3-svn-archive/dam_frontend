<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

$TCA["tx_damfrontend_filterStates"] = Array (
	"ctrl" => $TCA["tx_damfrontend_filterStates"]["ctrl"],
	"interface" => Array (
		"showRecordFieldList" => "hidden,title,description,from,to,searchword,filetypes,categories"
	),
	"feInterface" => $TCA["tx_damfrontend_filterStates"]["feInterface"],
	"columns" => Array (
		"hidden" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:lang/locallang_general.xml:LGL.hidden",
			"config" => Array (
				"type" => "check",
				"default" => "0"
			)
		),
		"title" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:dam_frontend/locallang_db.xml:tx_damfrontend_filterStates.title",
			"config" => Array (
				"type" => "input",
				"size" => "30",
				"eval" => "required",
			)
		),
		"description" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:dam_frontend/locallang_db.xml:tx_damfrontend_filterStates.description",
			"config" => Array (
				"type" => "text",
				"cols" => "30",
				"rows" => "5",
			)
		),
		"filter_from" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:dam_frontend/locallang_db.xml:tx_damfrontend_filterStates.from",
			"config" => Array (
				"type" => "input",
				"size" => "8",
				"max" => "20",
				"eval" => "date",
				"checkbox" => "0",
				"default" => "0"
			)
		),
		"filter_to" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:dam_frontend/locallang_db.xml:tx_damfrontend_filterStates.to",
			"config" => Array (
				"type" => "input",
				"size" => "8",
				"max" => "20",
				"eval" => "date",
				"checkbox" => "0",
				"default" => "0"
			)
		),
		"searchword" => Array (
			"exclude" => 1,
			"label" => "LLL:EXT:dam_frontend/locallang_db.xml:tx_damfrontend_filterStates.searchword",
			"config" => Array (
				"type" => "input",
				"size" => "30",
			)
		),
	),
	"types" => Array (
		"0" => Array("showitem" => "hidden;;1;;1-1-1, title;;;;2-2-2, description;;;;3-3-3, from, to, searchword, filetypes, categories")
	),
	"palettes" => Array (
		"1" => Array("showitem" => "")
	)
);
$TCA['tx_dam']['columns']['category']['config']['autoSizeMax'] = 40;

$TCA['tx_damfrontend_usage'] = array (
	'ctrl' => $TCA['tx_damfrontend_usage']['ctrl'],
	'interface' => array (
		'showRecordFieldList' => 'sys_language_uid,l10n_parent,l10n_diffsource,hidden,starttime,endtime,fe_group,recuid,description,dateusage,feuser'
	),
	'feInterface' => $TCA['tx_damfrontend_usage']['feInterface'],
	'columns' => array (
		'sys_language_uid' => array (		
			'exclude' => 1,
			'label'  => 'LLL:EXT:lang/locallang_general.xml:LGL.language',
			'config' => array (
				'type'                => 'select',
				'foreign_table'       => 'sys_language',
				'foreign_table_where' => 'ORDER BY sys_language.title',
				'items' => array(
					array('LLL:EXT:lang/locallang_general.xml:LGL.allLanguages', -1),
					array('LLL:EXT:lang/locallang_general.xml:LGL.default_value', 0)
				)
			)
		),
		'l10n_parent' => array (		
			'displayCond' => 'FIELD:sys_language_uid:>:0',
			'exclude'     => 1,
			'label'       => 'LLL:EXT:lang/locallang_general.xml:LGL.l18n_parent',
			'config'      => array (
				'type'  => 'select',
				'items' => array (
					array('', 0),
				),
				'foreign_table'       => 'tx_damfrontend_usage',
				'foreign_table_where' => 'AND tx_damfrontend_usage.pid=###CURRENT_PID### AND tx_damfrontend_usage.sys_language_uid IN (-1,0)',
			)
		),
		'l10n_diffsource' => array (		
			'config' => array (
				'type' => 'passthrough'
			)
		),
		'hidden' => array (		
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
			'config'  => array (
				'type'    => 'check',
				'default' => '0'
			)
		),
		'starttime' => array (		
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.starttime',
			'config'  => array (
				'type'     => 'input',
				'size'     => '8',
				'max'      => '20',
				'eval'     => 'date',
				'default'  => '0',
				'checkbox' => '0'
			)
		),
		'endtime' => array (		
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.endtime',
			'config'  => array (
				'type'     => 'input',
				'size'     => '8',
				'max'      => '20',
				'eval'     => 'date',
				'checkbox' => '0',
				'default'  => '0',
				'range'    => array (
					'upper' => mktime(3, 14, 7, 1, 19, 2038),
					'lower' => mktime(0, 0, 0, date('m')-1, date('d'), date('Y'))
				)
			)
		),
		'fe_group' => array (		
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.fe_group',
			'config'  => array (
				'type'  => 'select',
				'items' => array (
					array('', 0),
					array('LLL:EXT:lang/locallang_general.xml:LGL.hide_at_login', -1),
					array('LLL:EXT:lang/locallang_general.xml:LGL.any_login', -2),
					array('LLL:EXT:lang/locallang_general.xml:LGL.usergroups', '--div--')
				),
				'foreign_table' => 'fe_groups'
			)
		),
		'recuid' => array (		
			'exclude' => 0,		
			'label' => 'LLL:EXT:dam_frontend_/locallang_db.xml:tx_damfrontend_usage.recuid',		
			'config' => array (
				'type'     => 'input',
				'size'     => '4',
				'max'      => '4',
				'eval'     => 'int',
				'checkbox' => '0',
				'range'    => array (
					'upper' => '1000',
					'lower' => '10'
				),
				'default' => 0
			)
		),
		'description' => array (		
			'exclude' => 0,		
			'label' => 'LLL:EXT:dam_frontend_/locallang_db.xml:tx_damfrontend_usage.description',		
			'config' => array (
				'type' => 'text',
				'cols' => '30',	
				'rows' => '5',
			)
		),
		'dateusage' => array (		
			'exclude' => 0,		
			'label' => 'LLL:EXT:dam_frontend_/locallang_db.xml:tx_damfrontend_usage.dateusage',		
			'config' => array (
				'type'     => 'input',
				'size'     => '8',
				'max'      => '20',
				'eval'     => 'date',
				'checkbox' => '0',
				'default'  => '0'
			)
		),
		'feuser' => array (		
			'exclude' => 0,		
			'label' => 'LLL:EXT:dam_frontend_/locallang_db.xml:tx_damfrontend_usage.feuser',		
			'config' => array (
				'type' => 'select',	
				'foreign_table' => 'fe_users',	
				'foreign_table_where' => 'ORDER BY fe_users.uid',	
				'size' => 1,	
				'minitems' => 0,
				'maxitems' => 1,
			)
		),
	),
	'types' => array (
		'0' => array('showitem' => 'sys_language_uid;;;;1-1-1, l10n_parent, l10n_diffsource, hidden;;1, recuid, description, dateusage, feuser')
	),
	'palettes' => array (
		'1' => array('showitem' => 'starttime, endtime, fe_group')
	)
);

?>