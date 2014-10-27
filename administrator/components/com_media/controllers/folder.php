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
		$folder  = $this->input->get('folder', '', 'path');

		$redirect = 'index.php?option=com_media&context=' . $context . '&folder=' . $folder;

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

		foreach ($paths as $path)
		{
			$dispatcher->trigger('onMediaDeleteFolder', array('com_media.' . $context, $folder, $path, &$response));
			if ($response) {
				$this->setMessage($response->message, $response->type);
				$response->message = false;
				$response->type = false;
			}
		}

		$this->input->set('context', $context);
		$this->input->set('folder', ($parent) ? $parent . '/' . $folder : $folder);

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

		$folder = $this->input->get('folder');
		$folderName = $this->input->get('foldername', '');
		$folderCheck = (string) $this->input->get('foldername', null, 'raw');
		$context = $this->input->get('context', 'joomla', 'string');

		$redirect = 'index.php?option=com_media&context=' . $context . '&folder=' . $folder;

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
			$result = $dispatcher->trigger('onMediaCreateFolder', array('com_media.' . $context, $data['folderpath'], $folderName, $folderCheck, &$response));

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
