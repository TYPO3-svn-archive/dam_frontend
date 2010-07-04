<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2006-2010 in2form.com (typo3@in2form.com)
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*  A copy is found in the textfile GPL.txt and important notices to the license
*  from the author is found in LICENSE.txt distributed with these scripts.
*
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/**
 *
 * class.tx_damfrontend_catTreeView.php
 *
 * Provides a DAM category tree, which is completly customizible (for design)
 *
 * @package typo3
 * @subpackage tx_dam_frontend
 * @author Stefan Busemann <typo3@in2form.com>
 *
 *
 * @todo clean up the class for code that isn't needed, build a standard template
 */
require_once(PATH_txdam.'components/class.tx_dam_selectionCategory.php');
require_once(t3lib_extMgm::extPath('dam_frontend').'/DAL/class.tx_damfrontend_DAL_categories.php');

/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 *   80: class tx_damfrontend_catTreeViewAdvanced extends tx_dam_selectionCategory
 *  102:     function tx_damfrontend_catTreeViewAdvanced()
 *  137:     function init($treeID = '', $plugin = null)
 *  157:     function expandNext($id)
 *  169:     function initializePositionSaving()
 *  193:     function expandTreeLevel($levelDeepth=0)
 *  210:     function savePosition()
 *  224:     function wrapTitle($title,$row,$bank=0)
 *  251:     function wrapCatSelection($wrapItem,$row, $sel_class)
 *  342:     function PM_ATagWrap($icon,$cmd,$bMark='treeroot')
 *  392:     function PM_wrap($row,$a,$c,$nextCount,$exp,$wrapItem)
 *  423:     function getControl($title,$row)
 *  457:     function printTree($treeArr='')
 *  585:     function getTitleStr($row, $titleLen = 30)
 *  599:     function getBrowsableTree()
 *
 *              SECTION: tree data buidling
 *  684:     function getTree($uid, $depth=999, $depthData='',$blankLineCode='',$subCSSclass='')
 *  774:     function getRootIcon($rec)
 *  786:     function getIcon($row)
 *  801:     function setFileRef($filePath)
 *  814:     function getDataInit($parentId,$subCSSclass='')
 *  844:     function get_childCats($catUID, $treeStructure)
 *  860:     function get_treeStructure ()
 *  878:     function get_treeStructureElements ($uid)
 *  896:     function get_selectionStatus($catID,$treeStructure)
 *
 * TOTAL FUNCTIONS: 24
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */
class tx_damfrontend_catTreeViewAdvanced extends tx_dam_selectionCategory {

 	var $user;   											// instead of storing the data in the backend user, this data is stored in fe user
 	var $sessionVar = 'tx_damdownloads_treeState';			// name of the key, where to store the treeState in the current Session
	var $selectedCats;										// Array of currently selected cats
	var $catLogic;											// array which holds all selected categories
	var $treeID;											// ID Number of the tree given from the flexform configuration
	var $plugin;											// Back-reference to the calling plugin
	var $cObj;												// cObj
	var $conf;												// configuration array
	var $renderer;											// object of tx_damfrontend_rendering for doing the output
	var $categorizationMode; 								// true if the tree should display the categorization mode
	var $rootIconIsSet = false;								// indicates, if a root icon must be added or not
	var $mediaFolder;										// ID of the Folder, which contains the dam records
	var $treeStructureArray;
	var $additionalTreeConf= array();						// holds additonal rendering configuration
	/**
	 * prepares the category tree
	 *
	 * some small changes from the original category Tree
	 *
	 * @return	void
	 */
 	function tx_damfrontend_catTreeViewAdvanced() {

		$this->treeID = 1;
		$this->title = 'categorytree';
 		$this->treeName = 'txdamCat';
		$this->domIdPrefix = $this->treeName;
		$this->table = 'tx_dam_cat';
		$this->parentField = $GLOBALS['TCA'][$this->table]['ctrl']['treeParentField'];
		$this->parentField = 'parent_id';
		$this->typeField = $GLOBALS['TCA'][$this->table]['ctrl']['type'];

		$this->renderer; // keeps the reference to the frontend renderer


		$this->fieldArray = array('uid','title');
		if($this->parentField) $this->fieldArray[] = $this->parentField;
		if($this->typeField) $this->fieldArray[] = $this->typeField;
		$this->defaultList = 'uid,pid,tstamp,sorting';

		$this->clause = ' AND deleted=0 AND sys';
		$this->orderByFields = 'sorting,title';

		$conf = tx_dam::config_getValue('setup.selections.'.$this->treeName);
		$this->TSconfig = $conf['properties'];
		$this->mediaFolder = tx_dam_db::getPid();
 	}

	/**
	 * inits the class and user
	 * a frontend user is used to store the treestate data
	 *
	 * @param	[type]		$treeID: ...
	 * @param	[type]		$plugin: ...
	 * @return	void
	 */
 	function init($treeID = '', $plugin = null) {
		if ($this->conf['categoryTreeAdvanced.']['useLanguageOverlay']==1) $langWhere = ' AND sys_language_uid = 0';
 		parent::init($langWhere);
 		$this->treeID = $treeID;
 		$this->user =& $GLOBALS['TSFE']->fe_user;
 		$this->backPath = 'typo3/';
		if (isset($plugin)) $this->plugin = $plugin;
		$this->cObj = $this->plugin->cObj;
		$this->conf = $this->plugin->conf;
		if ($this->categorizationMode==true) $this->catLogic= t3lib_div::makeInstance('tx_damfrontend_DAL_categories');
	}

