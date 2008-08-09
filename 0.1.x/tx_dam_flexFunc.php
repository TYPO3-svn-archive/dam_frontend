<?php

require_once(t3lib_extmgm::extPath('dam').'lib/class.tx_dam_tcefunc.php');



class tx_dam_flexFunc extends tx_dam_tceFunc{


	function  getSingleField_selectTree($PA, &$fObj) {



		/********************************************
		*
		* you cannot pass values via flexform datastructure (I was to lazy to find a way)
		*
		********************************************/

		$PA['fieldConf']['config']['treeViewClass'] = 'EXT:dam/components/class.tx_dam_selectionCategory.php:&tx_dam_selectionCategory';
		$PA['fieldConf']['config']['userFunc'] = 'EXT:dam/lib/class.tx_dam_tcefunc.php:&tx_dam_tceFunc->getSingleField_selectTree';

//		debug($PA);
//		debug($fObj);
		return parent::getSingleField_selectTree($PA, $fObj);

	}

}



?>