<?php
// no direct access
defined('_JEXEC') or die;

JLoader::register('JAzureQuery', JPATH_PLUGINS . '/cloud/azure/library/factory/jazurequery.php');

/**
 * ListBlobs Class
 *
 * PHP Version 5.4
 *
 * @category	Library
 * @package		JAzure
 * @subpackage	JAzure.ListBlobs
 * @author		Brian Bolli <brian.bolli@arctg.com>
 * @copyright	Copyright (C) 2014 Arc Technology Group, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later.
 * @link		http://www.arctg.com
 */
class ListBlobs extends JAzureQuery
{

	/**
	 * Name of remote procedure call this object represents.
	 *
	 * @var string
	 */
	protected $name = 'listBlobs';

	/**
	 *
	 * @var string
	 */
	protected $container;

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
	public function __construct($container, $options = null) {
		if (is_null($container)) {
			$this->throwJoomlaError("Must set container to list blobs from");
			return false;
		}

		$this->container = $container;
		$this->options = $this->processOptions($options);
	}

	/**
	 * Constructor method
	 *
	 * @param mixed $options - options object to implement for this object
	 *
	 * @return void
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
		$return = array();
		foreach ($results->getBlobs() as $blob) {
			$properties = $blob->getProperties();

			$b = array();
			$b['name'] = $blob->getName();
			$b['url'] = $blob->getUrl();
			$b['metadata'] = $blob->getMetadata();
			$b['last_modified'] = date("Y-m-d H:i:s", $properties->getLastModified()->getTimestamp());
			$b['e_tag'] = $properties->getETag();
			$b['size'] = $properties->getContentLength();

			$return[$blob->getName()] = $b;
		}
		return $return;
	}
}