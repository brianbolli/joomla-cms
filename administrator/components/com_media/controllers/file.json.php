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

		// Check for request forgeries
		if (!JSession::checkToken('request'))
		{
			$response = array(
				'status' => '0',
				'error' => JText::_('JINVALID_TOKEN')
			);
			echo json_encode($response);
			return;
		}

		// Get the user
		$user  = JFactory::getUser();
		JLog::addLogger(array('text_file' => 'upload.error.php'), JLog::ALL, array('upload'));

		// Get some data from the request
		$file   = $this->input->files->get('Filedata', '', 'array');
		$folder = $this->input->get('folder', '', 'path');

		// Instantiate the media helper
		$mediaHelper = new JHelperMedia;

		if (
			$_SERVER['CONTENT_LENGTH'] > ($params->get('upload_maxsize', 0) * 1024 * 1024) ||
			$_SERVER['CONTENT_LENGTH'] > $mediaHelper->toBytes(ini_get('upload_max_filesize')) ||
			$_SERVER['CONTENT_LENGTH'] > $mediaHelper->toBytes(ini_get('post_max_size')) ||
			$_SERVER['CONTENT_LENGTH'] > $mediaHelper->toBytes(ini_get('memory_limit'))
		)
		{
			$response = array(
				'status' => '0',
				'error' => JText::_('COM_MEDIA_ERROR_WARNFILETOOLARGE')
			);
			echo json_encode($response);
			return;
		}

		// Trigger the onContentBeforeSave event.
		JPluginHelper::importPlugin('content');
		$dispatcher	= JEventDispatcher::getInstance();
		$object_file = new JObject($file);
		$object_file->filepath = $filepath;
		$result = $dispatcher->trigger('onContentBeforeSave', array('com_media.file', &$object_file, true));

		// Trigger the onMediaFileUpload event.
		JPluginHelper::importPlugin('media');
		$dispatcher	= JEventDispatcher::getInstance();
		$object_file = new JObject($file);
		$object_file->filepath = $filepath;
		$result = $dispatcher->trigger('onContentBeforeSave', array('com_media.file', &$object_file, true));
	}
}
