<?php

/**
* @package     Joomla.Plugin
* @subpackage  Media.joomla
*
* @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
* @license     GNU General Public License version 2 or later; see LICENSE.txt
*/

defined('_JEXEC') or die;

jimport('joomla.filesystem.folder');

/**
 * Joomla Media Plugin
 *
 * @package     Joomla.Plugin
 * @subpackage  Media.joomla
 * @since       3.3
 */
class PlgMediaJoomla extends JPlugin
{
	const CONTEXT = 'joomla';

	public function onMediaGetList(&$list, $context, $current, &$response) {
		if ($context === self::CONTEXT)
		{
			// If undefined, set to empty
			if ($current == 'undefined')
			{
				$current = '';
			}

			if (strlen($current) > 0)
			{
				$basePath = COM_MEDIA_BASE.'/'.$current;
			}
			else
			{
				$basePath = COM_MEDIA_BASE;
			}

			$mediaBase = str_replace(DIRECTORY_SEPARATOR, '/', COM_MEDIA_BASE.'/');

			$images		= array ();
			$folders	= array ();
			$docs		= array ();

			$fileList = false;
			$folderList = false;
			if (file_exists($basePath))
			{
				// Get the list of files and folders from the given folder
				$fileList	= JFolder::files($basePath);
				$folderList = JFolder::folders($basePath);
			}

			// Iterate over the files if they exist
			if ($fileList !== false)
			{
				foreach ($fileList as $file)
				{
					if (is_file($basePath.'/'.$file) && substr($file, 0, 1) != '.' && strtolower($file) !== 'index.html')
					{
						$tmp = new JObject;
						$tmp->name = $file;
						$tmp->title = $file;
						$tmp->path = str_replace(DIRECTORY_SEPARATOR, '/', JPath::clean($basePath . '/' . $file));
						$tmp->path_relative = str_replace($mediaBase, '', $tmp->path);
						$tmp->size = filesize($tmp->path);

						$ext = strtolower(JFile::getExt($file));
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
								$info = @getimagesize($tmp->path);
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
			}

			// Iterate over the folders if they exist
			if ($folderList !== false)
			{
					foreach ($folderList as $folder)
					{
							$tmp = new JObject;
							$tmp->name = basename($folder);
							$tmp->path = str_replace(DIRECTORY_SEPARATOR, '/', JPath::clean($basePath . '/' . $folder));
							$tmp->path_relative = str_replace($mediaBase, '', $tmp->path);
							$count = MediaHelper::countFiles($tmp->path);
							$tmp->files = $count[0];
							$tmp->folders = $count[1];

							$folders[] = $tmp;
					}
			}

			$list = array('folders' => $folders, 'docs' => $docs, 'images' => $images);

		}

		return true;
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

				// Build the array of select options for the folder list
			//$options[] = JHtml::_('select.option', urldecode($base), 'Joomla Media');
			$options[] = JHtml::_('select.option', "", "/");

				foreach ($folders as $folder)
				{
						$folder		= str_replace($com_media_base_uni, "", str_replace(DIRECTORY_SEPARATOR, '/', $folder));
						$value		= substr($folder, 1);
						$text		= str_replace(DIRECTORY_SEPARATOR, "/", $folder);
						$options[]	= JHtml::_('select.option', $value, $text);
				}

			// Sort the folder list array
			if (is_array($options))
			{
					sort($options);
			}

			return $options;
			// Create the drop-down folder select list
			//$list = JHtml::_('select.genericlist', $options, 'folderlist', 'size="1" onchange="ImageManager.setFolder(this.options[this.selectedIndex].value, '.$asset.', '.$author.')" ', 'value', 'text', $base);

	}

	public function onMediaGetFolderTree(&$tree, $base, &$response) {
			JFactory::getLanguage()->load('plg_media_joomla');

			// Get some paths from the request
			if (empty($base))
			{
				$base = COM_MEDIA_BASE;
			}

			$mediaBase = str_replace(DIRECTORY_SEPARATOR, '/', COM_MEDIA_BASE.'/');

			// Get the list of folders
			jimport('joomla.filesystem.folder');
			$folders = JFolder::folders($base, '.', true, true);

			$children = array();
			$tmp = &$children;
			foreach ($folders as $folder)
			{
				$folder		= str_replace(DIRECTORY_SEPARATOR, '/', $folder);
				$name		= substr($folder, strrpos($folder, '/') + 1);
				$relative	= str_replace($mediaBase, '', $folder);
				$absolute	= $folder;
				$path		= explode('/', $relative);
				$node		= (object) array('name' => $name, 'context' => self::CONTEXT, 'relative' => $relative, 'absolute' => $absolute);

				for ($i = 0, $n = count($path); $i < $n; $i++)
				{
					if (!isset($tmp['children']))
					{
						$tmp['children'] = array();
					}

					if ($i == $n - 1)
					{
						// We need to place the node
						$tmp['children'][$relative] = array('data' => $node, 'children' => array());
						break;
					}

					if (array_key_exists($key = implode('/', array_slice($path, 0, $i + 1)), $tmp['children']))
					{
						$tmp = &$tmp['children'][$key];
					}
				}
			}

			$children['data'] = (object) array('name' => JText::_('PLG_MEDIA_JOOMLA'), 'context' => self::CONTEXT, 'relative' => '', 'absolute' => $base);
			$tree['children'][self::CONTEXT] = $children;

			return true;
	}
}