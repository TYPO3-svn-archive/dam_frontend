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
 *   67: class tx_damfrontend_categorisationTree extends tx_dam_selectionCategory
 *   82:     function tx_damfrontend_categorisationTree()
 *  123:     function init($treeID='', $plugin=null)
 *  141:     function expandNext($id)
 *  153:     function initializePositionSaving()
 *  174:     function savePosition()
 *  189:     function wrapTitle($title,$row,$bank=0)
 *  226:     function PM_ATagWrap($icon,$cmd,$bMark='treeroot')
 *  249:     function getControl($title,$row)
 *  295:     function printTree($treeArr='')
 *  350:     function getBrowsableTree()
 *
 * TOTAL FUNCTIONS: 10
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

		// other Path are used, than in the original file
		// @todo make the path dynamically
		$this->iconName = 'cat.gif';
		$this->iconPath = 'typo3conf/ext/dam/i/';
		$this->rootIcon = 'typo3conf/ext/dam/i/catfolder.gif';

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
 	function init($treeID='', $plugin=null) {
 		$langWhere = ' AND sys_language_uid = ' . $GLOBALS['TSFE']->sys_language_uid;
		parent::init($langWhere);
 		$this->piVars= array();
 		$this->treeID = $treeID;
 		$this->user =& $GLOBALS['TSFE']->fe_user;
 		$this->backPath = 'typo3/';
		if (isset($plugin)) $this->plugin = $plugin;

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
		if ($this->catLogic->checkCategoryAccess($GLOBALS['TSFE']->fe_user->user['uid'],$row['uid'])) {
			$id = t3lib_div::_GET('id');
			/**$param_array = array (
				'catPlus' => $row['uid'],
				'treeID' => $this->treeID
			);*/
		$param_array = array (
			'tx_damfrontend_pi1[catPlus]' => $row['uid'],
			'tx_damfrontend_pi1[catEquals]' => null,
			'tx_damfrontend_pi1[catMinus]' => null,
			'tx_damfrontend_pi1[catPlus_Rec]' => null,
			'tx_damfrontend_pi1[catMinus_Rec]' => null,
			'tx_damfrontend_pi1[treeID]' => $this->treeID
		);
		if ($id != '') $param_array['id'] = $id;
		$param_array = array_merge($this->piVars,$param_array);
    	$url = $this->cObj->getTypoLink_URL($GLOBALS['TSFE']->id, $param_array);
		$title = '<a href="'.$url.'">'.$title.'</a>';
		return $title;
	}
		else {
			return '<span style="color: #aaa" >'.$title.'</span>';
		}

	}

	/**
	 * PM_ATagWrap
	 *
	 * TODO: is this function in use?
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
		// TODO: htmlspecialvars or rawurlencode? IMHO rawurlencode
		$linkConf['additionalParams'] .= '&PM='.htmlspecialchars($cmd);
		$linkConf['section'] = $bMark;
		if ($bMark) $linkConf['ATagParams'] = ' name="'.$bMark.'" ';
		return $this->cObj->typoLink($icon, $linkConf);

	}


// TODO: check why/if this function is not needed anymore

	/**
	 * Renders the +-= buttons with corresponding commands
	 *
	 * @param	string		$title: ...
	 * @param	resultslist		$row: ...
	 * @return	string		HTML Output
	 */
/**
	function getControl($title,$row) {
		// retrieving the current page id
		$id = (int)t3lib_div::_GET('id');
		$control = '<div class="control" >';
		if ($this->modeSelIcons
			AND !($this->mode=='tceformsSelect')
			AND ($row['uid'] OR ($row['uid'] == '0' AND $this->linkRootCat))) {

			// genrating plus button
			$urlVars = array(
				'tx_damfrontend_pi1[catPlus]' => null,
				'tx_damfrontend_pi1[catEquals]' => null,
				'tx_damfrontend_pi1[catMinus]' => null,
				'tx_damfrontend_pi1[catPlus_Rec]' => $row['uid'],
				'tx_damfrontend_pi1[catMinus_Rec]' => null,
				'tx_damfrontend_pi1[treeID]' => $this->treeID
			);
			if ($id != '') $param_array['id'] = $id;
			$url = t3lib_div::linkThisScript($urlVars);
			$icon =	'<img'.t3lib_iconWorks::skinImg($GLOBALS['BACK_PATH'],$this->iconPath.'plus.gif', 'width="8" height="11"').' alt="" border="0"/>';
			$control .= '<a href="'.$url.'">'.$icon.'</a>';

			// generate minus button
			$urlVars = array(
				'tx_damfrontend_pi1[catPlus]' => null,
				'tx_damfrontend_pi1[catEquals]' => null,
				'tx_damfrontend_pi1[catMinus]' => null,
				'tx_damfrontend_pi1[catPlus_Rec]' => null,
				'tx_damfrontend_pi1[catMinus_Rec]' => $row['uid'],
				'tx_damfrontend_pi1[treeID]' => $this->treeID
			);
			if ($id != '') $param_array['id'] = $id;
			$url = t3lib_div::linkThisScript($urlVars);
			$icon =	'<img'.t3lib_iconWorks::skinImg($GLOBALS['BACK_PATH'],$this->iconPath.'/minus.gif', 'width="8" height="11"').' alt="" border="0"/>';
			$control .= '<a href="'.$url.'">'.$icon.'</a>';
		}
		$control .= '</div>';
		return $control;
	}*/


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
				$title = $this->getTitleStr($v['row'], $titleLen);
				// TODO: how/where are the controls rendered?
				$control = $this->getControl($title, $v['row'], $v['bank']);
				$out.='
					<tr class="'.$class.'">
						<td id="'.$idAttr.'" class="'.$sel_class.'">'.
							$v['HTML'].
							$this->wrapTitle($title, $v['row'], $v['bank']).
						'</td>
					</tr>';
			}
			$out .= '
				</table>';
			return $out;
		}
	}


	/**
	 * calls the parrent method getBrowsableTree
	 *
	 * @return	[type]		...
	 */
	function getBrowsableTree() {
		return  parent::getBrowsableTree();
	}
}
?>