	/**
	 * expands the category tree
	 *
	 * @param	[int]		$id: ...
	 * @return	[type]		...
	 * @todo check if this function is used?
	 */
 	function expandNext($id)
 	{
 		return ($this->stored[$this->bank][$id] || $this->expandAll)? 1 : 0;
 	}

	/**
	 * initialises the handling of the current treestate. Instead of storing
	 * the serialised data inside the backend user ->uc Array, the frontend user
	 * session data is used
	 *
	 * @return	void
	 */
 	function initializePositionSaving() {
 		$this->stored=unserialize($this->user->getKey('ses',$this->sessionVar));
		$PM = explode('_',t3lib_div::_GP('PM'));	// 0: mount key, 1: set/clear boolean, 2: item ID (cannot contain "_"), 3: treeName
		if (is_array($PM)) {
			if (count($PM)==4 && $PM[3]==$this->treeName)	{
				if (isset($this->MOUNTS[$PM[0]]))	{
					if ($PM[1])	{	// set
						$this->stored[$PM[0]][$PM[2]]=1;
						$this->savePosition();
					} else {	// clear
						unset($this->stored[$PM[0]][$PM[2]]);
						$this->savePosition();
					}
				}
			}
		}
 	}

	/**
	 * expand the tree for the first view
	 * @author Jonas Dübi, Tizian Schmidlin <jd@cabag.ch>, <st@cabag.ch>
	 * @param	int		$levelDeepth defines how deep the tree is expanded
	 * @return	void
	 */
	function expandTreeLevel($levelDepth=0) {
 		
 		// expand only if tree was not expanded yet and level > 0
		if ($this->user->getKey("ses",$this->treeID.'expandTreeLevel')<>1 && $levelDepth > 0) {
			$structure = $this->get_treeStructure();
			
			// remove "cat_" from "cat_22" in structure array
			foreach($structure as $catNr => $parentID) {
				$catNr = explode('_',$catNr);
				$structureNumeric[$catNr[1]] = $parentID;
			}
			foreach ($this->MOUNTS as $mount => $ID) {
				$this->stored[$mount][$ID]=1;

				if($levelDepth > 1) {
					// alls ids of categories up to the levelDepth
					$idsWithinlevelDepth = array();
					
					// fill up $idsWithinlevelDepth
					$this->getLevelsFromFlatTree($idsWithinlevelDepth, $structureNumeric, $ID, 0, ($levelDepth-2));
					
					foreach($idsWithinlevelDepth as $uid) {
						$this->stored[$mount][$uid] = 1;
					}
				}
			}
			
			$this->savePosition();
			$this->user->setKey("ses",$this->treeID.'expandTreeLevel', 1);
		}
 	}

 	/**
 	 * Recursive expandation function
 	 * @author Jonas Dübi, Tizian Schmidlin <jd@cabag.ch>, <st@cabag.ch>
 	 * 
 	 * @param &$idsWithinlevelDepth
 	 * @param &$structure
 	 * @param $currentParentId
 	 * @param $currentLevel
 	 * @param $upToLevel
 	 * @return [void]
 	 *
 	 */
 	function getLevelsFromFlatTree(&$idsWithinlevelDepth, &$structure, $currentParentId, $currentLevel, $upToLevel) {
 		$parentIds = array();
 		
 		foreach($structure as $uid => $parentId) {
 			if($parentId == $currentParentId) {
 				$idsWithinlevelDepth[] = $uid;
 				
 				if($currentLevel < $upToLevel) {
 					$this->getLevelsFromFlatTree($idsWithinlevelDepth, $structure, $uid, ($currentLevel + 1), $upToLevel);
				}
 			}
 		}
 	}
	/**
	 * saves treestate inside of the fe_user Session Data
	 *
	 * @return	[void]
	 */
 	function savePosition()
 	{
 		$this->user->setKey("ses",$this->sessionVar, serialize($this->stored));
 	}


	/**
	 * wraps a Title in a link
	 *
	 * @param	string		$title: ...
	 * @param	resultset		$row: ...
	 * @param	int		$bank: ...
	 * @return	string		html ...
	 */
	function wrapTitle($title,$row,$bank=0) {
		$id = (int)t3lib_div::_GET('id');

		$param_array = array (
			'tx_damfrontend_pi1' => '', // ok, the t3lib_div::linkThisScript cant work with arrays
			'tx_damfrontend_pi1[catPlus]' => $row['uid'],
			'tx_damfrontend_pi1[catEquals]' => null,
			'tx_damfrontend_pi1[catMinus]' => null,
			'tx_damfrontend_pi1[catPlus_Rec]' => null,
			'tx_damfrontend_pi1[catMinus_Rec]' => null,
			'tx_damfrontend_pi1[treeID]' => $this->treeID
		);
		if ($id > 0) { $param_array['tx_damfrontend_pi1[id]'] = $id; }
		$this->conf['categoryTreeAdvanced.']['categoryTitle.']['parameter'] = $GLOBALS['TSFE']->id;
		$this->conf['categoryTreeAdvanced.']['categoryTitle.']['additionalParams'].= t3lib_div::implodeArrayForUrl('',$param_array);
		return $this->cObj->typoLink($title, $this->conf['categoryTreeAdvanced.']['categoryTitle.']);
	}

