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
 * File Media Controller
 *
 * @package     Joomla.Administrator
 * @subpackage  com_media
 * @since       1.6
 */
class MediaControllerFile extends JControllerLegacy
{
	/**
	 * Upload a file
	 *
	 * @return  void
	 *
	 * @since   1.5
	 */
	function upload()
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
		if (!$this->authoriseUser('create'))
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
				$this->setMessage(JText::plural('COM_MEDIA_ERROR_BEFORE_SAVE', count($errors = $object_file->getErrors()), implode('<br />', $errors)) , 'Warning');
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

		echo new JResponseJson(true);
	}

	public function form()
	{
		// Check for request forgeries
		//JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$user  = JFactory::getUser();

		$context     = $this->input->get('context', 'joomla', 'string');
		$folder      = $this->input->get('folder', '', 'path');

		if (!$user->authorise('core.create', 'com_media'))
		{
				// User is not authorised to create
				JError::raiseWarning(403, JText::_('JLIB_APPLICATION_ERROR_CREATE_NOT_PERMITTED'));

				return false;
		}

		$response = new stdClass();
		$response->message = false;
		$response->type = false;

		// Get the form.
		JForm::addFormPath(JPATH_COMPONENT . '/models/forms');
		JForm::addFieldPath(JPATH_COMPONENT . '/models/fields');

		try
		{
				$form = JForm::getInstance('com_media.uploadfile', 'uploadfile', array('control' => 'jform', 'load_data' => false));

				$data = array(
						'folderbase' => $this->input->get('foldername', ''),
						'contextbase' => $this->input->get('contextbase', '')
				);

				$dispatcher = JDispatcher::getInstance();
				JPluginHelper::importPlugin('media');
				$results = $dispatcher->trigger('onMediaPrepareFileForm', array('com_media.' . $context, &$form, &$response));

				// Check for errors encountered while preparing the form.
				if (count($results) && in_array(false, $results, true))
				{
						// Get the last error.
						$error = $dispatcher->getError();

						if (!($error instanceof Exception))
						{
								throw new Exception($error);
						}
				}


				// Load the data into the form after the plugins have operated.
				$form->bind($data);

				$layout = new JLayoutFile('uploadfile', JPATH_COMPONENT_ADMINISTRATOR . '/layouts/');
				$data = $layout->render(array('folder' => $folder, 'context' => $context, 'form' => $form));

				echo new JResponseJson($data);
		}
		catch (Exception $e)
		{
				echo new JResponseJson($e);
		}

	}
}
