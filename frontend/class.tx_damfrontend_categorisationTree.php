<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2006-2008 in2form.com (typo3@in2form.com)
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
 * @author Martin Baum <typo3@in2form.com>
 *
 * Some scripts that use this class:	--
 * Depends on:		--
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 *   74: class tx_damfrontend_categorisationTree extends tx_dam_selectionCategory
 *   91:     function tx_damfrontend_categorisationTree()
 *  127:     function init($treeID='', $plugin=null)
 *  145:     function expandNext($id)
 *  157:     function initializePositionSaving()
 *  179:     function expandTreeLevel($levelDeepth=0)
 *  197:     function savePosition()
 *  212:     function wrapTitle($title,$row,$bank=0)
 *  244:     function PM_ATagWrap($icon,$cmd,$bMark='treeroot')
 *  267:     function PMicon($row,$a,$c,$nextCount,$exp)
 *  290:     function printTree($treeArr='')
 *  352:     function getTitleStr($row, $titleLen = 30)
 *  366:     function getBrowsableTree()
 *
 *              SECTION: tree data buidling
 *  447:     function getTree($uid, $depth=999, $depthData='',$blankLineCode='',$subCSSclass='')
 *  533:     function getRootIcon($rec)
 *  545:     function getIcon($row)
 *
 * TOTAL FUNCTIONS: 15
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */

require_once(PATH_txdam.'components/class.tx_dam_selectionCategory.php');
require_once(t3lib_extMgm::extPath('dam_frontend').'/DAL/class.tx_damfrontend_DAL_categories.php');



class tx_damfrontend_categorisationTree extends tx_dam_selectionCategory {

 	var $user;   											// instead of storing the data in the backend user, this data is stored in fe user
 	var $sessionVar = 'tx_damdownloads_categoriseTree';		// name of the key, where to store the treeState in the current Session
	var $selectedCats;										// Array of currently selected cats
	var $catLogic;											// array which holds all selected categories
	var $treeID;											// ID Number of the tree given from the flexform configuration
	var $piVars;											// PiVars for keeping the vars in the links (must be set from the place where this class is used)
	var $cObj;                       						// for RealURL
	var $conf;												// configuration array
	var $mediaFolder;										// ID of the Folder, which contains the dam records

