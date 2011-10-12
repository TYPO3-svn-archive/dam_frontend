<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2006-2011 in2code.de (typo3@in2code.de)
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
 * What does it do?
 *
 * @package typo3
 * @subpackage tx_dam_frontend
 * @author Stefan Busemann <typo3@in2code.de>
 *
 * Some scripts that use this class:	--
 * Depends on:		--
 */
require_once(PATH_txdam.'components/class.tx_dam_selectionCategory.php');
require_once(t3lib_extMgm::extPath('dam_frontend') . '/DAL/class.tx_damfrontend_DAL_categories.php');

/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 *   72: class tx_damfrontend_catTreeView extends tx_dam_selectionCategory
 *   92:     function tx_damfrontend_catTreeView()
 *  127:     function init($treeID = '', $plugin = null)
 *  147:     function expandNext($id)
 *  159:     function initializePositionSaving()
 *  183:     function expandTreeLevel($levelDeepth=0)
 *  200:     function savePosition()
 *  214:     function wrapTitle($title,$row,$bank=0)
 *  243:     function PM_ATagWrap($icon,$cmd,$bMark='treeroot')
 *  264:     function PMicon($row,$a,$c,$nextCount,$exp)
 *  287:     function getControl($title,$row)
 *  321:     function printTree($treeArr='')
 *  386:     function getTitleStr($row, $titleLen = 30)
 *  400:     function getBrowsableTree()
 *
 *              SECTION: tree data buidling
 *  481:     function getTree($uid, $depth=999, $depthData='',$blankLineCode='',$subCSSclass='')
 *  565:     function getRootIcon($rec)
 *  577:     function getIcon($row)
 *
 * TOTAL FUNCTIONS: 16
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */
class tx_damfrontend_catTreeView extends tx_dam_selectionCategory {

 	var $user;   											// instead of storing the data in the backend user, this data is stored in fe user
 	var $sessionVar = 'tx_damdownloads_treeState';			// name of the key, where to store the treeState in the current Session
	var $selectedCats;										// Array of currently selected cats
	var $catLogic;											// array which holds all selected categories
	var $treeID;											// ID Number of the tree given from the flexform configuration
	var $plugin;											// Back-reference to the calling plugin
	var $cObj;												// cObj
	var $conf;												// configuration array
	var $mediaFolder;										// ID of the Folder, which contains the dam records

	var $rootIconIsSet = false;								// indicates, if a root icon must be added or not
	/**
	 * prepares the category tree
	 *
	 * some small changes from the original category Tree
	 *
	 * @return	void
	 */
 	function tx_damfrontend_catTreeView() {

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

		$this->clause = ' AND deleted=0';
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
 		$langWhere = ' AND sys_language_uid = 0';
		if (isset($plugin)) $this->plugin = $plugin;
		$this->cObj = $this->plugin->cObj;
		$this->conf = $this->plugin->conf;
 		if ($this->conf['categoryTree.']['showHiddenCategories']==0) $langWhere .= ' AND hidden = 0';
		parent::init($langWhere);
 		$this->treeID = $treeID;
 		$this->user =& $GLOBALS['TSFE']->fe_user;
 		$this->backPath = 'typo3/';

 		if ($this->conf['categoryTree.']['sorting']) $this->orderByFields = $this->conf['categoryTree.']['sorting'];
 		if ($this->conf['categoryTreeAdvanced.']['doNotShowNotAllowedCategories'] == 1) {
 			$this->catLogic = t3lib_div::makeInstance('tx_damfrontend_DAL_categories');
 		}
	}

