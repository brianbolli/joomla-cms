<?php

/**
* @package     Joomla.Plugin
* @subpackage  Cloud.joomla
*
* @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
* @license     GNU General Public License version 2 or later; see LICENSE.txt
*/

defined('_JEXEC') or die;

JLoader::register('JAzure', JPATH_ROOT . '/plugins/cloud/azure/library/jazure.php');

/**
 * Azure Cloud Plugin
 *
 * @package     Joomla.Plugin
 * @subpackage  Cloud.joomla
 * @since       3.3
 */
class PlgCloudAzure extends JPlugin
{

	const NAME = 'Azure';

	protected $azure = false;

	public function __construct(&$subject, $config)
	{
		parent::__construct($subject, $config);

		if ($this->params->get('azure_enabled', 0)) {
			$endpoint = $this->params->get('azure_default_endpoint', null);
			$name = $this->params->get('azure_account_name', null);
			$key = $this->params->get('azure_account_key', null);
			$environment_variable = $this->params->get('azure_use_environment_variable', null);
			$this->azure = JAzure::getInstance($endpoint, $name, $key, $environment_variable);
		}
	}

	public function onCloudActive() {

	}

	public function onCloudMediaFolderTree(&$root, $options, &$response) {
		if ($this->azure)
		{

			$tree = array();
			$azure = JAzure::getInstance();
			$baseUrl = $azure->getBaseUrl();
			$containers = $azure->listContainers();
			$tree['children'] = array();

			foreach ($containers as $container) {
					$folder		= $container['name'];
					$name		= $container['name'];
					$relative	= str_replace($baseUrl, '', $container['url']);
					$absolute	= $container['url'];
					$path		= explode('/', $relative);
					$node		= (object) array('name' => $name, 'relative' => $relative, 'absolute' => $absolute);

					$tree['children'][$name] = array('data' => $node, 'children' => array());
			}

			$tree['data'] = (object) array('name' =>'Azure', 'relative' => $baseUrl, 'absolute' => $baseUrl);

			$root['children'][JText::_('PLG_CLOUD_AZURE')] = $tree;
			$root['data'] = (object) array('name' => JText::_('PLG_CLOUD_AZURE'), 'relative' => '', 'absolute' => $this->azure->getBaseUrl());
		}
		else
		{
			return false;
		}

		return true;
	}

		public function onCloudMediaList() {

		}

		public function onCloudMediaUpload() {

		}


}