	/**
	 * wraps a Title in a link
	 *
	 * @param	string		$title: ...
	 * @param	resultset		$row: ...
	 * @param	string		$command: "selectAll", "selectThis", "deselect","deselectAll"
	 * @param	string		$sel_class
	 * @return	string		html ...
	 */
	function wrapCatSelection($wrapItem,$row, $sel_class) {
		if ($row['uid']==0) $row['uid']=-1;
		$id = (int)t3lib_div::_GET('id');
		switch ($sel_class) {
			case 'tree_selectedCats':
				$command = 'deselect';
				break;
			case 'tree_unselectedCats':
				$command = 'selectThis';
				break;
			case 'tree_selectedAllCats':
				$command = 'deselectAll';
				break;
			case 'tree_selectedPartlyCats':
				$command ='selectAll';
				break;
			case 'tree_selectedNoCats':
				$command ='selectAll';
				break;
			case 'tree_no_access':
				$command ='no_access';
				break;
			default:
				#die('parameter error in wrapCatSelection');
				break;
		}
		switch ($command) {
			case 'selectAll':
				$param_array = array (
					'tx_damfrontend_pi1' => '', // ok, the t3lib_div::linkThisScript cant work with arrays
					'tx_damfrontend_pi1[catPlus]' => null,
					'tx_damfrontend_pi1[catEquals]' => null,
					'tx_damfrontend_pi1[catMinus]' => null,
					'tx_damfrontend_pi1[catPlus_Rec]' => $row['uid'],
					'tx_damfrontend_pi1[catMinus_Rec]' => null,
					'tx_damfrontend_pi1[treeID]' => $this->treeID
				);
				break;
			case 'selectThis':
				$param_array = array (
					'tx_damfrontend_pi1' => '', // ok, the t3lib_div::linkThisScript cant work with arrays
					'tx_damfrontend_pi1[catPlus]' => null,
					'tx_damfrontend_pi1[catEquals]' => null,
					'tx_damfrontend_pi1[catMinus]' => null,
					'tx_damfrontend_pi1[catPlus_Rec]' =>  $row['uid'],
					'tx_damfrontend_pi1[catMinus_Rec]' => null,
					'tx_damfrontend_pi1[treeID]' => $this->treeID
				);
				break;
			case 'deselect':
				$param_array = array (
					'tx_damfrontend_pi1' => '', // ok, the t3lib_div::linkThisScript cant work with arrays
					'tx_damfrontend_pi1[catPlus]' => null,
					'tx_damfrontend_pi1[catEquals]' => null,
					'tx_damfrontend_pi1[catMinus]' => $row['uid'],
					'tx_damfrontend_pi1[catPlus_Rec]' => null,
					'tx_damfrontend_pi1[catMinus_Rec]' => null,
					'tx_damfrontend_pi1[treeID]' => $this->treeID
				);
				break;
			case 'deselectAll':
				$param_array = array (
					'tx_damfrontend_pi1' => '', // ok, the t3lib_div::linkThisScript cant work with arrays
					'tx_damfrontend_pi1[catPlus]' => null,
					'tx_damfrontend_pi1[catEquals]' => null,
					'tx_damfrontend_pi1[catMinus]' => null,
					'tx_damfrontend_pi1[catPlus_Rec]' => null,
					'tx_damfrontend_pi1[catMinus_Rec]' => $row['uid'],
					'tx_damfrontend_pi1[treeID]' => $this->treeID
				);
				break;
		}
		if ($this->renderer->piVars['catEditUID']) {
			$param_array['tx_damfrontend_pi1[catEditUID]'] =$this->renderer->piVars['catEditUID'];
		}
		if ($id > 0) { $param_array['tx_damfrontend_pi1[id]'] = $id; }
		$this->conf['categoryTreeAdvanced.']['categorySelection.']['link.']['parameter'] = $GLOBALS['TSFE']->id;
		if (is_array($param_array))	$this->conf['categoryTreeAdvanced.']['categorySelection.']['link.']['additionalParams'].= t3lib_div::implodeArrayForUrl('',$param_array);
		if ($command =='no_access') {
			return $wrapItem;
		}
		else {
			return $this->cObj->typoLink($wrapItem, $this->conf['categoryTreeAdvanced.']['categorySelection.']['link.']);
		}
	}
	/**
	 * PM_ATagWrap
	 *
	 * renders the plus or minus sign
	 *
	 * @param	string		$icon: html (img Tag)
	 * @param	string		$cmd: ...
	 * @param	string		$bMark: ..
	 * @param	array		$row: current record for category.
	 * @return	string		...
	 */
	function PM_ATagWrap($icon,$cmd,$bMark='treeroot', $row)	{
		$linkConf = array();
		$linkConf['parameter.']['data'] = 'TSFE:id';
		if ($this->renderer->piVars['catEditUID']) {
			$additionalParams = '&tx_damfrontend_pi1[catEditUID]=' .$this->renderer->piVars['catEditUID'];
		}
		$linkConf['additionalParams'] = $additionalParams.'&tx_damfrontend_pi1[treeID]='.$this->treeID.'&PM='.htmlspecialchars($cmd);
		$linkConf['section'] = $bMark;
		if ($bMark) $linkConf['ATagParams'] = ' name="'.$bMark.'" ';
		return $this->cObj->typoLink($icon, $linkConf);
	}

	

