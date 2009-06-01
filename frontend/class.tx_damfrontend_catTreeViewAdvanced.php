<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2006-2009 in2form.com (typo3@in2form.com)
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
 * 
 */
require_once(PATH_txdam.'components/class.tx_dam_selectionCategory.php');


/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 *   63: class tx_damfrontend_catTreeView extends tx_dam_selectionCategory
 *   77:     function tx_damfrontend_catTreeView()
 *  116:     function init($treeID = '', $plugin = null)
 *  132:     function expandNext($id)
 *  144:     function initializePositionSaving()
 *  167:     function savePosition()
 *  181:     function wrapTitle($title,$row,$bank=0)
 *  216:     function PM_ATagWrap($icon,$cmd,$bMark='treeroot')
 *  235:     function getControl($title,$row)
 *  296:     function printTree($treeArr='')
 *  359:     function getTitleStr($row, $titleLen = 30)
 *  373:     function getBrowsableTree()
 *
 * TOTAL FUNCTIONS: 11
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
	
	var $rootIconIsSet = false;								// indicates, if a root icon must be added or not
	/**
	 * prepares the category tree
	 *
	 * @return	void
	 * some small changes from the original category Tree */
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

		$this->clause = ' AND deleted=0';
		$this->orderByFields = 'sorting,title';

		$conf = tx_dam::config_getValue('setup.selections.'.$this->treeName);
		$this->TSconfig = $conf['properties'];
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
		//todo reimplement
		#$langWhere = ' AND sys_language_uid = ' . $GLOBALS['TSFE']->sys_language_uid;
		parent::init($langWhere);
 		$this->treeID = $treeID;
 		$this->user =& $GLOBALS['TSFE']->fe_user;
 		$this->backPath = 'typo3/';
		if (isset($plugin)) $this->plugin = $plugin;
		$this->cObj = $this->plugin->cObj;
		$this->conf = $this->plugin->conf;
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
	 *
	 *	@param int $levelDeepth defines how deep the tree is expanded
	 * 	@return	void
	 */
 	function expandTreeLevel($levelDeepth=0) {
			// expand only if tree was not expanded yet and level > 0
		if ($this->user->getKey("ses",$this->treeID.'expandTreeLevel')<>1 && $levelDeepth>0) {
			foreach ($this->MOUNTS as $mount => $ID) {
				$this->stored[$mount][$ID]=1;
				$this->savePosition();
				// TODO support more than one level
				$this->user->setKey("ses",$this->treeID.'expandTreeLevel', 1);			
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
		$this->conf['categoryTree.']['categoryTitle.']['parameter'] = $GLOBALS['TSFE']->id;
		$this->conf['categoryTree.']['categoryTitle.']['additionalParams'].= t3lib_div::implodeArrayForUrl('',$param_array);
		return $this->cObj->typoLink($title, $this->conf['categoryTree.']['categoryTitle.']);
	}

	/**
	 * wraps a Title in a link
	 *
	 * @param	string		$title: ...
	 * @param	resultset		$row: ...
	 * @param	string		$command: "selectAll", "selectThis", "deselect","deselectAll" 
	 * @param	string		$sel_class
	 * @return	string		html ...
	 * 
	 */
	function wrapCatSelection($wrapItem,$row, $sel_class) {
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
					'tx_damfrontend_pi1[catPlus]' => $row['uid'],
					'tx_damfrontend_pi1[catEquals]' => null,
					'tx_damfrontend_pi1[catMinus]' => null,
					'tx_damfrontend_pi1[catPlus_Rec]' => null,
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
		if ($id > 0) { $param_array['tx_damfrontend_pi1[id]'] = $id; }
		$this->conf['categoryTree.']['categorySelection.']['link.']['parameter'] = $GLOBALS['TSFE']->id;
		if (is_array($param_array))	$this->conf['categoryTree.']['categorySelection.']['link.']['additionalParams'].= t3lib_div::implodeArrayForUrl('',$param_array);
		return $this->cObj->typoLink($wrapItem, $this->conf['categoryTree.']['categorySelection.']['link.']);
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
	 * @see t3lib_pageTree::PMicon()
	 */
	function PM_wrap($row,$a,$c,$nextCount,$exp,$wrapItem)	{
		#t3lib_div::debug($wrapItem);
		$renderElement = $nextCount ? ($exp?'treeMinusIcon':'treePlusIcon') : 'treeJoinIcon';
		
		$BTM = ($a==$c)?'Bottom':'';		
				
		if ($nextCount)	{
			$cmd=$this->bank.'_'.($exp?'0_':'1_').$row['uid'].'_'.$this->treeName;
			
			$bMark=($this->bank.'_'.$row['uid']);
			$wrapItem = $this->PM_ATagWrap($wrapItem,$cmd,$bMark);
			if ($exp) {
				$wrapItem = $this->cObj->stdWrap($wrapItem,$this->conf['categoryTree.']['categoryTitle.']['treeMinus.']);
			}
			else {
				$wrapItem = $this->cObj->stdWrap($wrapItem,$this->conf['categoryTree.']['categoryTitle.']['treePlus.']);
			}
		}
		else {
			$wrapItem = $this->cObj->stdWrap($wrapItem,$this->conf['categoryTree.']['categoryTitle.']['treeNoControl.']);
		}
		#t3lib_div::debug($wrapItem);
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
			$this->conf['categoryTree.']['plusIcon.']['stdWrap.']['typolink.']['additionalParams'] .= '&tx_damfrontend_pi1[catPlus]=&tx_damfrontend_pi1[catEquals]=&tx_damfrontend_pi1[catMinus]=&tx_damfrontend_pi1[catPlus_Rec]='.$row['uid'].'&tx_damfrontend_pi1[catMinus_Rec]=&tx_damfrontend_pi1[treeID]='. $this->treeID;
			$control .= $cObj->cObjGetSingle($this->conf['categoryTree.']['plusIcon'], $this->conf['categoryTree.']['plusIcon.']);
			
			// generating equals buttons
			$this->conf['categoryTree.']['equalsIcon.']['stdWrap.']['typolink.']['additionalParams'] .= '&tx_damfrontend_pi1[catPlus]=&tx_damfrontend_pi1[catEquals]='.$row['uid'].'&tx_damfrontend_pi1[catMinus]=&tx_damfrontend_pi1[catPlus_Rec]=&tx_damfrontend_pi1[catMinus_Rec]=&tx_damfrontend_pi1[treeID]='. $this->treeID;
			$control .= $cObj->cObjGetSingle($this->conf['categoryTree.']['equalsIcon'], $this->conf['categoryTree.']['equalsIcon.']);

			// generate minus button
			$this->conf['categoryTree.']['minusIcon.']['stdWrap.']['typolink.']['additionalParams'] .= '&tx_damfrontend_pi1[catPlus]=&tx_damfrontend_pi1[catEquals]=&tx_damfrontend_pi1[catMinus]='.$row['uid'].'&tx_damfrontend_pi1[catPlus_Rec]=&tx_damfrontend_pi1[catMinus_Rec]=&tx_damfrontend_pi1[treeID]='. $this->treeID;
			$control .= $cObj->cObjGetSingle($this->conf['categoryTree.']['minusIcon'], $this->conf['categoryTree.']['minusIcon.']);
			
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
		$i = 0; //counter to determine of a row is even or uneven
		if(!$this->rootIconIsSet AND count($treeArr)) {
				// Artificial record for the tree root, id=0
			#t3lib_div::debug($rootRec);
			#$firstHtml =$this->getRootIcon($rootRec);
			#$treeArr = array_merge(array(array('HTML' => $firstHtml,'row' => $rootRec,'bank'=>0)), $treeArr);
		}
		$class="treeelem";
		#t3lib_div::debug($treeArr);
		t3lib_div::debug($this->selectedCats);
		if($this->mode=='elbrowser') {
			return $this->eb_printTree($treeArr);
		} else {
			$titleLen = intval($this->BE_USER->uc['titleLen']);
			$out=array();
			foreach($treeArr as $k => $v)	{
				if (is_array($this->selectedCats)) {
						// check if current category is in selection
					$test = array_search($v['row']['uid'], $this->selectedCats);
					if ($test == 0 ) $test++;
					$sel_class = $test ? "tree_selectedCats" : "tree_unselectedCats";
						// check if current category has subcategories, 
						// if yes and all selected status  then tree_selectedAllCats
						// if yes and partly selected then status  tree_selectedPartlyCats
						// if yes and no selected then tree_selectedNoCats
				} else {
					$sel_class = 'tree_unselectedCats';
				}
					// decide how the link must be rendered
					// check if this cat has childs
				if (is_array($this->selectedCats) ) {
					$childCats = $this->get_childCats($v['row']['uid']);
					$catSelected = false;
					$catNotSelected = false;
					
					if (is_array($childCats)){
						// has child: so check if they are selected all / partly / none
						t3lib_div::debug($childCats);
						foreach ($childCats as $cat) {
							$test = null;
							$found=false;
							$test = array_search($cat['uid'], $this->selectedCats);
							t3lib_div::debug($test);
							var_dump($test);
							if ($test === false) {
								$found = false;
							}
							else {
								$found = true;
							}
							if ($found==true) {
								$catSelected = true;
								#t3lib_div::debug('true');
							} 
							else {
								$catNotSelected = true;
							}
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
				}
				
				#t3lib_div::debug($sel_class);
				$title = $this->cObj->stdWrap ($this->getTitleStr($v['row'], $titleLen),$this->conf['categoryTree.']['catTitle.']);
				$control = $this->getControl($title, $v['row'], $v['bank']);
				$title = $this->wrapTitle($title, $v['row'], $v['bank']);
				$v['select_cat'] = $this->wrapCatSelection('&nbsp;',$v['row'],$sel_class);
				$idAttr = htmlspecialchars($this->domIdPrefix.$this->getId($v['row']).'_'.$v['bank']);
				if ($this->conf['categoryTree.']['category.']['useAlternatingSubpart']==1) {
					$marker = $GLOBALS['TSFE']->tmpl->splitConfArray(array('cObjNum' => $this->conf['categoryTree.']['category.']['marker']), count($treeArr));
					$marker  = $marker[$i]['cObjNum'];
				}
				else {
					$marker = $this->conf['categoryTree.']['category.']['marker'];
				}
				 $out['###TREE_ELEMENTS###'].= $this->renderer->renderCategoryTreeCategory($class,$idAttr,$sel_class,$v,$title,$control,$marker);
				$i++;
			}
			return $this->renderer->renderCategoryTree($out);
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
		$row = tx_dam_db::getRecordOverlay($this->table, $row, $conf);
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
			
				//prüfung auf childs einbauen
				
			if ($uid)	{
				$rootRec = $this->getRecord($uid);
				#t3lib_div::debug($rootRec);
				#$firstHtml.=$this->getIcon($rootRec);
			} else {
					// Artificial record for the tree root, id=0
					$rootRec = $this->getRootRecord($uid);	
					#$firstHtml.=$this->getRootIcon($rootRec);
			}
			#t3lib_div::debug($rootRec);			
					#$bMark=($this->bank.'_'.$row['uid']);
					$firstHtml = $this->PM_ATagWrap($rootRec['title'],$cmd);
					if ($isOpen) {
						$firstHtml = $this->cObj->stdWrap($firstHtml,$this->conf['categoryTree.']['categoryTitle.']['treeMinus.']);
					}
					else {
						$firstHtml = $this->cObj->stdWrap($firstHtml,$this->conf['categoryTree.']['categoryTitle.']['treePlus.']);
					}
				#}
				#else {
				#	$firstHtml = $this->cObj->stdWrap($firstHtml,$this->conf['categoryTree.']['categoryTitle.']['treeNoControl.']);
				#}
				#t3lib_div::debug($wrapItem);
				#return $wrapItem;
		
		#$firstHtml= $this->PM_wrap($icon,$cmd);

				// Preparing rootRec for the mount


			if (is_array($rootRec))	{
				$uid = $rootRec['uid'];		// In case it was swapped inside getRecord due to workspaces.
				
				if ($this->conf['categoryTree']['showRootCategory']==0) {
						// Add the root of the mount to ->tree
					$this->tree[]=array('HTML'=>$firstHtml, 'row'=>$rootRec, 'bank'=>$this->bank);
				}
				
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
		#t3lib_div::debug($treeArr);
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
	function getTree($uid, $depth=999, $depthData='',$blankLineCode='',$subCSSclass='', $rootOnly = false)	{
		// Buffer for id hierarchy is reset:
		$this->buffer_idH=array();

			// Init vars
		$depth=intval($depth);
		$HTML='';
		$a=0;
		if ($rootOnly==false) {
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
	
					// Set HTML-icons, if any:
				if ($this->makeHTML)	{
					#$HTML = $depthData.$this->PMicon($row,$a,$c,$nextCount,$exp);
					#t3lib_div::debug($row);
					$HTML = $this->PM_wrap($row,$a,$c,$nextCount,$exp,$row['title']);
					#$HTML.=$this->wrapStop($this->getIcon($row),$row);
				}
				#t3lib_div::debug($depth);
				$treeDepth = 1000-$depth;
				$paddingLeft = $treeDepth * $this->conf['categoryTree.']['treeLevelCSS.']['paddingLeft'];
					// Finally, add the row/HTML content to the ->tree array in the reserved key.
				$this->tree[$treeKey] = Array(
					'row'=>$row,
					'HTML'=>$HTML,
					'HTML_depthData' => $this->makeHTML==2 ? $HTML_depthData : '',
					'invertedDepth'=>$depth,
					'blankLineCode'=>$blankLineCode,
					'bank' => $this->bank,
					'treeLevelCSS' =>'style ="padding:0px 0px 0px '. $paddingLeft .'px;"'
				);
			}
	
			$this->getDataFree($res);
			$this->buffer_idH=$idH;
			return $c;
		}
		else {
			
			$row = $this->getRecord($uid);
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
			/*if ($depth>1 && $this->expandNext($newID) && !$row['php_tree_stop'])	{
				$nextCount=$this->getTree(
						$newID,
						$depth-1,
						$this->makeHTML ? $HTML_depthData : '',
						$blankLineCode.','.$LN,
						$row['_SUBCSSCLASS']
					);
				if (count($this->buffer_idH))	$idH[$row['uid']]['subrow']=$this->buffer_idH;
				$exp=1;	// Set "did expand" flag
			} else {*/
				$nextCount=$this->getCount($newID);
				$exp=0;	// Clear "did expand" flag
			#}*/

				// Set HTML-icons, if any:
			if ($this->makeHTML)	{
				#$HTML = $depthData.$this->PMicon($row,$a,$c,$nextCount,$exp);
				#t3lib_div::debug($row);
				$HTML = $this->PM_wrap($row,$a,$c,$nextCount,$exp,$row['title']);
				#$HTML.=$this->wrapStop($this->getIcon($row),$row);
			}
			#t3lib_div::debug($depth);
			$treeDepth = 999-$depth;
			#$paddingLeft = $treeDepth * $this->conf['categoryTree.']['treeLevelCSS.']['paddingLeft'];
				// Finally, add the row/HTML content to the ->tree array in the reserved key.
			$this->tree[$treeKey] = Array(
				'row'=>$row,
				'HTML'=>$HTML,
				'HTML_depthData' => $this->makeHTML==2 ? $HTML_depthData : '',
				'invertedDepth'=>$depth,
				'blankLineCode'=>$blankLineCode,
				'bank' => $this->bank,
				'treeLevelCSS' =>'style ="padding:0px 0px 0px '. $paddingLeft .'px;"'
			);
			$this->buffer_idH=$idH;
			return $c;
		}
	}

	/**
	 * Returns the root icon for a tree/mountpoint (defaults to the globe)
	 *
	 * @param	array		Record for root.
	 * @return	string		Icon image tag.
	 */
	function getRootIcon($rec) {
		$this->rootIconIsSet=true;
		return  $this->wrapIcon($this->cObj->IMAGE($this->conf['categoryTree.']['treeRootIcon.']),$rec);
	}
	
		/**
	 * Get icon for the row.
	 * If $this->iconPath and $this->iconName is set, try to get icon based on those values.
	 *
	 * @param	array		Item row.
	 * @return	string		Image tag.
	 */
	function getIcon($row) {
			$icon = $this->cObj->IMAGE($this->conf['categoryTree.']['treeCatIcon.']);
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
 	
 	
 	
 	
	function getDataInit($parentId,$subCSSclass='') {
		if (is_array($this->data)) {
			if (!is_array($this->dataLookup[$parentId][$this->subLevelID])) {
				$parentId = -1;
			} else {
				reset($this->dataLookup[$parentId][$this->subLevelID]);
			}
			return $parentId;
		} else {
			#t3lib_div::debug($this->table);
			#t3lib_div::debug(implode(',',$this->fieldArray));
			#t3lib_div::debug($this->parentField.'='.$GLOBALS['TYPO3_DB']->fullQuoteStr($parentId, $this->table).t3lib_BEfunc::deleteClause($this->table).t3lib_BEfunc::versioningPlaceholderClause($this->table).$this->clause);
			#t3lib_div::debug($this->orderByFields);
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
	
	function get_childCats($catUID) {
		$res = $this->getDataInit($catUID);
		$c = $this->getDataCount($res);
		if ($c > 0)	{
			$childCats = array();
			while ($row = $this->getDataNext($res,$subCSSclass)) {
				$childCats[] = $row;
			}
			return $childCats;
		}
		else {
			return false;
		}
	}
}	
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/dam_frontend/frontend/class.tx_damfrontend_catTreeViewAdvanced.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/dam_frontend/frontend/class.tx_damfrontend_catTreeViewAdvanced.php']);
}

?>
