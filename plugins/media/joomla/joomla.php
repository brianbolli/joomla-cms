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
	const CONTEXT = 'com_media.joomla';
	const NAME = 'joomla';

	public function onMediaUploadFile($context, &$object_file, $folder, &$response) {

		$user = JFactory::getUser();

		if ($context === self::CONTEXT)
		{
			// Instantiate the media helper
			$mediaHelper = new JHelperMedia;
			JLog::addLogger(array('text_file' => 'upload.error.php'), JLog::ALL, array('upload'));

			// Set FTP credentials, if given
			JClientHelper::setCredentialsFromRequest('ftp');


			if (isset($object_file->name))
			{
				// The request is valid
				$err = null;

				if (!MediaHelper::canUpload((array) $object_file, $err))
				{
					JLog::add('Invalid: ' . $object_file->filepath . ': ' . $err, JLog::INFO, 'upload');

					$response->message = JText::_($err);
					$response->type = 'Warning';
					return false;
				}

				if (JFile::exists($object_file->filepath))
				{
					// File exists
					JLog::add('File exists: ' . $object_file->filepath . ' by user_id ' . $user->id, JLog::INFO, 'upload');

					$response->message = JText::_('PLG_MEDIA_JOOMLA_ERROR_FILE_EXISTS');
					$response->type = 'Warning';
					return false;
				}
				elseif (!$user->authorise('core.create', 'com_media'))
				{
					// File does not exist and user is not authorised to create
					JLog::add('Create not permitted: ' . $object_file->filepath . ' by user_id ' . $user->id, JLog::INFO, 'upload');

					$response->message = JText::_('PLG_MEDIA_JOOMLA_ERROR_CREATE_NOT_PERMITTED');
					$response->type = 'Warning';
					return false;
				}

				if (!JFile::upload($object_file->tmp_name, $object_file->filepath))
				{
					// Error in upload
					JLog::add('Error on upload: ' . $object_file->filepath, JLog::INFO, 'upload');

					$response->message = JText::_('PLG_MEDIA_JOOMLA_ERROR_UNABLE_TO_UPLOAD_FILE');
					$response->type = 'Warning';
					return false;
				}
			}
			else
			{
				$response->message = JText::_('PLG_MEDIA_JOOMLA_ERROR_BAD_REQUEST');
				$response->type = 'Warning';
				return false;
			}
		}
	}

	public function onMediaCreateFolder($context, $parent, $folder, $data, &$response) {

		$folderCheck = $data['foldername'];

		$app = JFactory::getApplication();

		if ($context === self::CONTEXT)
		{
			JFactory::getLanguage()->load('plg_media_joomla');

			// Set FTP credentials, if given
			JClientHelper::setCredentialsFromRequest('ftp');

			$app->input->set('folder', $parent);

			if (($folderCheck !== null) && ($folder !== $folderCheck))
			{
				$app = JFactory::getApplication();
				$response->message = JText::_('PLG_MEDIA_JOOMLA_ERROR_UNABLE_TO_CREATE_FOLDER_WARNDIRNAME');
				$response->type = 'Warning';

				return false;
			}

			$path = JPath::clean(COM_MEDIA_BASE . '/' . $parent . '/' . $folder);

			if (!is_dir($path) && !is_file($path))
			{
				// Trigger the onContentBeforeSave event.
				$object_file = new JObject(array('filepath' => $path));
				JPluginHelper::importPlugin('content');
				$dispatcher	= JEventDispatcher::getInstance();
				$result = $dispatcher->trigger('onContentBeforeSave', array('com_media.folder', &$object_file, true));

				if (in_array(false, $result, true))
				{

					// There are some errors in the plugins
					$response->message = JText::plural('PLG_MEDIA_JOOMLA_ERROR_BEFORE_SAVE', count($errors = $object_file->getErrors()), implode('<br />', $errors));
					$response->type = 'Warning';

					return false;
				}

				if (JFolder::create($object_file->filepath))
				{
					$data = "<html>\n<body bgcolor=\"#FFFFFF\">\n</body>\n</html>";
					JFile::write($object_file->filepath . "/index.html", $data);

					// Trigger the onContentAfterSave event.
					$dispatcher->trigger('onContentAfterSave', array('com_media.folder', &$object_file, true));
					$response->message = JText::sprintf('PLG_MEDIA_JOOMLA_CREATE_COMPLETE', JText::_('PLG_MEDIA_JOOMLA'), $folder);
					$response->type = 'Message';
				}
			}
		}

		return true;
	}


	public function onMediaDeleteFile($context, $object_file, $folder, &$response) {

		if ($context === self::CONTEXT)
		{

			$pos = strrpos($object_file->filepath, DIRECTORY_SEPARATOR);
			$filename = substr($object_file->filepath, $pos + 1, strlen($object_file->filepath));
			if ($filename !== JFile::makeSafe($filename))
			{
				// filename is not safe
				$filename = htmlspecialchars($path, ENT_COMPAT, 'UTF-8');
				$response->message = JText::sprintf('COM_MEDIA_ERROR_UNABLE_TO_DELETE_FILE_WARNFILENAME', substr($filename, strlen(COM_MEDIA_BASE)));
				$response->type = 'Warning';
				return false;
			}

			if (!JFile::delete($object_file->filepath)) {
				return false;
			}
		}

		return true;
	}

	public function onMediaDeleteFolder($context, $folderpath, $folder, &$response) {

		if ($context === self::CONTEXT)
		{
			$deletePath = COM_MEDIA_BASE . '/';
			if (!empty($folderpath)) {
				$deletePath .= $folderpath . '/';
			}
			$deletePath .=  $folder;

			if (is_dir($deletePath))
			{
				$contents = JFolder::files($deletePath, '.', true, false, array('.svn', 'CVS', '.DS_Store', '__MACOSX', 'index.html'));

				if (empty($contents))
				{
					if (!JFolder::delete($deletePath))
					{
						$response->message = JText::sprintf('COM_MEDIA_ERROR_UNABLE_TO_DELETE_FOLDER_NOT_EMPTY', substr($deletePath, strlen(COM_MEDIA_BASE)));
						$response->type = 'Warning';
						return false;
					}
				}
				else
				{
					$response->message = JText::sprintf('COM_MEDIA_ERROR_UNABLE_TO_DELETE_FOLDER_NOT_EMPTY', substr($deletePath, strlen(COM_MEDIA_BASE)));
					$response->type = 'Warning';
					return false;
				}
			}
			else
			{
				$response->message = JText::sprintf('COM_MEDIA_ERROR_UNABLE_TO_DELETE_FOLDER_NOT_EMPTY', substr($deletePath, strlen(COM_MEDIA_BASE)));
				$response->type = 'Warning';
				return false;
			}
		}

		return true;
	}

	public function onMediaGetList($context, &$list, $current, &$response) {
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
						$tmp->context = self::NAME;

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
							$tmp->context = self::NAME;
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

	public function onMediaGetFolderList(&$groups, $base, &$response) {
		JFactory::getLanguage()->load('plg_media_joomla');

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
		$document->setTitle(JText::_('PLG_MEDIA_JOOMLA_INSERT_IMAGE'));

			// Build the array of select options for the folder list
		//$options[] = JHtml::_('select.option', urldecode($base), 'Joomla Media');
		$tmp[] = JHtml::_('select.option', "", "/", 'value', 'text', false, ' name="' . self::NAME . '"');

		foreach ($folders as $folder)
		{
			$folder		= str_replace($com_media_base_uni, "", str_replace(DIRECTORY_SEPARATOR, '/', $folder));
			$value		= substr($folder, 1);
			$text		= str_replace(DIRECTORY_SEPARATOR, "/", $folder);
			$tmp[]	= JHtml::_('select.option', $value, $text, 'value', 'text', false);
		}

		// Sort the folder list array
		//if (is_array($options))
		//{
				//sort($options);
		//}

		$groups[self::NAME] = array(
			'id' => self::NAME,
			'label' => JText::_('PLG_MEDIA_JOOMLA'),
			'items' => $tmp
		);

		return true;
	}

	public function onMediaGetFolderTree(&$tree, &$response) {
		JFactory::getLanguage()->load('plg_media_joomla');

		$mediaBase = str_replace(DIRECTORY_SEPARATOR, '/', COM_MEDIA_BASE);
		$children  = new JFolderTree(JText::_('PLG_MEDIA_JOOMLA'), $mediaBase, self::NAME);
		$childNodes = $children->getTreeArray();
		//var_dump($childNodes);die;
		$tree['children'] = $childNodes;
		return true;
	}

	private function getDirectoryFolderTree($name, $relative, $directory) {
		jimport('joomla.filesystem.folder');
		$folders = JFolder::folders($directory, '.');

		$children = new JFolderTree($name);
		$children->setData($name, self::NAME, $relative, $directory);

		if (count($folders) > 0)
		{
			foreach ($folders as $folder)
			{
				$relative = $folder;
				$absolute	= $directory . $folder;
				$childNodes   = $this->getDirectoryFolderTree($folder, $relative, $absolute);
				$children->addChildren($name, $childNodes);
			}

		}

		return $children;
	}


	public function onMediaGetFolderTreeActive(&$tree, $base, &$response) {
			JFactory::getLanguage()->load('plg_media_joomla');

			// Get some paths from the request
			if (empty($base))
			{
				$base = COM_MEDIA_BASE;
			}

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
				$node = (object) array('name' => $name, 'context' => self::NAME, 'subfolders' => '', 'relative' => $relative, 'absolute' => $absolute);

				for ($i = 0, $n = count($path); $i < $n; $i++)
				{
					if (!isset($tmp['children']))
					{
						$tmp['children'] = array();
					}

					if ($i == $n - 1)
					{
						$node->subfolders = false;
						$tmp['children'][$relative] = array('data' => $node, 'children' => array());
						$subFolders = false;
						break;
					}

					if (array_key_exists($key = implode('/', array_slice($path, 0, $i + 1)), $tmp['children']))
					{
						$tmp = &$tmp['children'][$key];
						$node->subfolders = true;
					}
				}


			}

			$children['data'] = (object) array('name' => JText::_('PLG_MEDIA_JOOMLA'), 'context' => self::NAME, 'subfolders' => $subFolders, 'relative' => '', 'absolute' => $base);
			$tree['children'][self::NAME] = $children;

			return true;
	}
}