	/**
	 * Generate a plus/minus navigation for the browsable tree.
	 *
	 * @param	array		record for the entry
	 * @param	integer		The current entry number
	 * @param	integer		The total number of entries. If equal to $a, a "bottom" element is returned.
	 * @param	integer		The number of sub-elements to the current element.
	 * @param	boolean		The element was expanded to render subelements if this flag is set.
	 * @param	string		wrapItem, item that is wrapped by this function
	 * @return	string		Image tag with the plus/minus icon.
	 * @access private
	 */
	function PM_wrap($row,$a,$c,$nextCount,$exp,$wrapItem)	{
		$renderElement = $nextCount ? ($exp?'treeMinusIcon':'treePlusIcon') : 'treeJoinIcon';

		$BTM = ($a==$c)?'Bottom':'';

		if ($nextCount)	{
			$cmd=$this->bank.'_'.($exp?'0_':'1_').$row['uid'].'_'.$this->treeName;

			$bMark=($this->bank.'_'.$row['uid']);
			$wrapItem = $this->PM_ATagWrap($wrapItem,$cmd,$bMark,$row);
			if ($exp) {
				$wrapItem = $this->cObj->stdWrap($wrapItem,$this->conf['categoryTreeAdvanced.']['categoryTitle.']['treeMinus.']);
			}
			else {
				$wrapItem = $this->cObj->stdWrap($wrapItem,$this->conf['categoryTreeAdvanced.']['categoryTitle.']['treePlus.']);
			}
		}
		else {
			$wrapItem = $this->cObj->stdWrap($wrapItem,$this->conf['categoryTreeAdvanced.']['categoryTitle.']['treeNoControl.']);
		}
		return $wrapItem;
	}
	/**
	 * Renders the +-= buttons with corresponding commands
	 *
	 * @param	string		$title: ...
	 * @param	resultslist		$row: ...
	 * @return	string		HTML Output
	 */
	function getControl($title,$row) {
		// retrieving the current page id
		$id = intval(t3lib_div::_GET('id'));
		$param_array = array();
		$cObj = t3lib_div::makeInstance('tslib_cObj');
		$cObj->start($row, 'tx_dam_cat');

		if ($this->modeSelIcons
			AND !($this->mode=='tceformsSelect')
			AND ($row['uid'] OR ($row['uid'] == '0' AND $this->linkRootCat))) {

			// genrating plus button
			$this->conf['categoryTreeAdvanced.']['plusIcon.']['stdWrap.']['typolink.']['additionalParams'] .= '&tx_damfrontend_pi1[catPlus]=&tx_damfrontend_pi1[catEquals]=&tx_damfrontend_pi1[catMinus]=&tx_damfrontend_pi1[catPlus_Rec]='.$row['uid'].'&tx_damfrontend_pi1[catMinus_Rec]=&tx_damfrontend_pi1[treeID]='. $this->treeID;
			$control .= $cObj->cObjGetSingle($this->conf['categoryTreeAdvanced.']['plusIcon'], $this->conf['categoryTreeAdvanced.']['plusIcon.']);

			// generating equals buttons
			$this->conf['categoryTreeAdvanced.']['equalsIcon.']['stdWrap.']['typolink.']['additionalParams'] .= '&tx_damfrontend_pi1[catPlus]=&tx_damfrontend_pi1[catEquals]='.$row['uid'].'&tx_damfrontend_pi1[catMinus]=&tx_damfrontend_pi1[catPlus_Rec]=&tx_damfrontend_pi1[catMinus_Rec]=&tx_damfrontend_pi1[treeID]='. $this->treeID;
			$control .= $cObj->cObjGetSingle($this->conf['categoryTreeAdvanced.']['equalsIcon'], $this->conf['categoryTreeAdvanced.']['equalsIcon.']);

			// generate minus button
			$this->conf['categoryTreeAdvanced.']['minusIcon.']['stdWrap.']['typolink.']['additionalParams'] .= '&tx_damfrontend_pi1[catPlus]=&tx_damfrontend_pi1[catEquals]=&tx_damfrontend_pi1[catMinus]='.$row['uid'].'&tx_damfrontend_pi1[catPlus_Rec]=&tx_damfrontend_pi1[catMinus_Rec]=&tx_damfrontend_pi1[treeID]='. $this->treeID;
			$control .= $cObj->cObjGetSingle($this->conf['categoryTreeAdvanced.']['minusIcon'], $this->conf['categoryTreeAdvanced.']['minusIcon.']);

		}
		$control = $cObj->stdWrap($control, $this->conf['categoryTreeAdvanced.']['stdWrapControl.']);
		return $control;
	}


