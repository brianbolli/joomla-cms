<?php
// no direct access
defined('_JEXEC') or die;

JLoader::register('JAzureQuery', JPATH_PLUGINS . '/media/azure/library/factory/jazurequery.php');


use WindowsAzure\Blob\Models\GetServicePropertiesResult;

/**
 * GetServiceProperties Class
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
class GetServiceProperties extends JAzureQuery
{
	/**
	 * Name of remote procedure call this object represents.
	 *
	 * @var string
	 */
	protected $name = 'getServiceProperties';

	/**
	 * Constructor method
	 *
	 * @param array/stdClass $options
	 * @param string $container
	 */
	public function __construct($options) {
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
		return $options;
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
}
