<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_media
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');

/**
 * Media File Controller
 *
 * @package     Joomla.Administrator
 * @subpackage  com_media
 * @since       1.5
 */
class MediaControllerFile extends JControllerLegacy
{
	/**
	 * The folder we are uploading into
	 *
	 * @var   string
	 */
	protected $folder = '';

	public function update()
	{
		JSession::checkToken('request') or jexit(JText::_('JINVALID_TOKEN'));

		$folder   = $this->input->get('folder', '', 'path');
		$return   = JFactory::getSession()->get('com_media.return_url');
		$context  = $this->input->get('context', '', 'string');
		$data     = $this->input->post->get('jform', '', 'array');

		// Set the redirect
		if ($return)
		{
			$this->setRedirect($return . '&context=' . $context . '&folder=' . $folder);
		}
		else
		{
			$this->setRedirect('index.php?option=com_media&context=' . $context . '&folder=' . $folder);
		}

		// Authorize the user
		if (!JHelperMedia::authoriseUser('create'))
		{
			return false;
		}

		$response = new stdClass();
		$response->message = false;
		$response->type = false;

		// Trigger the onMediaUploadFile event.
		JPluginHelper::importPlugin('media');
		$dispatcher	= JEventDispatcher::getInstance();
		$dispatcher->trigger('onMediaUpdateFile', array('com_media.' . $context, $data, $folder, &$response));

		if ($response->message)
		{
			// Error in upload
			JError::raiseWarning(100, JText::_('COM_MEDIA_ERROR_UNABLE_TO_UPDATE_FILE'));
			$this->setMessage($response->message, $response->type);
			return false;
		}

		return true;
	}

	/**
	 * Upload one or more files
	 *
	 * @return  boolean
	 *
	 * @since   1.5
	 */
	public function upload()
	{
		// Check for request forgeries
		JSession::checkToken('request') or jexit(JText::_('JINVALID_TOKEN'));
		$params = JComponentHelper::getParams('com_media');

		// Get some data from the request
		$files        = $this->input->files->get('Filedata', '', 'array');
		$return       = JFactory::getSession()->get('com_media.return_url');
		$this->folder = $this->input->get('folder', '', 'path');
		$context      = $this->input->get('context', '', 'string');

		// Set the redirect
		if ($return)
		{
			$this->setRedirect($return . '&context=' . $context . '&folder=' . $this->folder);
		}
		else
		{
			$this->setRedirect('index.php?option=com_media&context=' . $context . '&folder=' . $this->folder);
		}

		// Authorize the user
		if (!JHelperMedia::authoriseUser('create'))
		{
			return false;
		}

		// Total length of post back data in bytes.
		$contentLength = (int) $_SERVER['CONTENT_LENGTH'];

		// Instantiate the media helper
		$mediaHelper = new JHelperMedia;

		// Maximum allowed size of post back data in MB.
		$postMaxSize = $mediaHelper->toBytes(ini_get('post_max_size'));

		// Maximum allowed size of script execution in MB.
		$memoryLimit = $mediaHelper->toBytes(ini_get('memory_limit'));

		// Check for the total size of post back data.
		if (($postMaxSize > 0 && $contentLength > $postMaxSize)
			|| ($memoryLimit != -1 && $contentLength > $memoryLimit))
		{
			JError::raiseWarning(100, JText::_('COM_MEDIA_ERROR_WARNUPLOADTOOLARGE'));

			return false;
		}

		$uploadMaxSize = $params->get('upload_maxsize', 0) * 1024 * 1024;
		$uploadMaxFileSize = $mediaHelper->toBytes(ini_get('upload_max_filesize'));

		// Perform basic checks on file info before attempting anything
		foreach ($files as &$file)
		{
			$file['name']     = JFile::makeSafe($file['name']);
			$file['filepath'] = JPath::clean(implode(DIRECTORY_SEPARATOR, array(COM_MEDIA_BASE, $this->folder, $file['name'])));

			if (($file['error'] == 1)
				|| ($uploadMaxSize > 0 && $file['size'] > $uploadMaxSize)
				|| ($uploadMaxFileSize > 0 && $file['size'] > $uploadMaxFileSize))
			{
				// File size exceed either 'upload_max_filesize' or 'upload_maxsize'.
				JError::raiseWarning(100, JText::_('COM_MEDIA_ERROR_WARNFILETOOLARGE'));

				return false;
			}

			if (JFile::exists($file['filepath']))
			{
				// A file with this name already exists
				JError::raiseWarning(100, JText::_('COM_MEDIA_ERROR_FILE_EXISTS'));

				return false;
			}

			if (!isset($file['name']))
			{
				// No filename (after the name was cleaned by JFile::makeSafe)
				$this->setRedirect('index.php', JText::_('COM_MEDIA_INVALID_REQUEST'), 'error');

				return false;
			}
		}

		// Set FTP credentials, if given
		JClientHelper::setCredentialsFromRequest('ftp');
		JPluginHelper::importPlugin('content');
		JPluginHelper::importPlugin('media');
		$dispatcher	= JEventDispatcher::getInstance();

		foreach ($files as &$file)
		{
			// The request is valid
			$err = null;

			if (!MediaHelper::canUpload($file, $err))
			{
				// The file can't be uploaded

				return false;
			}

			// Make the filename safe
			$file['name'] = JFile::makeSafe($file['name']);
			$file['filepath'] = JPath::clean(implode(DIRECTORY_SEPARATOR, array(COM_MEDIA_BASE, $this->folder, $file['name'])));

			// Trigger the onContentBeforeSave event.
			$object_file = new JObject($file);

			$result = $dispatcher->trigger('onContentBeforeSave', array('com_media.file', &$object_file, true));

			if (in_array(false, $result, true))
			{
				// There are some errors in the plugins
				JError::raiseWarning(100, JText::plural('COM_MEDIA_ERROR_BEFORE_SAVE', count($errors = $object_file->getErrors()), implode('<br />', $errors)));

				return false;
			}

			$response = new stdClass();
			$response->message = false;
			$response->type = false;

			// Trigger the onMediaUploadFile event.
			$dispatcher->trigger('onMediaUploadFile', array('com_media.' . $context, &$object_file, $this->folder, &$response));

			if ($response->message)
			{
				// Error in upload
				JError::raiseWarning(100, JText::_('COM_MEDIA_ERROR_UNABLE_TO_UPLOAD_FILE'));
				$this->setMessage($response->message, $response->type);
				return false;
			}
			else
			{
				// Trigger the onContentAfterSave event.
				$dispatcher->trigger('onContentAfterSave', array('com_media.file', &$object_file, true));
				$this->setMessage(JText::sprintf('COM_MEDIA_UPLOAD_COMPLETE', substr($object_file->filepath, strlen(COM_MEDIA_BASE))));
			}
		}

		return true;
	}