	/* Compiles the HTML code for displaying the structure found inside the ->tree array
	 *
	 * @param	array		"tree-array" - if blank string, the internal ->tree array is used.
	 * @return	string		The HTML code for the tree
	 */
	function printTree($treeArr='')	{
		// 0 - show root icon always
		$i = 0; //counter to determine of a row is even or uneven
			
			// setup ts options for rendering
		if ($this->additionalTreeConf['useExplorerView']==1) {
			$scope='explorerView';						
		}
		else {
			$scope='categoryTreeAdvanced';
		}
		if(!$this->rootIconIsSet AND count($treeArr)) {
				// Artificial record for the tree root, id=0
		}
		if($this->mode=='elbrowser') {
			return $this->eb_printTree($treeArr);
		} else {
			$titleLen = intval($this->BE_USER->uc['titleLen']);
			$out=array();
			$this->renderer->fileContentTree= tsLib_CObj::fileResource($this->renderer->conf[$scope.'.']['templateFile']);
			foreach($treeArr as $k => $v)	{

				$sel_class = 'tree_unselectedCats';
				if (is_array($this->selectedCats) ) {

					$sel_class = $this->get_selectionStatus($v['row']['uid'],$this->treeStructureArray, $this->selectedCats);
					
				}
				if ($this->categorizationMode==true) {

					if (!$this->catLogic->checkCategoryUploadAccess($GLOBALS['TSFE']->fe_user->user['uid'],$v['row']['uid'])) {

						$sel_class ='tree_no_access';
						$v['HTML'] = $this->cObj->stdWrap ($v['HTML'],$this->conf[$scope.'.']['catTitle.']['no_access.']);
					}
				}
				else {

					if ($this->conf['categoryTreeAdvanced.']['markNotAllowedCategories']==1) {
						if (!$this->catLogic->checkCategoryAccess ($GLOBALS['TSFE']->fe_user->user['uid'],$v['row']['uid'],1)){

							$sel_class ='tree_no_access';
							$v['HTML'] = $this->cObj->stdWrap ($v['HTML'],$this->conf[$scope.'.']['categoryTitle.']['no_cat_access.']);
						}
					}
				}
				$title = $this->cObj->stdWrap ($this->getTitleStr($v['row'], $titleLen),$this->conf[$scope.'.']['catTitle.']);
				$control = $this->getControl($title, $v['row'], $v['bank']);
				$v['select_cat'] = $this->wrapCatSelection('&nbsp;',$v['row'],$sel_class);
				$idAttr = htmlspecialchars($this->domIdPrefix.$this->getId($v['row']).'_'.$v['bank']);
				
				if ($this->conf[$scope.'.']['category.']['useAlternatingSubpart']==1) {
					$marker = $GLOBALS['TSFE']->tmpl->splitConfArray(array('cObjNum' => $this->conf[$scope.'.']['category.']['marker']), count($treeArr));
					$marker  = $marker[$i]['cObjNum'];
				}
				else {
					$marker = $this->conf[$scope.'.']['category.']['marker_single'];
				}
				if ($this->conf[$scope.'.']['showRootCategory']==0 && $v['row']['uid']==0) {
					
				}
				else {
					$out['###TREE_ELEMENTS###'].= $this->renderer->renderCategoryTreeCategory($sel_class,$v,$title,$control,$marker,$scope);
					if ($this->additionalTreeConf['useExplorerView']==1) {
						if ($v['isOpen']==1 ) {
							$out['###TREE_ELEMENTS###'].= $this->filelist($v['row']['uid'],$v['treeLevelCSS']);
						}
					}
				}
				$i++;
			}
			return $this->renderer->renderCategoryTree($out, $this->treeID,$scope);
		}
	}

	/**
	 * Returns the title for the input record. If blank, a "no title" label (localized) will be returned.
	 * Do NOT htmlspecialchar the string from this function - has already been done.
	 *
	 * This is an overload of the parent's method which uses BE objects not available in the FE
	 *
	 * @param	array		The input row array (where the key "title" is used for the title)
	 * @param	integer		Title length (30)
	 * @return	string		The title.
	 */
	function getTitleStr($row, $titleLen = 30)	{
		$conf['sys_language_uid'] = $GLOBALS['TSFE']->sys_language_uid;
		$row['pid']=$this->mediaFolder;
		if ($this->conf['categoryTreeAdvanced.']['useLanguageOverlay']==1) $row = tx_dam_db::getRecordOverlay($this->table, $row, $conf);
		// @todo edit ts value for titleLen and add a crop
		$title =  trim($row['title']);
		if (empty($title)) $title = '<em>['.$this->plugin->pi_getLL('no_title').']</em>';
		return $title;
	}

