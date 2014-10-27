<?php

/**
* @package     Joomla.Plugin
* @subpackage  Media.azure
*
* @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
* @license     GNU General Public License version 2 or later; see LICENSE.txt
*/

defined('_JEXEC') or die;

jimport('joomla.plugin.plugin');

JLoader::register('JAzure', JPATH_ROOT . '/plugins/media/azure/library/jazure.php');

/**
 * Azure Cloud Plugin
 *
 * @package     Joomla.Plugin
 * @subpackage  Media.azure
 * @since       3.3
 */
class PlgMediaAzure extends JPlugin
{

	const CONTEXT = 'com_media.azure';
	const NAME = 'azure';

	protected $azure = false;

	public function __construct(&$subject, $config)
	{
		parent::__construct($subject, $config);

		if ($this->params->get('azure_enabled', 0)) {
			JFactory::getLanguage()->load('plg_media_azure');
			$endpoint = $this->params->get('azure_default_endpoint', null);
			$name = $this->params->get('azure_account_name', null);
			$key = $this->params->get('azure_account_key', null);
			$environment_variable = $this->params->get('azure_use_environment_variable', null);
			$this->azure = JAzure::getInstance($endpoint, $name, $key, $environment_variable);
		}
	}

	public function onMediaUploadFile($context, &$object_file, $folder, &$response) {

		if ($context === self::CONTEXT) {

			$content = fopen($object_file->tmp_name, 'r');

			$data = array(
				"content_type" => $object_file->type,
				"content_language" => "",
				"content_encoding" => "",
				"content_mD5" => "",
				"cache_control" => "",
				"sequence_number" => ""
			);

			if ($this->azure->createBlockBlob($folder, $object_file->name, $content, $data, $response)) {
				return false;
			}
		}

		return true;
	}

	public function onMediaDeleteFile($context, &$paths, &$response) {

		if ($context === self::CONTEXT) {
			$this->azure->deleteBlob(strtolower($folder));
		}

	}

	public function onMediaCreateFolder($context, $parent, $folder, $folderCheck, &$response) {

		if ($context === self::CONTEXT) {
			$result = $this->azure->createContainer(strtolower($folder));
		}

	}

	public function onMediaDeleteFolder($context, $folder, $path, &$response) {

		if ($context === self::CONTEXT) {
			$this->azure->deleteContainer(strtolower($path));
		}

	}

	public function onMediaGetFolderList(&$groups, $base, &$response) {

		$tmp = array();
		// Get some paths from the request
		if (empty($base))
		{
				$base = COM_MEDIA_BASE;
		}
		//corrections for windows paths
		$base = str_replace(DIRECTORY_SEPARATOR, '/', $base);
		$com_media_base_uni = str_replace(DIRECTORY_SEPARATOR, '/', COM_MEDIA_BASE);

		// Get the list of folders
		jimport('joomla.filesystem.folder');
		$folders = JFolder::folders($base, '.', true, true);

		$document = JFactory::getDocument();
		$document->setTitle(JText::_('COM_MEDIA_INSERT_IMAGE'));
		$response = new stdClass();
		$response->message = false;
		if ($this->params->get('azure_enabled', 0)) {
			$data = new stdClass();
			$data->name = 'Azure';
			$data->relative = '';
			$data->absolute = '';
			$tmp[] = JHtml::_('select.option', "", "/", 'value', 'text', false);
			$containers = $this->azure->listContainers();
			$account_name = $this->params->get('azure_default_name');

			foreach ($containers as $container) {
				$value = $container['name'];
				$tmp[] = JHtml::_('select.option', $value, $container['name'], 'value', 'text', false);
			}

			// Sort the folder list array
			//if (is_array($options))
			//{
					//sort($options);
			//}
		}


		$groups[self::NAME] = array(
			'id' => self::NAME,
			'label' => JText::_('PLG_MEDIA_AZURE'),
			'items' => $tmp
		);

		return true;

	}


	public function onMediaGetList($context, &$list, $current, &$response) {
			if (JFactory::getApplication()->input->get('view') == 'imagesList') {
					$mediaList = false;
			} else {
					$mediaList = true;
			}

		if ($context === self::CONTEXT)
		{
			if ($this->azure)
			{
				if (empty($current))
				{
					$iterateList = $this->azure->listContainers();
					$list = $this->buildFolderListObjects($iterateList);
					if ($mediaList)
					{
						JFactory::getDocument()->addScriptDeclaration("
							window.addEvent('domready', function()
							{
								var el = window.parent.document.getElementById('toolbar-new');
								var button = el.firstElementChild || elem.firstChild;
								button.disabled = 0;
							});"
						);
					}
				}
				else
				{
					$iterateList = $this->azure->listBlobs($current);
					$list = $this->buildFileListObjects($iterateList);
					if ($mediaList)
					{
						JFactory::getDocument()->addScriptDeclaration("
							window.addEvent('domready', function()
							{
								var el = window.parent.document.getElementById('toolbar-new');
								var button = el.firstElementChild || elem.firstChild;
								button.disabled = 1;
							});"
						);
					}
				}
			}
		} else {
			if ($mediaList)
			{
				JFactory::getDocument()->addScriptDeclaration("
					window.addEvent('domready', function()
					{
						var el = window.parent.document.getElementById('toolbar-new');
						var button = el.firstElementChild || elem.firstChild;
						button.disabled = 0;
					});"
				);
			}
		}
		return true;
	}


