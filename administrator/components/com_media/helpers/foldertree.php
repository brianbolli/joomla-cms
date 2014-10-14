<?php
/**
 * @package     Joomla.FolderTree
 * @subpackage  FolderTree
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * ToolBar handler
 *
 * @package     Joomla.FolderTree
 * @subpackage
 * @since       1.5
 */
class JFolderTree
{

	protected $tree = array();
	protected $data;
	protected $name;
	protected $context;
	protected $mediaBase;

	public function __construct($name = false, $basePath, $context = 'joomla') {
		$this->name = $name;
		$this->context = $context;
		$absolute = str_replace(DIRECTORY_SEPARATOR, '/', $basePath);
		$this->mediaBase = str_replace(DIRECTORY_SEPARATOR, '/', COM_MEDIA_BASE);
		$relative = str_replace($this->mediaBase, '', $absolute);
		$this->data = (object) array('name' => $name, 'context' => $this->context, 'relative' => $relative, 'absolute' => $absolute);
		$this->tree = $this->fillArrayWithFileNodes(new DirectoryIterator( $absolute ));
	}

	public function addChildren($name, $child) {
		if ($child instanceof JFolderTree)
		{
			$this->tree[$name]= $child->getTreeArray();
		}
		else
		{
			throw new Exception(JText::_('COM_MEDIA_ERROR_FOLDER_TREE_OBJECT_EXCEPTION'));
		}
	}



	public function getTreeArray() {
		if ($this->name)
		{
			return array(
				$this->name => array(
					'children' => $this->tree,
					'data' => $this->data
				)
			);
		}
		else
		{
			return array(
				'children' => $this->tree,
				'data' => $this->data
			);
		}
	}

	private function fillArrayWithFileNodes(DirectoryIterator $dir) {
		$data = array();
		foreach ( $dir as $node )
		{
			if ( $node->isDir() && !$node->isDot() )
			{
				$absolute = str_replace(DIRECTORY_SEPARATOR, '/', $node->getPathname());
				$relative = str_replace($this->mediaBase . '/', '', $absolute);
				$data[$node->getFilename()]['children'] = $this->fillArrayWithFileNodes( new DirectoryIterator( $node->getPathname() ) );
				$data[$node->getFilename()]['data'] = (object) array('name' => $node->getFilename(), 'context' => $this->context, 'relative' => $relative, 'absolute' => $absolute);
			}
		}
		return $data;
	}
}