	/**
	 * builds the tree for the treeview
	 *
	 * @return	[arr]		Array of the treeelements
	 */
	function getBrowsableTree() {

			// Get stored tree structure AND updating it if needed according to incoming PM GET var.
		$this->initializePositionSaving();

			// Init done:
		$titleLen=intval($this->BE_USER->uc['titleLen']);
		$treeArr=array();
		$this->treeStructureArray = $this->get_treeStructure();
			// fix null value
			// Traverse mounts:
		foreach($this->MOUNTS as $idx => $uid)	{
				// Set first:
			$this->bank=$idx;
			$isOpen = $this->stored[$idx][$uid] || $this->expandFirst;
			if ($this->conf['categoryTreeAdvanced.']['showRootCategory']==0 && !$uid) {
				$isOpen =true;				
			}

				// Save ids while resetting everything else.
			$curIds = $this->ids;
			$this->reset();
			$this->ids = $curIds;

				// Set PM icon for root of mount:
			$cmd=$this->bank.'_'.($isOpen?"0_":"1_").$uid.'_'.$this->treeName;

			if ($isOpen) {
				$icon=$this->cObj->IMAGE($this->conf['categoryTreeAdvanced.']['treeMinusIcon.']);
			}
			else {
				$icon=$this->cObj->IMAGE($this->conf['categoryTreeAdvanced.']['treePlusIcon.']);
			}

			if ($uid)	{
				$rootRec = $this->getRecord($uid);
			} else {
					// Artificial record for the tree root, id=0
				$rootRec = $this->getRootRecord($uid);
			}
			$noChilds = false;
				// check if this mount has childs, if not, render no tree controls
			if ($rootRec['uid']>0) {
				$childs = $this->get_childCats($rootRec['uid'], $this->treeStructureArray);
				if (empty($childs)) $noChilds=true;
			}
			$titleLen = $this->conf['categoryTreeAdvanced.']['categoryTitle.']['length'] ? $this->conf['categoryTreeAdvanced.']['categoryTitle.']['length']:30;
			if ($noChilds == true ) {
				$firstHtml =$this->cObj->stdWrap( $this->getTitleStr($rootRec,$titleLen),$this->conf['categoryTreeAdvanced.']['categoryTitle.']['treeRoot.']);
			}
			else {
				$firstHtml = $this->PM_ATagWrap($this->getTitleStr($rootRec,$titleLen),$cmd,'',$rootRec);
				if ($isOpen) {
					
					$firstHtml = $this->cObj->stdWrap($firstHtml,$this->conf['categoryTreeAdvanced.']['categoryTitle.']['treeMinus.']);
				}
				else {
					
					$firstHtml = $this->cObj->stdWrap($firstHtml,$this->conf['categoryTreeAdvanced.']['categoryTitle.']['treePlus.']);
				}
			}

				// Preparing rootRec for the mount
			if (is_array($rootRec))	{
				$uid = $rootRec['uid'];		// In case it was swapped inside getRecord due to workspaces.
					// Add the root of the mount to ->tree
				$this->tree[]=array('HTML'=>$firstHtml, 'row'=>$rootRec, 'bank'=>$this->bank,'isOpen'=>$isOpen);

				// If the mount is expanded, go down:
				if ($isOpen)	{
						// Set depth:
					$depthD='<img'.t3lib_iconWorks::skinImg($this->backPath,'gfx/ol/blank.gif','width="18" height="16"').' alt="" />';
					if ($this->addSelfId)	$this->ids[] = $uid;
					$this->getTree($uid,999,$depthD,'',$rootRec['_SUBCSSCLASS']);
				}

					// Add tree:
				$treeArr=array_merge($treeArr,$this->tree);
			}
		}
		return $this->printTree($treeArr);
	}


	/********************************
	 *
	 * tree data buidling
	 *
	 ********************************/

	/**
	 * Fetches the data for the tree
	 *
	 * @param	integer		item id for which to select subitems (parent id)
	 * @param	integer		Max depth (recursivity limit)
	 * @param	string		HTML-code prefix for recursive calls.
	 * @param	string		? (internal)
	 * @param	string		CSS class to use for <td> sub-elements
	 * @return	integer		The count of items on the level
	 */
	function getTree($uid, $depth=999, $depthData='',$blankLineCode='',$subCSSclass='')	{
			// Buffer for id hierarchy is reset:
		$this->buffer_idH=array();

			// Init vars
		$depth=intval($depth);
		$HTML='';
		$a=0;
		$res = $this->getDataInit($uid,$subCSSclass);
		$c = $this->getDataCount($res);
		$crazyRecursionLimiter = 999;
			// Traverse the records:
		while ($crazyRecursionLimiter>0 && $row = $this->getDataNext($res,$subCSSclass))	{
			$a++;
			$crazyRecursionLimiter--;

			$newID = $row['uid'];
			if ($newID==0)	{
				t3lib_BEfunc::typo3PrintError ('Endless recursion detected', 'TYPO3 has detected an error in the database. Please fix it manually (e.g. using phpMyAdmin) and change the UID of '.$this->table.':0 to a new value.<br /><br />See <a href="http://bugs.typo3.org/view.php?id=3495" target="_blank">bugs.typo3.org/view.php?id=3495</a> to get more information about a possible cause.',0);
				exit;
			}
				$this->tree[]=array();		// Reserve space.
				end($this->tree);
				$treeKey = key($this->tree);	// Get the key for this space
				$LN = ($a==$c)?'blank':'line';
	
					// If records should be accumulated, do so
				if ($this->setRecs)	{
					$this->recs[$row['uid']] = $row;
				}
	
					// Accumulate the id of the element in the internal arrays
				$this->ids[] = $idH[$row['uid']]['uid'] = $row['uid'];
				$this->ids_hierarchy[$depth][] = $row['uid'];
				$this->orig_ids_hierarchy[$depth][] = $row['_ORIG_uid'] ? $row['_ORIG_uid'] : $row['uid'];
	
					// Make a recursive call to the next level
				$HTML_depthData = $depthData.$this->cObj->IMAGE($this->conf['categoryTreeAdvanced.']['treeNavIcons.'][$LN.'.']);
				if ($depth>1 && $this->expandNext($newID) && !$row['php_tree_stop'])	{
					$nextCount=$this->getTree(
							$newID,
							$depth-1,
							$this->makeHTML ? $HTML_depthData : '',
							$blankLineCode.','.$LN,
							$row['_SUBCSSCLASS']
						);
					if (count($this->buffer_idH))	$idH[$row['uid']]['subrow']=$this->buffer_idH;
					$exp=1;	// Set "did expand" flag
				} else {
					$nextCount=$this->getCount($newID);
					$exp=0;	// Clear "did expand" flag
				}
				if ($this->additionalTreeConf['useExplorerView']==1) {
					
					$nextCount=1;
				}
					// Set HTML-icons, if any:
				if ($this->makeHTML)	{
					$titleLen = $this->conf['categoryTreeAdvanced.']['categoryTitle.']['length'] ? $this->conf['categoryTreeAdvanced.']['categoryTitle.']['length']:30;
					$title = $this->cObj->stdWrap ($this->getTitleStr($row, $titleLen),$this->conf['categoryTreeAdvanced.']['categoryTitle.']);
					$HTML = $this->PM_wrap($row,$a,$c,$nextCount,$exp,$title);
				}
				$hasSubs=1;
				$childs = $this->get_childCats($row['uid'], $this->treeStructureArray);
				if (empty($childs)) $hasSubs=0;
				
				$treeDepth = 1000-$depth;
	
				$paddingLeft = $treeDepth * $this->conf['categoryTreeAdvanced.']['treeLevelCSS.']['paddingLeft'];
	
					// Finally, add the row/HTML content to the ->tree array in the reserved key.
				$this->tree[$treeKey] = Array(
					'row'=>$row,
					'HTML'=>$HTML,
					'HTML_depthData' => $this->makeHTML==2 ? $HTML_depthData : '',
					'invertedDepth'=>$depth,
					'blankLineCode'=>$blankLineCode,
					'bank' => $this->bank,
					'treeLevelCSS' =>'style ="padding:'.$this->conf['categoryTreeAdvanced.']['treeLevelCSS.']['paddingTop'].'px '.$this->conf['categoryTreeAdvanced.']['treeLevelCSS.']['paddingRight'].'px '.$this->conf['categoryTreeAdvanced.']['treeLevelCSS.']['paddingBottom'].'px '. $paddingLeft .'px;"',
					'isOpen'=>$exp,
					'hasSubs'=>$hasSubs
				);
		}

		$this->getDataFree($res);
		$this->buffer_idH=$idH;
		return $c;
	}


