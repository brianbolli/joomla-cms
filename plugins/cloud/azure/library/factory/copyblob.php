<?php
// no direct access
defined('_JEXEC') or die;

JLoader::register('JAzureQuery', JPATH_PLUGINS . '/cloud/azure/library/factory/jazurequery.php');

/**
 * CopyBlob Class
 *
 * PHP Version 5.4
 *
 * @category	Library
 * @package		JAzure
 * @subpackage	JAzure.CopyBlob
 * @author		Brian Bolli <brian.bolli@arctg.com>
 * @copyright	Copyright (C) 2014 Arc Technology Group, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later.
 * @link		http://www.arctg.com
 */
class CopyBlob extends JAzureQuery
{

	/**
	 * Name of remote procedure call this object represents.
	 *
	 * @var string
	 */
	protected $name = 'copyBlob';

	protected $destinationContainer;
	protected $destinationBlob;
	protected $originalContainer;
	protected $originalBlob;
	protected $container;

	/**
	 * Constructor method
	 *
	 * @param string $destinationContainer - destination container for blob
	 * @param string $destinationBlob - destination name for blob
	 * @param string $originalContainer - original container blob was in
	 * @param string $originalBlob - original blob name
	 * @param mixed $options - options object to implement for this object
	 *
	 * @return void
	 *
	 */
	public function __construct($destinationContainer, $destinationBlob, $originalContainer, $originalBlob, $options = null) {
		$this->container = $destinationContainer;
		$this->destinationContainer = $destinationContainer;
		$this->destinationBlob = $destinationBlob;
		$this->originalContainer = $originalContainer;
		$this->originalBlob = $originalBlob;
		$this->options = $this->processOptions($options);
	}

	/**
	 * Method to tailor the options parameter before making the
	 * API remote procedure call.
	 *
	 * @params mixed $options - options object to convert and encapsulate in array
	 *
	 * @return array
	 *
	 * @see JAzureQuery::processOptions()
	 */
	protected function processOptions($options) {
		return array(
			$this->destinationContainer,
			$this->destinationBlob,
			$this->originalContainer,
			$this->originalBlob,
			$options
		);
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

