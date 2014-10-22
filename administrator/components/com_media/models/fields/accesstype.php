<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2014 Arc Technology Group, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

jimport('joomla.form.formfield');
jimport('joomla.form.helper');
JFormHelper::loadFieldClass('list');

/**
 * Custom Form Field class for the Joomla Azure Blob Exetension
 * to genereate a list of accepted MIME content types.
 *
 * @package     Joomla.Platform
 * @subpackage  Form
 * @since       11.1
 */
class JFormFieldAccessType extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var		string
	 * @since	11.1
	 */
	protected $type = 'AccessType';

	/**
	 * Method to get the field options.
	 *
	 * @return	array  The field option objects.
	 *
	 * @since	11.1
	 */
	protected function getOptions() {
		$options = array('' => 'Private');
		$access_types = JAzure::getInstance()->getAccessTypes();
		return array_merge($options, $access_types);
	}
}

