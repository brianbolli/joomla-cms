<?php
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

jimport('joomla.form.formfield');
jimport('joomla.form.helper');
JFormHelper::loadFieldClass('list');

class JFormFieldAzureContainers extends JFormFieldList
{
	protected $type = 'AzureContainers';

	public function getOptions() {
		$containers = JAzure::getInstance()->query('listContainers');
		$options = array();
		foreach ($containers as $key => $obj) {
			// Create a new option object based on the <option /> element.
			$tmp = JHtml::_(
					'select.option', $key,
					JText::alt(trim((string) $key), preg_replace('/[^a-zA-Z0-9_\-]/', '_', $this->fieldname)), 'value', 'text');
			$options[$key] = $tmp;
		}

		reset($options);

		return $options;
	}
}