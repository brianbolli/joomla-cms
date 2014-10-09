<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Helper
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

/**
 * Media helper class
 *
 * @package     Joomla.Libraries
 * @subpackage  Helper
 * @since       3.2
 */

class FolderTreeNode extends JObject
{

}

class JHelperFolderTree
{
		protected $nodes = array();

		public function add() {

		}
		public function clear() {
			$this->nodes = array();
		}

		public function getFolderTreeObject($fileName) {
			JHelperMedia::getTypeIcon($fileName);
		}
}