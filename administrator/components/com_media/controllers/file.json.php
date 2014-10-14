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
		$params = JComponentHelper::getParams('com_media');
				//$this->setMessage('Something happened yo', 'Notice');
				//return;
		// Check for request forgeries
		if (!JSession::checkToken('request'))
		{
			$this->setMessage(JText::_('JINVALID_TOKEN'), 'Warning');
			return;
		}

		// Get the user
		$user  = JFactory::getUser();

		// Get some data from the request
		$file   = $this->input->files->get('Filedata', '', 'array');
		$context = $this->input->get('context', 'joomla', 'string');
		$folder = $this->input->get('folder', '', 'path');

		if (
			$_SERVER['CONTENT_LENGTH'] > ($params->get('upload_maxsize', 0) * 1024 * 1024) ||
			$_SERVER['CONTENT_LENGTH'] > $mediaHelper->toBytes(ini_get('upload_max_filesize')) ||
			$_SERVER['CONTENT_LENGTH'] > $mediaHelper->toBytes(ini_get('post_max_size')) ||
			$_SERVER['CONTENT_LENGTH'] > $mediaHelper->toBytes(ini_get('memory_limit'))
		)
		{
			$this->setMessage(JText::_('COM_MEDIA_ERROR_WARNFILETOOLARGE'), 'Warning');
			return;
		}

		// Trigger the onContentBeforeSave event.
		JPluginHelper::importPlugin('content');
		JPluginHelper::importPlugin('media');
		$dispatcher	= JEventDispatcher::getInstance();

		$object_file = new JObject($file);
		$object_file->filepath = $filepath;
		$dispatcher->trigger('onContentBeforeSave', array('com_media.file', &$object_file, true));

		$response = new stdClass();
		$response->message = false;
		$response->type = false;

		// Trigger the onMediaFileUpload event.
		$dispatcher->trigger('onMediaUploadFile', array($context, $folder, $file, &$response));

		if ($response->message)
		{
			$this->setMessage($response->message, $response->type);
		}
	}
}
