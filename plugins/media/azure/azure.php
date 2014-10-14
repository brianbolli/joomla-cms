<?php

/**
* @package     Joomla.Plugin
* @subpackage  Media.azure
*
* @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
* @license     GNU General Public License version 2 or later; see LICENSE.txt
*/

defined('_JEXEC') or die;

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

	const CONTEXT = 'azure';

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

	public function onMediaUploadFile($file, $context, &$response) {

		if ($context === self::CONTEXT) {
			#TODO - figure this one out
			$sream = file_stream_me($file);
			$this->azure->createBlockBlob($context, $file, $stream);
		}

	}

	public function onMediaDeleteFile($file, $context, &$response) {

		if ($context === self::CONTEXT) {
			$this->azure->deleteBlob(strtolower($folder));
		}

	}

	public function onMediaCreateFolder($context, $folder, $parent, &$response) {

		if ($context === self::CONTEXT) {
			$result = $this->azure->createContainer(strtolower($folder));
			var_dump($result);
		}

	}

	public function onMediaDeleteFolder($context, $folder, $path, &$response) {

		if ($context === self::CONTEXT) {
			$this->azure->deleteContainer(strtolower($folder));
		}

	}

	public function onMediaGetFolderList(&$options, $base, &$response) {
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
				$options[] = JHtml::_('select.option', "Azure", "/");
				$containers = $this->azure->listContainers();
				$account_name = $this->params->get('azure_default_name');

				foreach ($containers as $container) {
					$value = $account_name . '.' . $container['name'];
						$options[] = JHtml::_('select.option', $value, $container['name']);
				}

				// Sort the folder list array
				//if (is_array($options))
				//{
						//sort($options);
				//}
			}

			return true;

	}


	public function onMediaGetList(&$list, $context, $current, &$response) {

		if ($context === self::CONTEXT)
		{
			if ($this->azure)
			{
				if (empty($current))
				{
					$iterateList = $this->azure->listContainers();
					$list = $this->buildFolderListObjects($iterateList);
					JFactory::getDocument()->addScriptDeclaration("
						window.addEvent('domready', function()
						{
							var el = window.parent.document.getElementById('toolbar-new');
							var button = el.firstElementChild || elem.firstChild;
							button.disabled = 0;
						});"
					);
				}
				else
				{
					$iterateList = $this->azure->listBlobs($current);
					$list = $this->buildFileListObjects($iterateList);
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
		} else {
			JFactory::getDocument()->addScriptDeclaration("
				window.addEvent('domready', function()
				{
					var el = window.parent.document.getElementById('toolbar-new');
					var button = el.firstElementChild || elem.firstChild;
					button.disabled = 0;
				});"
			);
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
				$tmp->context = self::CONTEXT;
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

			foreach ($objects as $item)
			{

					$tmp = new JObject;
					$tmp->name = $item['name'];
					$tmp->title = $item['name'];
					$tmp->path = $item['url'];
					$tmp->context = self::CONTEXT;
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
				$node		= (object) array('name' => $name, 'context' => self::CONTEXT, 'relative' => $relative, 'absolute' => $absolute);
				$tmp['children'][$relative] = array('data' => $node, 'children' => array());
			}

			$children['data'] = (object) array('name' => JText::_('PLG_MEDIA_AZURE'), 'context' => self::CONTEXT, 'relative' => '', 'absolute' => $baseUrl);
			array_push($tree['children'], $children);

		}

		return true;
	}
}