	/**
	 * expands the category tree
	 *
	 * @param	[int]		$id: ...
	 * @return	[type]		...
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
	 * returns a flat array with the tree structure (al treemaunts)
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
	 * @param	string		$cmd: Tree Control command (open or close the tree)
	 * @param	string		$scope: a unique string for the caller of the function, to create unique names
	 * @return	string		html ...
	 */
	function wrapTitle($title,$row,$bank=0,$cmd,$scope='default') {
		$id = (int)t3lib_div::_GET('id');
		$param_array = array (
			'tx_damfrontend_pi1[catPlus]' => null,
			'tx_damfrontend_pi1[catEquals]' => null,
			'tx_damfrontend_pi1[catMinus]' => null,
			'tx_damfrontend_pi1[catPlus_Rec]' => null,
			'tx_damfrontend_pi1[catMinus_Rec]' => null,
			'tx_damfrontend_pi1[treeID]' => $this->treeID
		);

		switch ($this->conf['categoryTree.']['catTitle.']['actions.']['selectCat']) {
			case 'catEquals':
				$param_array['tx_damfrontend_pi1[catEquals]']=$row['uid'];
				break;
			case 'catPlus':
				$param_array['tx_damfrontend_pi1[catPlus]']=$row['uid'];
				break;
			case 'catPlus_Rec':
				$param_array['tx_damfrontend_pi1[catPlus_Rec]']=$row['uid'];
				break;
		}

		if ($this->conf['categoryTree.']['catTitle.']['actions.']['openTree']==1) {
			$param_array['PM']=$cmd;
		}

		if ($this->conf['categoryTree.']['resetFilterOnClick']==1) {
			$param_array['tx_damfrontend_pi1[resetFilter]']=1;
		}



		if ($id > 0) { $param_array['tx_damfrontend_pi1[id]'] = $id; }
		$this->conf['categoryTree.']['categoryTitle.']['parameter'] = $GLOBALS['TSFE']->id;
		$linkConfOrg = $this->conf['categoryTree.']['categoryTitle.']['ATagParams'];
		$this->conf['categoryTree.']['categoryTitle.']['ATagParams'].= ' name="'.$scope.$cmd.'" ';
		$sectionConfOrg = $this->conf['categoryTree.']['categoryTitle.']['section'];
		if (!$this->conf['categoryTree.']['categoryTitle.']['section']) {
			$pos = strpos($cmd,'_');
			if ($pos) {
				//return left part
				$section = substr($cmd,0,$pos+1);
				$invert = substr($cmd,$pos+1,1);
				$inverted = 0;
				if ($invert==0)$inverted=1;
				$sectionRight = substr($cmd,$pos+2);
				$section = $section.$inverted.$sectionRight;
			}
			else {
				$section =$cmd;
			}
			$this->conf['categoryTree.']['categoryTitle.']['section']=$scope.$section;
		}
		#$this->conf['categoryTree.']['categoryTitle.']['section']=$scope.$cmd;
		$this->conf['categoryTree.']['categoryTitle.']['additionalParams'].= t3lib_div::implodeArrayForUrl('',$param_array);
		$content = $this->cObj->typoLink($title, $this->conf['categoryTree.']['categoryTitle.']);
		$this->conf['categoryTree.']['categoryTitle.']['ATagParams'] = $linkConfOrg;
		$this->conf['categoryTree.']['categoryTitle.']['section'] = $sectionConfOrg;
		
		if (intval($GLOBALS['TSFE']->fe_user->getKey('ses', 'currentCategory'))==$row['uid']) {
			$content = $this->cObj->stdWrap($content, $this->conf['categoryTree.']['currentCatWrap.']);
		}
		
		return $content;
	}


	/**
	 * PM_ATagWrap
	 *
	 * renders the plus or minus sign
	 *
	 * @param	string		$icon: html (img Tag)
	 * @param	string		$cmd: ...
	 * @param	string		$bMark: ...
	 * @return	string		...
	 */
	function PM_ATagWrap($icon,$cmd,$bMark='treeroot')	{
		$linkConf = array();
		$linkConf['parameter.']['data'] = 'TSFE:id';
		$linkConf['additionalParams'] = '&tx_damfrontend_pi1[treeID]='.$this->treeID.'&PM='.htmlspecialchars($cmd);
		$linkConf['section'] = $bMark;
		if ($bMark) $linkConf['ATagParams'] = ' name="'.$bMark.'" ';
		return $this->cObj->typoLink($icon, $linkConf);
	}

