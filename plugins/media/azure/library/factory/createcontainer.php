<?php
// no direct access
defined('_JEXEC') or die;

JLoader::register('JAzureQuery', JPATH_PLUGINS . '/media/azure/library/factory/jazurequery.php');


use WindowsAzure\Blob\Models\CreateContainerOptions;
use WindowsAzure\Blob\Models\PublicAccessType;

/**
 * CreateContainer Class
 *
 * PHP Version 5.4
 *
 * @category	Library
 * @package		JAzure
 * @subpackage	JAzure.CreateContainer
 * @author		Brian Bolli <brian.bolli@arctg.com>
 * @copyright	Copyright (C) 2014 Arc Technology Group, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later.
 * @link		http://www.arctg.com
 */
class CreateContainer extends JAzureQuery
{
	/**
	 * Name of remote procedure call this object represents.
	 *
	 * @var string
	 */
	protected $name = 'createContainer';

	/**
	 *
	 * @var string
	 */
	protected $container;

	/**
	 *
	 * @var string
	 */
	protected $acl;

	/**
	 * Constructor method
	 *
	 * @param array/stdClass $options
	 * @param string $container
	 */
	public function __construct($container, $acl = false, $options) {
		$this->container = ereg_replace("[^A-Za-z0-9]", "", strtolower($container));
		$this->acl = $acl;
		$this->options = $this->processOptions($options);
	}

	/**
	/* Method to tailor the options parameter before making the
	 * API remote procedure call.
	 *
	 * @param mixed $options -
	 *
	 * @return mixed;
	 *
	 * @see JAzureQuery::processOptions()
	 */
	protected function processOptions($options) {
		if (is_null($options) || !($options instanceof CreateContainerOptions)) {
			$containerOptions = new CreateContainerOptions();
			if ($this->acl !== false && PublicAccessType::isValid($this->acl)) {
				$containerOptions->setPublicAccess($this->acl);
			}
		} else {
			$containerOptions = null;
		}
		return array($this->container, $containerOptions);
	}

	/**
	 * Method to tailor API response before returning.
	 *
	 * @params mixed $results
	 *
	 * @return mixed
	 *
	 * @see JAzureQuery::processResults()
	 */
	protected function processResults($results) {
		return $results;
	}

	/**
	 * Overrided method to verify validity of conatiner parameter passed in at object
	 * creation.
	 *
	 * @see JAzureQuery::containerExists()
	 */
	protected function containerExists(Array $containers) {
		if (is_null($this->container)) {
			return false;
		}

		if (array_key_exists($this->container, $containers) === false) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Overrided method to generate container test error message
	 *
	 * @see JAzureQuery::containerExists()
	 */
	protected function containerTestErrorMessage($container) {
		return JText::sprintf(JText::_('COM_LOFT_J_AZURE_CONTAINER_ALREADY_EXIST_ERROR'), $container);
	}
}
