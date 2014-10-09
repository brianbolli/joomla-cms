<?php
// no direct access
defined('_JEXEC') or die;

JLoader::register('JAzureQuery', JPATH_PLUGINS . '/media/azure/library/factory/jazurequery.php');
JLoader::register('JBlobProperties', JPATH_PLUGINS . '/media/azure/library/factory/results/jblobproperties.php');

use WindowsAzure\Blob\Models\BlobProperties;
use WindowsAzure\Blob\Models\CreateBlobOptions;

/**
 * CreateBlockBlob Class
 *
 * PHP Version 5.4
 *
 * @category	Library
 * @package		JAzure
 * @subpackage	JAzure.CreateBlockBlob
 * @author		Brian Bolli <brian.bolli@arctg.com>
 * @copyright	Copyright (C) 2014 Arc Technology Group, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later.
 * @link		http://www.arctg.com
 */
class CreateBlockBlob extends JAzureQuery
{

	/**
	 * Name of remote procedure call this object represents.
	 *
	 * @var string
	 */
	protected $name = 'createBlockBlob';

	/**
	 *
	 * @var string
	 */
	protected $container;

	/**
	 * Name of the blob where properties are to be modified.
	 *
	 * @var string
	 */
	private $blob;

	/**
	 * Name of the blob where properties are to be modified.
	 *
	 * @var file
	 */
	private $content;

	/**
	 * constructor
	 *
	 * @param string $container
	 * @param string $blob
	 * @param string $content
	 * @param mixed $options
	 *
	 * @return void
	 */
	public function __construct($container, $blob, $content, $options) {
		$this->container = $container;
		$this->blob = $blob;
		$this->content = $content;
		$this->options = $this->processOptions($options);
	}

	/**
	 * Method to tailor the options parameter before making the
	 * API remote procedure call.
	 *
	 * @params JBlobOptions $options - options object to convert to BlobPropeties Object and encapsulate in array
	 *
	 * @return array
	 *
	 * @see JAzureQuery::processOptions()
	 */
	protected function processOptions($options) {
		if ($options instanceof JBlobProperties || is_array($options) || is_object($options)) {
			$blobOptions = $this->loadAzureOptionsObject(new CreateBlobOptions(), $options);
		} else {
			$blobOptions = null;
		}
		return array($this->container, $this->blob, $this->content, $blobOptions);
	}

	/**
	 *
	 * @params unknown $result
	 *
	 * @return unknown $result;
	 */
	protected function processResults($results) {
		return $results;
	}
}