	/**
	 * prepares the category tree
	 *
	 * @return	void
	 */// some small changes from the original category Tree
 	function tx_damfrontend_categorisationTree() {


		$this->treeID = 1;
		$this->title = 'categorytree';
 		$this->treeName = 'txdamCat';
		$this->domIdPrefix = $this->treeName;
		$this->table = 'tx_dam_cat';
		$this->parentField = $GLOBALS['TCA'][$this->table]['ctrl']['treeParentField'];
		$this->parentField = 'parent_id';
		$this->typeField = $GLOBALS['TCA'][$this->table]['ctrl']['type'];

		$this->renderer; // keeps the reference to the frontend renderer
		$this->catLogic= t3lib_div::makeInstance('tx_damfrontend_DAL_categories');

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
 	function init($treeID='', $plugin=null) {
 		$langWhere = ' AND sys_language_uid = 0';
		parent::init($langWhere);
 		$this->piVars= array();
 		$this->treeID = $treeID;
 		$this->user =& $GLOBALS['TSFE']->fe_user;
 		$this->backPath = 'typo3/';
		if (isset($plugin)) $this->plugin = $plugin;
		$this->cObj = $this->plugin->cObj;
		$this->conf = $this->plugin->conf;
	    // 2012-05-21 Esteban Marin
	    // added custom sorting of categories
	    if(isset($this->conf['categorisationTree.'],$this->conf['categorisationTree.']['sorting']))
	    {
		    $this->orderByFields = $this->conf['categorisationTree.']['sorting'];
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

	/**
	 * expand the tree for the first view
	 *
	 * @param	int		$levelDeepth defines how deep the tree is expanded
	 * @return	void
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
	 * @return	[type]		...
	 */
 	function savePosition()
 	{
 		$this->user->setKey("ses",$this->sessionVar, serialize($this->stored));
 	}


	/**
	 * wraps a Title in a link
	 * or not - if the user has no access rights
	 *
	 * @param	string		$title: ...
	 * @param	resultset		$row: ...
	 * @param	int		$bank: ...
	 * @return	string		html ...
	 */
	function wrapTitle($title,$row,$bank=0) {
		if ($this->catLogic->checkCategoryUploadAccess($GLOBALS['TSFE']->fe_user->user['uid'],$row['uid'])) {
			$id = t3lib_div::_GET('id');
			$param_array = array (
				'tx_damfrontend_pi1[catPlus]' => null,
				'tx_damfrontend_pi1[catEquals]' => null,
				'tx_damfrontend_pi1[catMinus]' => null,
				'tx_damfrontend_pi1[catPlus_Rec]' => null,
				'tx_damfrontend_pi1[catMinus_Rec]' => null,
				'tx_damfrontend_pi1[treeID]' => $this->treeID,
				'tx_damfrontend_pi1[catEditUID]' => $row['uid']
			);

			// 2012-05-21 Esteban Marin
			// Added possibility to select link action
			switch ($this->conf['categorisationTree.']['catTitle.']['actions.']['selectCat']) {
				case 'catEquals':
					$param_array['tx_damfrontend_pi1[catEquals]']=$row['uid'];
					break;
				case 'catPlus':
					$param_array['tx_damfrontend_pi1[catPlus]']=$row['uid'];
					break;
				case 'catPlus_Rec':
					$param_array['tx_damfrontend_pi1[catPlus_Rec]']=$row['uid'];
					break;
				default:
					$param_array['tx_damfrontend_pi1[catPlus]']=$row['uid'];
					break;
			}

			$param_array =array_unique(array_merge($param_array, $this->piVars));
			$this->conf['categorisationTree.']['categoryTitle.']['parameter'] = $GLOBALS['TSFE']->id;
			$this->conf['categorisationTree.']['categoryTitle.']['additionalParams'].= t3lib_div::implodeArrayForUrl('',$param_array);
			return $this->cObj->typoLink($title, $this->conf['categorisationTree.']['categoryTitle.']);
		}
		else {
			return  $this->cObj->stdWrap($title,$this->conf['categorisationTree.']['categoryTitleNoAccess.']);
		}

	}

	/**
	 * PM_ATagWrap
	 *
	 * @param	string		$icon: html (img Tag)
	 * @param	string		$cmd: ...
	 * @param	string		$bMark: ...
	 * @return	string		...
	 */
	function PM_ATagWrap($icon,$cmd,$bMark='treeroot')	{
		$linkConf = array();
		$linkConf['parameter'] = $GLOBALS['TSFE']->id;
		$linkConf['additionalParams'] = t3lib_div::implodeArrayForUrl('',$this->piVars);
		$linkConf['additionalParams'] .= '&PM='.htmlspecialchars($cmd);
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
		$icon=$this->cObj->IMAGE($this->conf['categorisationTree.'][$renderElement.$BTM.'.']);

		if ($nextCount)	{
			$cmd=$this->bank.'_'.($exp?'0_':'1_').$row['uid'].'_'.$this->treeName;
			$bMark=($this->bank.'_'.$row['uid']);
			$icon = $this->PM_ATagWrap($icon,$cmd,$bMark);
		}

		return $icon;
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
			$rootRec = $this->getRootRecord(9);
			$firstHtml =$this->getRootIcon($rootRec);
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
				<table cellpadding="0" cellspacing="0" border="0" class="typo3-browsetree">';

			foreach($treeArr as $k => $v)	{
				if (is_array($this->selectedCats)) {
					$test = array_search($v['row']['uid'], $this->selectedCats);
					if ($test == 0 ) $test++;
					$sel_class = $test ? "tree_selectedCats" : "tree_unselectedCats";
				}
				$idAttr = htmlspecialchars($this->domIdPrefix.$this->getId($v['row']).'_'.$v['bank']);
				$titleLen = $this->conf['categoryTree.']['categoryTitle.']['length'] ? $this->conf['categoryTree.']['categoryTitle.']['length']:30;
				$title = $this->cObj->stdWrap ($this->getTitleStr($v['row'], $titleLen),$this->conf['categoryTree.']['catTitle.']);

				$control = $this->getControl($title, $v['row'], $v['bank']);
				$line='
					<tr class="'.$class.'">
						<td id="'.$idAttr.'" class="'.$sel_class.'">'.
							$v['HTML'].
							$this->wrapTitle($title, $v['row'], $v['bank']).
						'</td>
					</tr>';
				$out.= $this->cObj->stdWrap ($line,$this->conf['categorisationTree.']['category.']);
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
		if ($this->conf['categorisationTree.']['useLanguageOverlay']==1) $row = tx_dam_db::getRecordOverlay($this->table, $row, $conf);
		$title = trim($row['title']);
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
				$icon=$this->cObj->IMAGE($this->conf['categorisationTree.']['treeMinusIcon.']);
			}
			else {
				$icon=$this->cObj->IMAGE($this->conf['categorisationTree.']['treePlusIcon.']);
			}

			$firstHtml= $this->PM_ATagWrap($icon,$cmd);

				// Preparing rootRec for the mount
			if ($uid)	{
				$rootRec = $this->getRecord($uid);
				$firstHtml.=$this->getIcon($rootRec);
			} else {
					// Artificial record for the tree root, id=0
				$rootRec = $this->getRootRecord($uid);
				$firstHtml.=$this->getRootIcon($rootRec);
			}

			if (is_array($rootRec))	{
				$uid = $rootRec['uid'];		// In case it was swapped inside getRecord due to workspaces.

					// Add the root of the mount to ->tree
				$this->tree[]=array('HTML'=>$firstHtml, 'row'=>$rootRec, 'bank'=>$this->bank);

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
			$HTML_depthData = $depthData.$this->cObj->IMAGE($this->conf['categorisationTree.']['treeNavIcons.'][$LN.'.']);
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
				$HTML = $depthData.$this->PMicon($row,$a,$c,$nextCount,$exp);
				$HTML.=$this->wrapStop($this->getIcon($row),$row);
			}

				// Finally, add the row/HTML content to the ->tree array in the reserved key.
			$this->tree[$treeKey] = Array(
				'row'=>$row,
				'HTML'=>$HTML,
				'HTML_depthData' => $this->makeHTML==2 ? $HTML_depthData : '',
				'invertedDepth'=>$depth,
				'blankLineCode'=>$blankLineCode,
				'bank' => $this->bank
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
		return $this->wrapIcon($this->cObj->IMAGE($this->conf['categorisationTree.']['treeRootIcon.']),$rec);
	}

		/**
 * Get icon for the row.
 * If $this->iconPath and $this->iconName is set, try to get icon based on those values.
 *
 * @param	array		Item row.
 * @return	string		Image tag.
 */
	function getIcon($row) {
			$icon = $this->cObj->IMAGE($this->conf['categorisationTree.']['treeCatIcon.']);
			$icon = $this->wrapIcon($icon,$row);

		return $icon;
	}
}
?>
