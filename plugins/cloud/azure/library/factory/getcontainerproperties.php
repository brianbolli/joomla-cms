<?php
// no direct access
defined('_JEXEC') or die;

JLoader::register('JAzureQuery', JPATH_PLUGINS . '/cloud/azure/library/factory/jazurequery.php');


use WindowsAzure\Blob\Models\CreateContainerOptions;
use WindowsAzure\Blob\Models\PublicAccessType;
use WindowsAzure\Blob\Models\GetContainerPropertiesResult;

/**
 * GetContainerProperties Class
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
class GetContainerProperties extends JAzureQuery
{
	/**
	 * Name of remote procedure call this object represents.
	 *
	 * @var string
	 */
	protected $name = 'getContainerProperties';

	/**
	 *
	 * @var string
	 */
	protected $container;

	/**
	 * Constructor method
	 *
	 * @param array/stdClass $options
	 * @param string $container
	 */
	public function __construct($container, $options) {
		$this->container = $container;
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
		return array($this->container, $options);
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
		//return $results;
		if ($results instanceof GetContainerPropertiesResult) {
			$obj = new stdClass();
			$obj->e_tag = $results->getEtag();
			$obj->last_modified = $results->getLastModified();
			return $obj;
		}
		return false;
	}

	/**
	 * Overrided method to verify validity of conatiner parameter passed in at object
	 * creation.
	 *
	 * @see JAzureQuery::containerExists()
	 */
	protected function containerExists(Array $containers) {
		return true;
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
