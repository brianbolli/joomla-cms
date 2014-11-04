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

		if ($this->params->get('azure_enabled', 0))
		{
			JFactory::getLanguage()->load('plg_media_azure');
			$endpoint = $this->params->get('azure_default_endpoint', null);
			$name = $this->params->get('azure_account_name', null);
			$key = $this->params->get('azure_account_key', null);
			$environment_variable = $this->params->get('azure_use_environment_variable', null);
			$this->azure = JAzure::getInstance($endpoint, $name, $key, $environment_variable);
		}
	}

	public function onMediaUploadFile($context, &$object_file, $folder, &$response)
	{

		if ($context === self::CONTEXT)
		{

			$content = fopen($object_file->tmp_name, 'r');

			$data = array(
				"content_type" => $object_file->type,
				"content_language" => "",
				"content_encoding" => "",
				"content_mD5" => "",
				"cache_control" => !empty($data['cache_control']) ? 'public, max-age=' . $data['cache_control'] : '',
				"sequence_number" => ""
			);

			if (!$this->azure->createBlockBlob($folder, $object_file->name, $content, $data, $response)) {
				return false;
			}
		}

		return true;
	}

	public function onMediaUpdateFile($context, $data, $folder, &$response)
	{
			error_log(json_encode($data));
			error_log($folder);
		if ($context === self::CONTEXT)
		{
			$config = JFactory::getConfig();
			$timezone = new DateTimeZone($config->get('offset'));

			$options = array(
				"content_type" => $data['content_type'],
				"content_language" => "",
				"content_encoding" => "",
				"content_mD5" => "",
				"cache_control" => !empty($data['cache_control']) ? 'public, max-age=' . $data['cache_control'] : '',
				"last_modified" => new DateTime(date("Y-m-d H:i:s", time()), $timezone),
				"sequence_number" => ""
			);


			if (!$this->azure->setBlobProperties($folder, $data['blob_name'], $options)) {
				return false;
			}
		}

		return true;
	}

	public function onMediaDeleteFile($context, $object_file, $folder, &$response)
	{
		if ($context === self::CONTEXT)
		{

			// Azure blobs use URLs, therefor no need to compensate for directory seperator specific syntax
			$pos = strrpos($object_file->filepath, '/');
			$blob = substr($object_file->filepath, $pos + 1, strlen($object_file->filepath));
			$this->azure->deleteBlob(strtolower($folder), $blob);
		}
		return true;
	}

	public function onMediaUpdateFolder($context, $data, $folder, &$response)
	{
		if ($context === self::CONTEXT)
		{
			$result = $this->azure->setContainerAcl(strtolower($data['foldername']), $data['access_type']);
		}
		return true;
	}

	public function onMediaCreateFolder($context, $parent, $folder, $data, &$response)
	{

		if ($context === self::CONTEXT) {
			$result = $this->azure->createContainer(strtolower($folder), $data['access_type']);
		}
		return true;
	}

	public function onMediaDeleteFolder($context, $folder, $folderpath, &$response) {
		if ($context === self::CONTEXT) {

			// confirm Azure container is empty by retrieving blobs
			$blobs = $this->azure->listBlobs($folder);

			if (empty($blobs))
			{
				$this->azure->deleteContainer(strtolower($folder));
			}
			else
			{
				$response->message = JText::_('PLG_MEDIA_AZURE_ERROR_CANNOT_DELETE_CONTAINER_WITH_BLOBS');
				$response->type = 'Warning';
				return false;
			}
		}
		return true;
	}

	public function onMediaGetFolderList(&$groups, $base, &$response, $images = false) {

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
		if ((int) $this->params->get('azure_enabled', 0))
		{
			$data = new stdClass();
			$data->name = 'Azure';
			$data->relative = '';
			$data->absolute = '';
			$tmp[] = JHtml::_('select.option', "", "/", 'value', 'text', false);
			$containers = $this->azure->listContainers();
			$account_name = $this->params->get('azure_default_name');

			foreach ($containers as $container)
			{
				if ($images === true)
				{
					if ($container['public_access'] == 'blob' || $container['public_access'] == 'container' && !is_null($container['public_access']))
					{
						$value = $container['name'];
						$tmp[] = JHtml::_('select.option', $value, $container['name'], 'value', 'text', false);
					}
				} else {
					$value = $container['name'];
					$tmp[] = JHtml::_('select.option', $value, $container['name'], 'value', 'text', false);
				}
			}
		}


		$groups[self::NAME] = array(
			'id' => self::NAME,
			'label' => JText::_('PLG_MEDIA_AZURE'),
			'items' => $tmp
		);

		return true;

	}


	public function onMediaGetList($context, &$list, $current, &$response) {
		if (JFactory::getApplication()->input->get('view') == 'imagesList')
		{
			$imageList = true;
		} else {
			$imageList = false;
		}

		if ($context === self::CONTEXT)
		{
			if ($this->azure)
			{
				if (empty($current))
				{
					$iterateList = $this->azure->listContainers();
					$list = $this->buildFolderListObjects($iterateList);
					if (!$imageList)
					{
						JFactory::getDocument()->addScriptDeclaration("
							window.addEvent('domready', function()
							{
								var collapse = window.parent.document.getElementById('collapseUpload');

								var el1 = window.parent.document.getElementById('toolbar-new');
								var button1 = el1.firstElementChild || el1.firstChild;
								button1.disabled = 0;

								var el2 = window.parent.document.getElementById('toolbar-upload');
								var button2 = el2.firstElementChild || el2.firstChild;

								if (jQuery(collapse).hasClass('in'))
								{
									jQuery(button2).click();
								}
								button2.disabled = 1;
							});"
						);
					}
				}
				else
				{
					$iterateList = $this->azure->listBlobs($current);
					$list = $this->buildFileListObjects($iterateList, $imageList);
					if (!$imageList)
					{
						JFactory::getDocument()->addScriptDeclaration("
							window.addEvent('domready', function()
							{
								var collapse = window.parent.document.getElementById('collapseFolder');

								var el1 = window.parent.document.getElementById('toolbar-new');
								var button1 = el1.firstElementChild || el1.firstChild;

								if (jQuery(collapse).hasClass('in'))
								{
									jQuery(button1).click();
								}

								button1.disabled = 1;

								var el2 = window.parent.document.getElementById('toolbar-upload');
								var button2 = el2.firstElementChild || el2.firstChild;
								button2.disabled = 0;
							});"
						);
					}
				}

			}
		} else {
			if (!$imageList)
			{
				JFactory::getDocument()->addScriptDeclaration("
					window.addEvent('domready', function()
					{
						var el1 = window.parent.document.getElementById('toolbar-new');
						var button1 = el1.firstElementChild || el1.firstChild;
						button1.disabled = 0;
						var el2 = window.parent.document.getElementById('toolbar-upload');
						var button2 = el2.firstElementChild || el2.firstChild;
						button2.disabled = 0;
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
			foreach ($containers as $container)
			{

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

	public function onMediaContentImageForm($context, &$form, &$response)
	{
		if (!($form instanceof JForm))
		{
			return false;
		}

		if ($context === self::CONTEXT)
		{
			if ((int) $this->params->get('azure_cdn_enabled', 0) && (int) $this->params->get('azure_cdn_query_string', 0))
			{
				JForm::addFormPath(JPATH_ROOT . '/plugins/media/azure/forms');
				$form->load('contentimage', false);
			}
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

	private function processBlobUrl($url)
	{
		if ((int) $this->params->get('azure_cdn_enabled', 0, 'int') === 1) {
			$cdn_url = $this->params->get('azure_cdn_url');
			$name = $this->params->get('azure_account_name');
			$endpoint = $this->params->get('azure_default_endpoint');
			$azure_url = $this->buildAzureStorageUrl($endpoint, $name);
			return str_replace($azure_url, $cdn_url, $url);
		} else {
			return $url;
		}
	}


	private function buildAzureStorageUrl($endpoint, $name) {
		return $endpoint . '://' . $name . '.blob.core.windows.net/';
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
				$tmp->properties = json_encode(array('foldername' => $item['name'], 'access_type' => $item['public_access']), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);
				$folders[] = $tmp;
		}

		return array('folders' => $folders, 'docs' => array(), 'images' => array());
	}

	private function buildFileListObjects($objects, $imageList = false)
	{
		$docs = array();
		$images = array();

		if ($objects)
		{
			foreach ($objects as $item)
			{
				$parts = explode("/", $item['content_type']);

				if (is_null($item['cache_control']))
				{
					$cache_control = '';
				}
				else
				{
					$cache_control = preg_replace("/[^0-9]/", "", $item['cache_control']);
				}

				$tmp = new JObject;
				$tmp->name = $item['name'];
				$tmp->title = $item['name'];
				$tmp->path = $item['url'];
				$tmp->context = self::NAME;
				$tmp->path_relative = false;
				$tmp->path_absolute = $this->processBlobUrl($item['url']);
				$tmp->size = $item['size'];
				$cache_control =  empty($cache_control) ? '' : $cache_control;
				$tmp->properties = json_encode(array('blob_name' => $item['name'], 'content_type' => $item['content_type'], 'cache_control' => $cache_control), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);
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
						if (!$imageList)
							{
							$tmp->icon_32 = "media/mime-icon-32/".$ext.".png";
							$tmp->icon_16 = "media/mime-icon-16/".$ext.".png";
							$docs[] = $tmp;
						}
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