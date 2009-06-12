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
?>