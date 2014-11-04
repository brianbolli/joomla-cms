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
 * Folder Media Controller
 *
 * @package     Joomla.Administrator
 * @subpackage  com_media
 * @since       1.5
 */
class MediaControllerFolder extends JControllerLegacy
{

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
		if (!$this->authoriseUser('create'))
		{
			return false;
		}

		$response = new stdClass();
		$response->message = false;
		$response->type = false;

		// Trigger the onMediaUploadFile event.
		JPluginHelper::importPlugin('media');
		$dispatcher	= JEventDispatcher::getInstance();
		$dispatcher->trigger('onMediaUpdateFolder', array('com_media.' . $context, $data, $folder, &$response));

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
	 * Deletes paths from the current path
	 *
	 * @return  boolean
	 *
	 * @since   1.5
	 */
	public function delete()
	{
		JSession::checkToken('request') or jexit(JText::_('JINVALID_TOKEN'));

		$user	= JFactory::getUser();

		// Get some data from the request
		$tmpl    = $this->input->get('tmpl');
		$paths   = $this->input->get('rm', array(), 'array');
		$context = $this->input->get('context', 'joomla', 'string');
		$folderbase  = $this->input->get('folder', '', 'path');

		$redirect = 'index.php?option=com_media&context=' . $context . '&folder=' . $folderbase;

		if ($tmpl == 'component')
		{
			// We are inside the iframe
			$redirect .= '&view=mediaList&tmpl=component';
		}

		$this->setRedirect($redirect);

		// Just return if there's nothing to do
		if (empty($paths))
		{
			return true;
		}

		if (!$user->authorise('core.delete', 'com_media'))
		{
			// User is not authorised to delete
			JError::raiseWarning(403, JText::_('JLIB_APPLICATION_ERROR_DELETE_NOT_PERMITTED'));

			return false;
		}

		$response = new stdClass();
		$response->message = false;
		$response->type = false;

		// Trigger the onMediaFileUpload event.
		JPluginHelper::importPlugin('media');
		$dispatcher	= JEventDispatcher::getInstance();

		foreach ($paths as $folder)
		{
			// Trigger the onContentBeforeDelete event.
			$result = $dispatcher->trigger('onContentBeforeDelete', array('com_media.folder', &$object_file));

			if (in_array(false, $result, true))
			{
				// There are some errors in the plugins
				$this->setMessage(JText::plural('COM_MEDIA_ERROR_BEFORE_DELETE', count($errors = $object_file->getErrors()), implode('<br />', $errors)), 'Warning');
				return false;
			}

			$result = $dispatcher->trigger('onMediaDeleteFolder', array('com_media.' . $context, $folderbase, $folder, &$response));

			if (in_array(false, $result, true))
			{
				$this->setMessage($response->message, $response->type);
				return false;
			}

			// Trigger the onContentAfterDelete event.
			$dispatcher->trigger('onContentAfterDelete', array('com_media.folder', &$object_file));
			$this->setMessage(JText::sprintf('COM_MEDIA_DELETE_COMPLETE', substr($object_file->filepath, strlen(COM_MEDIA_BASE))));
		}

		$this->input->set('context', $context);
		$this->input->set('folder', ($parent) ? $parent . '/' . $folderbase : $folderbase);

		return true;
	}

	/**
	 * Create a folder
	 *
	 * @return  boolean
	 *
	 * @since   1.5
	 */
	public function create()
	{
		// Check for request forgeries
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$user  = JFactory::getUser();

		$data = $this->input->post->get('jform', array(), 'array');

		// Get some data from the request
		$tmpl    = $this->input->get('tmpl');
		$context = $this->input->get('context', 'joomla', 'string');

		$redirect = 'index.php?option=com_media&context=' . $context . '&folder=' . $data['folderpath'];

		if ($tmpl == 'component')
		{
			// We are inside the iframe
			$redirect .= '&view=mediaList&tmpl=component';
		}

		$this->setRedirect($redirect);

		if (strlen($data['foldername']) > 0)
		{
			if (!$user->authorise('core.create', 'com_media'))
			{
				// User is not authorised to create
				JError::raiseWarning(403, JText::_('JLIB_APPLICATION_ERROR_CREATE_NOT_PERMITTED'));

				return false;
			}

			$response = new stdClass();
			$response->message = false;
			$response->type = false;

			// Trigger the onMediaFileUpload event.
			JPluginHelper::importPlugin('media');
			$dispatcher	= JEventDispatcher::getInstance();
			$result = $dispatcher->trigger('onMediaCreateFolder', array('com_media.' . $context, $data['folderpath'], $data['foldername'], $data, &$response));

			if ($result) {
				$this->input->set('context', $context);
				$this->input->set('folder', ($folder) ? $folder . '/' . $data['foldername'] : $data['foldername']);
			} else {
				$this->setMessage($response->message, $response->type);
			}
		}
		else
		{
			// File name is of zero length (null).

			$this->setMessage(JText::_('COM_MEDIA_ERROR_UNABLE_TO_CREATE_FOLDER_WARNDIRNAME'), 'Warning');

			return false;
		}

		if ($response->message) {
			$this->setMessage($response->message, $response->type);
		}

		return true;
	}
}