	/**
	 * Generate the plus/minus icon for the browsable tree.
	 *
	 * @param	array		record for the entry
	 * @param	integer		The current entry number
	 * @param	integer		The total number of entries. If equal to $a, a "bottom" element is returned.
	 * @param	integer		The number of sub-elements to the current element.
	 * @param	boolean		The element was expanded to render subelements if this flag is set.
	 * @return	string		Image tag with the plus/minus icon.
	 * @access private
	 * @see t3lib_pageTree::PMicon()
	 */
	function PMicon($row,$a,$c,$nextCount,$exp)	{

		$renderElement = $nextCount ? ($exp?'treeMinusIcon':'treePlusIcon') : 'treeJoinIcon';

		$BTM = ($a==$c)?'Bottom':'';
		$icon=$this->cObj->IMAGE($this->conf['categoryTree.'][$renderElement.$BTM.'.']);

		if ($nextCount)	{
			$cmd=$this->bank.'_'.($exp?'0_':'1_').$row['uid'].'_'.$this->treeName;
			$bMark=($this->bank.'_'.$row['uid']);
			$icon = $this->PM_ATagWrap($icon,$cmd,$bMark);
		}

		return $icon;
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

		if ($this->conf['categoryTree.']['resetFilterOnClick']==1) {
			$additionalParams='&tx_damfrontend_pi1[resetFilter]=1';
		}

		if ($this->modeSelIcons
			AND !($this->mode=='tceformsSelect')
			AND ($row['uid'] OR ($row['uid'] == '0' AND $this->linkRootCat))) {

			if ($this->conf['categoryTree.']['showCategoriesControl.']['plusIcon']==1) {
				// genrating plus button
				$this->conf['categoryTree.']['plusIcon.']['stdWrap.']['typolink.']['additionalParams'] .= '&tx_damfrontend_pi1[catPlus]=&tx_damfrontend_pi1[catEquals]=&tx_damfrontend_pi1[catMinus]=&tx_damfrontend_pi1[catPlus_Rec]='.$row['uid'].'&tx_damfrontend_pi1[catMinus_Rec]=&tx_damfrontend_pi1[treeID]='. $this->treeID .$additionalParams;

				$control .= $cObj->cObjGetSingle($this->conf['categoryTree.']['plusIcon'], $this->conf['categoryTree.']['plusIcon.']);
			}

			if ($this->conf['categoryTree.']['showCategoriesControl.']['equalsIcon']==1) {
				// generating equals buttons
				$this->conf['categoryTree.']['equalsIcon.']['stdWrap.']['typolink.']['additionalParams'] .= '&tx_damfrontend_pi1[catPlus]=&tx_damfrontend_pi1[catEquals]='.$row['uid'].'&tx_damfrontend_pi1[catMinus]=&tx_damfrontend_pi1[catPlus_Rec]=&tx_damfrontend_pi1[catMinus_Rec]=&tx_damfrontend_pi1[treeID]='. $this->treeID .$additionalParams;
				$control .= $cObj->cObjGetSingle($this->conf['categoryTree.']['equalsIcon'], $this->conf['categoryTree.']['equalsIcon.']);
			}

			if ($this->conf['categoryTree.']['showCategoriesControl.']['minusIcon']==1) {
				// generate minus button
				$this->conf['categoryTree.']['minusIcon.']['stdWrap.']['typolink.']['additionalParams'] .= '&tx_damfrontend_pi1[catPlus]=&tx_damfrontend_pi1[catEquals]=&tx_damfrontend_pi1[catMinus]='.$row['uid'].'&tx_damfrontend_pi1[catPlus_Rec]=&tx_damfrontend_pi1[catMinus_Rec]=&tx_damfrontend_pi1[treeID]='. $this->treeID .$additionalParams;
				$control .= $cObj->cObjGetSingle($this->conf['categoryTree.']['minusIcon'], $this->conf['categoryTree.']['minusIcon.']);
			}
		}
		$control = $cObj->stdWrap($control, $this->conf['categoryTree.']['stdWrapControl.']);
		return $control;
	}


