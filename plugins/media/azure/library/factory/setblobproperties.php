<?php
// no direct access
defined('_JEXEC') or die;

JLoader::register('JAzureQuery', JPATH_PLUGINS . '/media/azure/library/factory/jazurequery.php');
JLoader::register('JBlobProperties', JPATH_PLUGINS . '/media/azure/library/factory/results/jblobproperties.php');

use WindowsAzure\Blob\Models\GetBlobPropertiesOptions;
use WindowsAzure\Blob\Models\GetBlobPropertiesResult;
use WindowsAzure\Blob\Models\SetBlobPropertiesOptions;
use WindowsAzure\Blob\Models\BlobProperties;
use WindowsAzure\Blob\Models\AccessCondition;
use WindowsAzure\Blob\Models\SetBlobPropertiesResult;

/**
 * SetBlobProperties Class
 *
 * PHP Version 5.4
 *
 * @category	Library
 * @package		JAzure
 * @subpackage	JAzure.SetBlobProperties
 * @author		Brian Bolli <brian.bolli@arctg.com>
 * @copyright	Copyright (C) 2014 Arc Technology Group, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later.
 * @link		http://www.arctg.com
 */
class SetBlobProperties extends JAzureQuery
{

	/**
	 * Name of remote procedure call this object represents.
	 *
	 * @var string
	 */
	protected $name = 'setBlobProperties';

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
	 * Constructor method
	 *
	 * @param string $container - container target blob resides in
	 * @param string $blob - name of target blob
	 * @param JBlobProperties $options - options object to implement for this object
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
	 * @params JBlobOptions $options - options object to convert to BlobPropeties Object and encapsulate in array
	 *
	 * @return array
	 *
	 * @see JAzureQuery::processOptions()
	 */
	protected function processOptions($options) {

		if ($options instanceof JBlobProperties || is_array($options) || is_object($options)) {
			$blobProperties = $this->loadAzureOptionsObject(new BlobProperties(), $options);
		} else {
			$blobProperties = null;
		}

		$blobOptions = new SetBlobPropertiesOptions($blobProperties);

		return array($this->container, $this->blob, $blobOptions);
	}

	/**
	 *
	 * @params unknown $result
	 *
	 * @return unknown $result;
	 */
	protected function processResults($results) {
		if ($results instanceof SetBlobPropertiesResult) {
			return true;
		} else {
			return false;
		}
	}
}