	public function onMediaGetFolderTree(&$tree, &$response) {

		if ($this->azure)
		{
			$baseUrl = $this->azure->getBaseUrl();
			$containers = $this->azure->listContainers();

			$children = array();
			$tmp = &$children;
			foreach ($containers as $container) {

					$folder		= $container['name'];
					$name		= $container['name'];
					$relative	= str_replace($baseUrl, '', $container['url']);
					$absolute	= $container['url'];
					//$path		= explode('/', $relative);
					$node		= (object) array('name' => $name, 'context' => self::NAME, 'relative' => $relative, 'absolute' => $absolute);
					$tmp['children'][$relative] = array('data' => $node, 'children' => array());
			}

			$children['data'] = (object) array('name' => JText::_('PLG_MEDIA_AZURE'), 'context' => self::NAME, 'relative' => '', 'absolute' => $baseUrl);
			array_push($tree['children'], $children);
		}

		return true;
	}

	public function onMediaPrepareFileForm($context, &$form, &$response)
	{
		if (!($form instanceof JForm))
		{
			return false;
		}

		if ($context === self::CONTEXT)
		{
			JForm::addFormPath(JPATH_ROOT . '/plugins/media/azure/forms');
			$form->loadFile('uploadblob', false);
		}

		return true;
	}

	public function onMediaPrepareFolderForm($context, &$form, &$response)
	{
		if (!($form instanceof JForm))
		{
			return false;
		}

		if ($context === self::CONTEXT)
		{
			JForm::addFormPath(JPATH_ROOT . '/plugins/media/azure/forms');
			$form->loadFile('uploadcontainer', false);
		}

		return true;
	}

	private function buildFolderListObjects($objects) {
		$folders = array();

		foreach ($objects as $item)
		{
				$tmp = new JObject();
				$tmp->name = $item['name'];
				$tmp->path = $item['url'];
				$tmp->context = self::NAME;
				$tmp->path_relative = $item['name'];
				$tmp->files = 0;
				$tmp->folders = 0;
				$folders[] = $tmp;
		}

		return array('folders' => $folders, 'docs' => array(), 'images' => array());
	}

	private function buildFileListObjects($objects) {
			$docs = array();
			$images = array();

			if ($objects)
			{
				foreach ($objects as $item)
				{

						$tmp = new JObject;
						$tmp->name = $item['name'];
						$tmp->title = $item['name'];
						$tmp->path = $item['url'];
						$tmp->context = self::NAME;
						$tmp->path_relative = false;
						$tmp->path_absolute = $item['url'];
						$tmp->size = $item['size'];
						$parts = explode('/', $item['content_type']);

						if ($parts[0] == 'image') {
							$ext = $parts[1];
						} else {
							$ext = $this->getApplicationContentTypeExtension($parts[1], $tmp->name);
						}

						switch ($ext)
						{
								// Image
								case 'jpg':
								case 'png':
								case 'gif':
								case 'xcf':
								case 'odg':
								case 'bmp':
								case 'jpeg':
								case 'ico':
										$info = @getimagesize($tmp->path_absolute);
										$tmp->width		= @$info[0];
										$tmp->height	= @$info[1];
										$tmp->type		= @$info[2];
										$tmp->mime		= @$info['mime'];

										if (($info[0] > 60) || ($info[1] > 60))
										{
												$dimensions = MediaHelper::imageResize($info[0], $info[1], 60);
												$tmp->width_60 = $dimensions[0];
												$tmp->height_60 = $dimensions[1];
										}
										else
										{
												$tmp->width_60 = $tmp->width;
												$tmp->height_60 = $tmp->height;
										}

										if (($info[0] > 16) || ($info[1] > 16))
										{
												$dimensions = MediaHelper::imageResize($info[0], $info[1], 16);
												$tmp->width_16 = $dimensions[0];
												$tmp->height_16 = $dimensions[1];
										}
										else
										{
												$tmp->width_16 = $tmp->width;
												$tmp->height_16 = $tmp->height;
										}

										$images[] = $tmp;
										break;

										// Non-image document
								default:
										$tmp->icon_32 = "media/mime-icon-32/".$ext.".png";
										$tmp->icon_16 = "media/mime-icon-16/".$ext.".png";
										$docs[] = $tmp;
										break;
						}
				}
			}
			return array('folders' => array(), 'docs' => $docs, 'images' => $images);

	}

	private function getApplicationContentTypeExtension($type, $name) {
			switch ($type)
			{
					case 'x-zip' :
						return 'zip';
					break;
					case 'plain' :
						return 'js';
					break;
					default :
						return 'doc';
			}
	}

}