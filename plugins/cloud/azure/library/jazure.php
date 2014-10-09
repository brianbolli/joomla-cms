<?php
// no direct access
defined('_JEXEC') or die;

jimport('azure.vendor.autoload');

JLoader::register('JAzureFactory', JPATH_ROOT . '/plugins/cloud/azure/library/jazurefactory.php');

/**
 * JAzure class
 *
 * @package		Joomla.Plugin
 * @subpackage	Jazure
 * @author		Brian Bolli <brian.bolli@arctg.com>
 * @copyright	Copyright (C) 2014 Arc Technology Group, Inc. All rights reserved.
 * @license		GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
 * @link		http://www.arctg.com
 */
class JAzure extends JAzureFactory
{

	/**
	 * Singleton instance of JAzure class
	 *
	 * @var JAzure
	 */
	private static $_instance = null;

	/**
	 * Protected constructor method which calls parent constrcutor with required
	 * Azure PHP SDK credential constants.
	 *
	 * @return void
	 */
	protected function __construct($endpoint, $name, $key, $environmental_variable) {
		if (is_null($endpoint)) {
			//JFactory::getApplication()->enqueMessage(JText::_('PLG_CLOUD_ERROR_DEFAULT_ENDPOINT_NOT_SET'), 'error');
			return false;
		}

		if (isset($environmental_variable) && (int) $environmental_variable === 1)
		{
			if (!defined('JOOMLA_AZURE_STORAGE_ACCOUNT_NAME'))
			{
				//JFactory::getApplication()->enqueMessage(JText::_('PLG_CLOUD_ERROR_ACCOUNT_NAME_ENVIRONMENTAL_VARIABLE_NOT_SET'), 'error');
				return false;
			}
			else
			{
				$name = getenv('JOOMLA_AZURE_STORAGE_ACCOUNT_NAME');
			}

			if (!defined('JOOMLA_AZURE_STORAGE_ACCOUNT_KEY'))
			{
				//JFactory::getApplication()->enqueMessage(JText::_('PLG_CLOUD_ERROR_ACCOUNT_KEY_ENVIRONMENTAL_VARIABLE_NOT_SET'), 'error');
				return false;
			}
			else
			{
				$key = getenv('JOOMLA_AZURE_STORAGE_ACCOUNT_KEY');
			}
		}
		else if (is_null($name))
		{
			//JFactory::getApplication()->enqueMessage(JText::_('PLG_CLOUD_ERROR_ACCOUNT_NAME_NOT_SET'), 'error');
			return false;
		}
		else if (is_null($key))
		{
			//JFactory::getApplication()->enqueMessage(JText::_('PLG_CLOUD_ERROR_ACCOUNT_KEY_NOT_SET'), 'error');
			return false;
		}

		parent::__construct($endpoint, $name, $key);
	}

	/**
	 * Public static entry point to retrieve JAzure object for access to
	 * exposed Azure PHP SDK methods.
	 *
	 * @return JAzure
	 */
	public static function getInstance($endpoint = null, $name = null, $key = null, $environemt_variable = false) {
		if (!(self::$_instance instanceof JAzure)) {
			self::$_instance = new JAzure($endpoint, $name, $key, $environemt_variable);
		}

		if (!is_array(parent::$containers)) {
			self::$_instance = null;
			return false;
		}

		return self::$_instance;
	}
}