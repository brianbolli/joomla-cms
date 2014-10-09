<?php
// no direct access
defined('_JEXEC') or die;

JLoader::register('JAzureQuery', JPATH_PLUGINS . '/cloud/azure/library/factory/jazurequery.php');

/**
 * DeleteBlob Class
 *
 * PHP Version 5.4
 *
 * @category	Library
 * @package		JAzure
 * @subpackage	JAzure.DeleteBlob
 * @author		Brian Bolli <brian.bolli@arctg.com>
 * @copyright	Copyright (C) 2014 Arc Technology Group, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later.
 * @link		http://www.arctg.com
 */
class DeleteBlob extends JAzureQuery
{

	/**
	 * Name of remote procedure call this object represents.
	 *
	 * @var string
	 */
	protected $name = 'deleteBlob';

	/**
	 *
	 * @var string
	 */
	protected $container;

	/**
	 * Name of blob this object represents.
	 *
	 * @var string
	 */
	protected $blob;

	/**
	 * Constructor method
	 *
	 * @param mixed $options - options object to implement for this object
	 * @param string $container - container target blob resides in
	 *
	 * @return void
	 *
	 * @see JAzureQuery::processOptions()
	 */
	public function __construct($container, $blob, $options) {
		$this->container = $container;
		$this->blob = $blob;
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
		return array($this->container, $this->blob, $options);
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
