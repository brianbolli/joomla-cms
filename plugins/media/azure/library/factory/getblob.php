<?php
// no direct access
defined('_JEXEC') or die;

JLoader::register('JAzureQuery', JPATH_PLUGINS . '/media/azure/library/factory/jazurequery.php');  //
JLoader::register('JBlobProperties', JPATH_PLUGINS . '/media/azure/library/factory/results/jblobproperties.php');  //

/**
 * GetBlob Class
 *
 * PHP Version 5.4
 *
 * @category	Library
 * @package		JAzure
 * @subpackage	JAzure.GetBlob
 * @author		Brian Bolli <brian.bolli@arctg.com>
 * @copyright	Copyright (C) 2014 Arc Technology Group, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later.
 * @link		http://www.arctg.com
 */
class GetBlob extends JAzureQuery
{

	/**
	 * Name of remote procedure call this object represents.
	 *
	 * @var string
	 */
	protected $name = 'getBlob';

	/**
	 * Blob's parent container URL this object represents
	 *
	 * @var string
	 */
	protected $url;

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
	 * @param string $container - container target blob resides in
	 * @param string $blob - name of target blob
	 * @param mixed $options - options object to implement for this object
	 * @param string $url - blob's parent container url
	 *
	 * @return void
	 *
	 * @see JAzureQuery::processOptions()
	 */
	public function __construct($container, $blob, $options = null, $url) {
		$this->container = $container;
		$this->blob = $blob;
		$this->url = $url;
		$this->options = $this->processOptions($options);
	}

	/**
	 * Method to tailor the options parameter before making the
	 * API remote procedure call.
	 *
	 * @params JBlobOptions $options - options object to convert and encapsulate in array
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
	protected function processResults($results)
	{
		//return $results;
		if (empty($this->options[1]))
		{
			$properties = new JBlobProperties();
		}
		else
		{
			$properties = new JBlobProperties($this->options[1], $this->url, $results->getProperties());
		}
		return $properties;
	}
}
