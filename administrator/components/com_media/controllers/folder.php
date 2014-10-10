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
		$tmpl   = $this->input->get('tmpl');
		$paths  = $this->input->get('rm', array(), 'array');
		$folder = $this->input->get('folder', '', 'path');

		$redirect = 'index.php?option=com_media&folder=' . $folder;

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


		return $ret;
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

		$folder      = $this->input->get('foldername', '');
		$folderCheck = (string) $this->input->get('foldername', null, 'raw');
		$parent      = $this->input->get('folderbase', '', 'path');

		$this->setRedirect('index.php?option=com_media&folder=' . $parent . '&tmpl=' . $this->input->get('tmpl', 'index'));

		if (strlen($folder) > 0)
		{
			if (!$user->authorise('core.create', 'com_media'))
			{
				// User is not authorised to create
				JError::raiseWarning(403, JText::_('JLIB_APPLICATION_ERROR_CREATE_NOT_PERMITTED'));

				return false;
			}


			$this->input->set('folder', ($parent) ? $parent . '/' . $folder : $folder);
		}
		else
		{
			// File name is of zero length (null).
			JError::raiseWarning(100, JText::_('COM_MEDIA_ERROR_UNABLE_TO_CREATE_FOLDER_WARNDIRNAME'));

			return false;
		}

		return true;
	}
}