	/**
	 * Deletes paths from the current path
	 *
	 * @return  boolean
	 *
	 * @since   1.5
	 */
	public function delete()
	{
		JSession::checkToken('request') or jexit(JText::_('JINVALID_TOKEN'));

		// Get some data from the request
		$tmpl	 = $this->input->get('tmpl');
		$files	 = $this->input->get('rm', array(), 'array');
		$folder  = $this->input->get('folder', '', 'path');
		$context = $this->input->get('context', '', 'string');

		$redirect = 'index.php?option=com_media&context=' . $context . '&folder=' . $folder;

		if ($tmpl == 'component')
		{
			// We are inside the iframe
			$redirect .= '&view=mediaList&tmpl=component';
		}

		$this->setRedirect($redirect);

		// Nothing to delete
		if (empty($files))
		{
			return true;
		}

		// Authorize the user
		if (!JHelperMedia::authoriseUser('delete'))
		{
			return false;
		}

		$response = new stdClass();
		$response->message = false;
		$response->type = false;

		JPluginHelper::importPlugin('content');
		JPluginHelper::importPlugin('media');
		$dispatcher	= JEventDispatcher::getInstance();

		// Set FTP credentials, if given
		JClientHelper::setCredentialsFromRequest('ftp');

		foreach ($files as $file)
		{
			if (is_dir($file))
			{
				// Trigger the onContentBeforeDelete event.
				$result = $dispatcher->trigger('onContentBeforeDelete', array('com_media.folder', &$object_file));

				if (in_array(false, $result, true))
				{
					// There are some errors in the plugins
					$this->setMessage(JText::plural('COM_MEDIA_ERROR_BEFORE_DELETE', count($errors = $object_file->getErrors()), implode('<br />', $errors)), 'Warning');
					return false;
				}

				$result = $dispatcher->trigger('onMediaDeleteFolder', array('com_media.' . $context, $folder, $file, &$response));

				if (in_array(false, $result, true))
				{
					$this->setMessage($response->message, $response->type);
					return false;
				}

				// Trigger the onContentAfterDelete event.
				$dispatcher->trigger('onContentAfterDelete', array('com_media.folder', &$object_file));
			}
			else
			{
				$name = JFile::makeSafe($file);

				if ($context == 'joomla')
				{
					//$fullPath = JPath::clean(implode(DIRECTORY_SEPARATOR, array(COM_MEDIA_BASE, 'images' . DIRECTORY_SEPARATOR . $folder, $filename)));
					$fullPath = JPath::clean(implode(DIRECTORY_SEPARATOR, array(COM_MEDIA_BASE, $folder, $file)));
					$object_file = new JObject(array('name' => $name, 'filepath' => $fullPath));
				}
				else
				{
					$object_file = new JObject(array('filepath' => urldecode($file)));
				}

				// Trigger the onContentBeforeDelete event.
				$result = $dispatcher->trigger('onContentBeforeDelete', array('com_media.file.' . $context, &$object_file));

				if (in_array(false, $result, true))
				{
					// There are some errors in the plugins
					$this->setMessage(JText::plural('PLG_MEDIA_JOOMLA_ERROR_BEFORE_DELETE', count($errors = $object_file->getErrors()), implode('<br />', $errors)), 'Warning');
					return false;
				}

				$response = new stdClass();
				$response->message = false;
				$response->type = false;

				// Trigger the onMediaUploadFile event.
				$result = $dispatcher->trigger('onMediaDeleteFile', array('com_media.' . $context, $folder, &$object_file, &$response));

				if (in_array(false, $result, true))
				{
					// Error in Delete
					$this->setMessage($response->message, $response->type);
					return false;
				}
			}
			error_log($object_file->name);
			error_log(json_encode($object_file));
			// Trigger the onContentAfterDelete event.
			$dispatcher->trigger('onContentAfterDelete', array('com_media.file.', &$object_file));
			$this->setMessage(JText::sprintf('COM_MEDIA_DELETE_COMPLETE', $object_file->name));

		}

		return true;
	}

}