	/**
	 * Returns the root icon for a tree/mountpoint (defaults to the globe)
	 *
	 * @param	array		Record for root.
	 * @return	string		Icon image tag.
	 */
	function getRootIcon($rec) {
		$this->rootIconIsSet=true;
		return  $this->wrapIcon($this->cObj->IMAGE($this->conf['categoryTreeAdvanced.']['treeRootIcon.']),$rec);
	}

		/**
 * Get icon for the row.
 * If $this->iconPath and $this->iconName is set, try to get icon based on those values.
 *
 * @param	array		Item row.
 * @return	string		Image tag.
 */
	function getIcon($row) {
			$icon = $this->cObj->IMAGE($this->conf['categoryTreeAdvanced.']['treeCatIcon.']);
			$icon = $this->wrapIcon($icon,$row);

		return $icon;
	}



	/**
	 * sets the paths for file references
	 *
	 * @param	[string]		$filePath: ...
	 * @return	[type]		...
	 */
 	function setFileRef($filePath) {
 		$this->fileContent = tsLib_CObj::fileResource($filePath);
 		$formCode  = tslib_CObj::getSubpart($this->fileContent, '###EDITFORM###');
 		return tslib_cObj::substituteMarkerArray($formCode, $markerArray);
 	}

	/**
	 * [Describe function...]
	 *
	 * @param	[type]		$parentId: ...
	 * @param	[type]		$subCSSclass: ...
	 * @return	[type]		...
	 */
	function getDataInit($parentId,$subCSSclass='') {
		$this->clause =  ' AND sys_language_uid = 0' ;
 		if ($this->conf['categoryTreeAdvanced.']['showHiddenCategories']==0) $this->clause .= ' AND hidden = 0';  
		if (is_array($this->data)) {
			if (!is_array($this->dataLookup[$parentId][$this->subLevelID])) {
				$parentId = -1;
			} else {
				reset($this->dataLookup[$parentId][$this->subLevelID]);
			}
			return $parentId;
		} else {
			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
						implode(',',$this->fieldArray),
						$this->table,
						$this->parentField.'='.$GLOBALS['TYPO3_DB']->fullQuoteStr($parentId, $this->table).
							t3lib_BEfunc::deleteClause($this->table).
							t3lib_BEfunc::versioningPlaceholderClause($this->table).
							$this->clause,	// whereClauseMightContainGroupOrderBy
						'',
						$this->orderByFields
					);
			return $res;
		}
	}

	/**
	 * returns a list with child cats and their selection status
	 *
	 * @param	[array]		$treeArray $
	 * @param	[type]		$treeStructure: ...
	 * @return	[array]		array with the tree $key = catID $value = parrentID
	 */
	function get_childCats($catUID, $treeStructure) {
		$childs = array();
		foreach ($treeStructure as $cat => $parent) {
			if ($parent==$catUID) {
				if (substr_replace($cat,'',0,4) >0)	$childs[] = substr_replace($cat,'',0,4);
			}
		}
		return $childs;
	}

	/**
	 * returns a flat array with the tree structure
	 *
	 * @param	[array]		$treeArray $
	 * @return	[array]		array with the tree $key = catID $value = parrentID
	 */
	function get_treeStructure () {
		$treeStructure = array();
		foreach($this->MOUNTS as $idx => $uid)	{
			if ($uid=='') $uid = 0;
			$treeStructure['cat_'.$uid]= 0;
			$treeStructure = array_merge($treeStructure, $this->get_treeStructureElements($uid)) ;
		}
		return $treeStructure ;
	}


	/**
	 * returns a flat array with the tree structure
	 *
	 * @param	[array]		$treeArray $
	 * @return	[array]		array with the tree $key = catID $value = parrentID
	 */
	function get_treeStructureElements ($uid) {
		$treeStructure = array();
			$res = $this->getDataInit($uid,$subCSSclass);
			while ( $row = $this->getDataNext($res,$subCSSclass))	{
				$treeStructure['cat_'.$row['uid']]= $uid;
				$treeStructure = array_merge($treeStructure,$this->get_treeStructureElements($row['uid'])) ;
			}
		return $treeStructure;
	}


	/**
	 * returns the selection status for a given category
	 *
	 * @param	[array]		$treeArray $
	 * @param	[type]		$treeStructure: ...
	 * @return	[int]		tree_selectedCats, tree_unselectedCats, tree_selectedPartlyCats
	 */
	function get_selectionStatus($catID,$treeStructure) {
		// check if current category is in selection
		$test = array_search($catID, $this->selectedCats);
		if ($test == 0 ) $test++;
		$sel_class = $test ? "tree_selectedCats" : "tree_unselectedCats";
				#// check if current category has subcategories,
				#// if yes and all selected status  then tree_selectedAllCats
				#// if yes and partly selected then status  tree_selectedPartlyCats
				#// if yes and no selected then tree_selectedNoCats
		$catSelected = false;
		$catNotSelected = false;
		$childCats = $this->get_childCats($catID,$treeStructure);
		if (is_array($childCats) && !empty($childCats)) {

			// has child: so check if they are selected all / partly / none
			foreach ($childCats as $cat) {
				#if ($cat>0) {
				$childSelection = $this->get_selectionStatus($cat,$treeStructure);
				switch ($childSelection){
					case 'tree_selectedCats':
						$catSelected = true;
						break;
					case 'tree_unselectedCats':
						$catNotSelected = true;
						break;
					case 'tree_selectedNoCats':
						$catNotSelected = true;
						break;
					case 'tree_selectedAllCats':
						$catSelected = true;
						break;
					case 'tree_selectedPartlyCats':
						$catSelected = true;
						$catNotSelected = true;
						break;
				}
				#}
			}
			
			if ($catSelected == false and  $catNotSelected==true) {
					//	no cats are selected
				$sel_class ='tree_selectedNoCats';
			}
			else {

				if ($catSelected == true and  $catNotSelected==false) {
					
					//	all cats are selected
					$sel_class ='tree_selectedAllCats';
				}
				else {
					
					$sel_class ='tree_selectedPartlyCats';
				}
			}
		}
		return $sel_class;
	}
	
	/**
	 * returns an array of files belonging to the given category
	 *
	 * @param	[int]		$catID
	 * @return	[int]		tree_selectedCats, tree_unselectedCats, tree_selectedPartlyCats
	 */	
	function filelist($catID,$treeLevelCSS) {
		$currentCat = array();
		$currentCat[]=$catID;
		
		// initialization the document logic
		$this->additionalTreeConf['docLogic']->categories = array();
		$this->additionalTreeConf['docLogic']->categories[$this->cObj->data['uid']]=$currentCat;
		$result=array();
		if (is_array($this->additionalTreeConf['filter'])) {
			$this->internal['filterError'] = $this->additionalTreeConf['docLogic']->setFilter($this->internal['filter']);
		}
		#TODO implement sorting for the filelist
		$this->additionalTreeConf['docLogic']->orderBy = 'tx_dam.'. 'title';
		$this->additionalTreeConf['docLogic']->limit = '0,9999';
		$files = $this->additionalTreeConf['docLogic']->getDocumentList($GLOBALS['TSFE']->fe_user->user['uid']);
		if (!empty($files)) {
			$i=0;
			// Optionsplit for ###FILELIST_RECORD###
			if ($this->conf['explorerViewElements.']['useAlternatingRows']==1) {
				$filelist_record_marker = $GLOBALS['TSFE']->tmpl->splitConfArray(array('cObjNum' => $this->conf['explorerViewElements.']['marker.']['filelist_record_alterning']), count($files));
			}
			else {
				if (!isset($this->conf['explorerViewElements.']['marker.']['filelist_record'])) { $this->conf['explorerViewElements.']['marker.']['filelist_record'] = '###FILELIST_RECORD###'; }
				$filelist_record_marker = $this->conf['explorerViewElements.']['marker.']['filelist_record'];
			}
	 		$list_Code = tsLib_CObj::getSubpart(tsLib_CObj::fileResource($this->conf['templateFile']),$filelist_record_marker);
			$cObj = t3lib_div::makeInstance('tslib_cObj');
			foreach ($files as $elem) {
				$i++;
				$elem['treeLevelCSS']=$treeLevelCSS;
				$content .= $this->renderer->renderDamRecordRow($elem,$i,0,9999,'explorerViewElements',$list_Code,$cObj);
			}
		}
		else {
			// render message
#t3lib_div::debug($this->renderer->conf);
			if ($this->renderer->conf['explorerView.']['showEmptyMessage']==1) $content .= $this->renderer->cObj->stdWrap($this->renderer->get_LLLabel('noDocInCat'),$this->renderer->conf['explorerView.']['showEmptyMessage.']);
		}
		$content = $this->renderer->cObj->stdWrap($content,$this->renderer->conf['explorerView.']['filelist.']);
		#t3lib_div::debug($content);
		
		return $content;
	}
}
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/dam_frontend/frontend/class.tx_damfrontend_catTreeViewAdvanced.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/dam_frontend/frontend/class.tx_damfrontend_catTreeViewAdvanced.php']);
}

?>
