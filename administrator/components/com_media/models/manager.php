<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_media
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Media Component Manager Model
 *
 * @package     Joomla.Administrator
 * @subpackage  com_media
 * @since       1.5
 */
class MediaModelManager extends JModelLegacy
{
	public function getState($property = null, $default = null)
	{
		static $set;

		if (!$set)
		{
			$input = JFactory::getApplication()->input;

			$folder = $input->get('folder', '', 'path');
			$this->setState('folder', $folder);

			$context = $input->get('context', 'joomla', 'string');
			$this->setState('context', $context);

			$fieldid = $input->get('fieldid', '');
			$this->setState('field.id', $fieldid);

			$parent = str_replace("\\", "/", dirname($folder));
			$parent = ($parent == '.') ? null : $parent;
			$this->setState('parent', $parent);
			$set = true;
		}

		return parent::getState($property, $default);
	}

	function getFolderList($base = null)
	{
		$response = new stdClass();
		$response->message = false;
		$response->type = false;

		$groups = array();

		$dispatcher = JDispatcher::getInstance();
		JPluginHelper::importPlugin('media');
		$dispatcher->trigger('onMediaGetFolderlist', array(&$groups, $base, &$response));

		// Get asset and author id (use integer filter)
		$input = JFactory::getApplication()->input;
		$asset = $input->get('asset', 0, 'integer');

		// For new items the asset is a string. JAccess always checks type first
		// so both string and integer are supported.
		if ($asset == 0)
		{
			$asset = $input->get('asset', 0, 'string');
		}

		$author = $input->get('author', 0, 'integer');

		$value = $input->get('folder', 'banners'. 'string');

		// Create the drop-down folder select list
		$list = JHtml::_('select.groupedlist', $groups, 'folderlist',
			array(
				'list.attr' => 'size="1" data-asset="' . $asset . '" data-author="' . $author . '"',
				'id' => 'folderlist',
				'list.select' => $value,
				'group.id' => 'id',
				'group.label' => 'label',
				'group.items' => 'items',
				'option.key.toHtml' => false,
				'option.text.toHtml' => false
			)
		);

		return $list;
	}

	/**
	 *
	 * @param string $base
	 * @return multitype:multitype: StdClass
	 */
	function getFolderTree($base = null) {
		$response = new stdClass();
		$response->message = false;
		$response->type = false;

		$tree = array();
		$tree['children'] = array();

		$dispatcher = JDispatcher::getInstance();
		JPluginHelper::importPlugin('media');
		$dispatcher->trigger('onMediaGetFolderTree', array(&$tree, &$response));

		$tree['data'] = (object) array('name' => JText::_('COM_MEDIA_MEDIA'), 'context' => '', 'subfolders' => true, 'relative' => '', 'absolute' => COM_MEDIA_BASE);

		return $tree;
	}

	function getForm() {
		$context = JFactory::getApplication()->input->get('context', 'joomla', 'string');
		$response = new stdClass();
		$response->message = false;
		$response->type = false;

		JFormHelper::addformPath(JPATH_COMPONENT_ADMINISTRATOR . '/models/forms/');
		$form = new JForm('uploadmedia');
		$form->loadFile('uploadmedia');

		$dispatcher = JDispatcher::getInstance();
		JPluginHelper::importPlugin('media');
		$dispatcher->trigger('onMediaPrepareForm', array($context, &$tree, &$response));

		return $form;
	}
}