	/* Compiles the HTML code for displaying the structure found inside the ->tree array
	 *
	 * @param	array		"tree-array" - if blank string, the internal ->tree array is used.
	 * @return	string		The HTML code for the tree
	 */
	function printTree($treeArr='')	{
		// 0 - show root icon always
		if(!$this->rootIconIsSet AND count($treeArr)) {
				// Artificial record for the tree root, id=0
			$rootRec = $this->getRootRecord(0);
			$firstHtml =$this->getRootIcon($rootRec,1,'0_0_0');
			$treeArr = array_merge(array(array('HTML' => $firstHtml,'row' => $rootRec,'bank'=>0)), $treeArr);
		}
		$class="treeelem";

		if($this->mode=='elbrowser') {
			return $this->eb_printTree($treeArr);
		} else {
			$titleLen = intval($this->BE_USER->uc['titleLen']);
			$out='';
				// put a table around it with IDs to access the rows from JS
				// not a problem if you don't need it
				// In XHTML there is no "name" attribute of <td> elements - but Mozilla will not be able to highlight rows if the name attribute is NOT there.
			$out .= '

				<!--
				  TYPO3 tree structure.
				-->
				<table class="typo3-browsetree">';
			if ($this->conf['enableDebug']==1) {
				if ($this->conf['debug.']['tx_damfrontend_catTreeView.']['printTree.']['showTreeArr']==1) t3lib_div::debug($treeArr);
			}

			foreach($treeArr as $k => $v)	{
				if (is_array($this->selectedCats)) {
					$test = array_search($v['row']['uid'], $this->selectedCats);
					if ($test == 0 ) $test++;
					$sel_class = $test ? "tree_selectedCats" : "tree_unselectedCats";
				}
				
				if ($this->conf['categoryTree.']['doNotShowNotAllowedCategories'] == 1) {
					if (!$this->catLogic->checkCategoryAccess($GLOBALS['TSFE']->fe_user->user['uid'], $v['row']['uid'], 1)) {
						continue;
					}
				}
				
				
				if ($this->conf['doNotShowEmptyCategories'] == 1) {
					if (!$this->catLogic->checkCategoryForFiles($v['row']['uid'])) {
						continue;
					}
				}
				
				$idAttr = htmlspecialchars($this->domIdPrefix.$this->getId($v['row']).'_'.$v['bank']);
				$titleLen = $this->conf['categoryTree.']['categoryTitle.']['length'] ? $this->conf['categoryTree.']['categoryTitle.']['length']:30;
				$title = $this->cObj->stdWrap ($this->getTitleStr($v['row'], $titleLen),$this->conf['categoryTree.']['catTitle.']);
				$control = $this->getControl($title, $v['row'], $v['bank']);
				$line='
					<tr class="'.$class.'">
						<td id="'.$idAttr.'" class="'.$sel_class.'">'.
							$v['HTML'].
							$this->wrapTitle($title, $v['row'], $v['bank'],$v['cmd'],'control').
						'</td>
						<td  id="'.$idAttr.'Control" class="typo3-browsetree-control">'.
							($control ? $control : '<span></span>').
						'</td>
					</tr>';
				if ($this->conf['categoryTree.']['showRootCategory']==0 && $v['row']['uid']==0) {

				}
				else {
					$out.= $this->cObj->stdWrap ($line,$this->conf['categoryTree.']['category.']);
				}
			}
			$out .= '
				</table>';
			return $this->cObj->stdWrap($out,$this->conf['categoryTree.']);
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
		if ($this->conf['categoryTree.']['useLanguageOverlay']==1) $row = tx_dam_db::getRecordOverlay($this->table, $row, $conf);
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

			// Traverse mounts:
		foreach($this->MOUNTS as $idx => $uid)	{

				// Set first:
			$this->bank=$idx;
			$isOpen = $this->stored[$idx][$uid] || $this->expandFirst;
			if ($this->conf['categoryTree.']['showRootCategory']==0 && !$uid) {
				$isOpen =true;
			}
				// Save ids while resetting everything else.
			$curIds = $this->ids;
			$this->reset();
			$this->ids = $curIds;

				// Set PM icon for root of mount:
			$cmd=$this->bank.'_'.($isOpen?"0_":"1_").$uid.'_'.$this->treeName;

			if ($isOpen) {
				$icon=$this->cObj->IMAGE($this->conf['categoryTree.']['treeMinusIcon.']);
			}
			else {
				$icon=$this->cObj->IMAGE($this->conf['categoryTree.']['treePlusIcon.']);
			}

			$firstHtml= $this->PM_ATagWrap($icon,$cmd);

				// Preparing rootRec for the mount
			if ($uid)	{
				$rootRec = $this->getRecord($uid);
				$firstHtml.=$this->getIcon($rootRec,$isOpen,$cmd);
			} else {
					// Artificial record for the tree root, id=0
				$rootRec = $this->getRootRecord($uid);
				$firstHtml.=$this->getRootIcon($rootRec,$isOpen,$cmd);
			}

			if (is_array($rootRec))	{
				$uid = $rootRec['uid'];		// In case it was swapped inside getRecord due to workspaces.

					// Add the root of the mount to ->tree
				$this->tree[]=array('HTML'=>$firstHtml, 'row'=>$rootRec, 'bank'=>$this->bank,'cmd'=>$cmd);

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
			$HTML_depthData = $depthData.$this->cObj->IMAGE($this->conf['categoryTree.']['treeNavIcons.'][$LN.'.']);
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

			$cmd = $this->bank.'_'.($exp?'0_':'1_').$row['uid'].'_'.$this->treeName;

				// Set HTML-icons, if any:
			if ($this->makeHTML)	{
				$HTML = $depthData.$this->PMicon($row,$a,$c,$nextCount,$exp);
				$HTML.=$this->wrapStop($this->getIcon($row,$exp,$cmd),$row);
			}

				// Finally, add the row/HTML content to the ->tree array in the reserved key.
			$this->tree[$treeKey] = Array(
				'row'=>$row,
				'HTML'=>$HTML,
				'HTML_depthData' => $this->makeHTML==2 ? $HTML_depthData : '',
				'invertedDepth'=>$depth,
				'blankLineCode'=>$blankLineCode,
				'bank' => $this->bank,
				'cmd' => $cmd,
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
	function getRootIcon($rec,$isOpen,$cmd) {
		$this->rootIconIsSet=true;
		if ($isOpen) {
			return $this->wrapTitle($this->wrapIcon($this->cObj->IMAGE($this->conf['categoryTree.']['treeRootOpenIcon.']),$rec),$rec,0,$cmd,'treeRoot');
		}
		else {
			return $this->wrapTitle($this->wrapIcon($this->cObj->IMAGE($this->conf['categoryTree.']['treeRootIcon.']),$rec),$rec,0,$cmd,'treeRoot');
		}
	}

	/**
	 * Get icon for the row.
	 * If $this->iconPath and $this->iconName is set, try to get icon based on those values.
	 *
	 * @param	array		Item row.
	 * @return	string		Image tag.
	 */
	function getIcon($row,$isOpen,$cmd) {
		if ($isOpen) {
			$icon = $this->cObj->IMAGE($this->conf['categoryTree.']['treeOpenCatIcon.']);
		}
		else {
			$icon = $this->cObj->IMAGE($this->conf['categoryTree.']['treeCatIcon.']);
		}
		$icon = $this->wrapTitle($this->wrapIcon($icon,$row),$row,0,$cmd,'icon') ;

		return $icon;
	}

	function get_subCategories($catID) {
		$res = $this->getDataInit($catID,'subCSSclass');
		$subCategories = array();
		while ( $row = $this->getDataNext($res,$subCSSclass))	{
			$row['catID']=$row['uid'];
			$subCategories[]= $row;
		}
		// Order subcategories by title
		return $subCategories;
	}
}
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/dam_frontend/frontend/class.tx_damfrontend_catTreeView.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/dam_frontend/frontend/class.tx_damfrontend_catTreeView.php']);
}

?>
