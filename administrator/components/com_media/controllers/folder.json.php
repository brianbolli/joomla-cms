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
			$form = JForm::getInstance('com_media.uploadfolder', 'uploadfolder', array('control' => 'jform', 'load_data' => false));

			$data = array(
					'folderpath' => $folder,
					'folderbase' => $folder,
					'contextbase' => $context
			);

			$dispatcher = JDispatcher::getInstance();
			JPluginHelper::importPlugin('media');
			$results = $dispatcher->trigger('onMediaPrepareFolderForm', array('com_media.' . $context, &$form, &$response));

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

			$layout = new JLayoutFile('uploadfolder', JPATH_COMPONENT_ADMINISTRATOR . '/layouts/');
			$data = $layout->render(array('folder' => $folder, 'context' => $context, 'form' => $form));

			echo new JResponseJson($data);
		}
		catch (Exception $e)
		{
			echo new JResponseJson($e);
		}

	}
}
