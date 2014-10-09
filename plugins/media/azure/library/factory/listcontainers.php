<?php
// no direct access
defined('_JEXEC') or die;

JLoader::register('JAzureQuery', JPATH_PLUGINS . '/media/azure/library/factory/jazurequery.php');
JLoader::register('GetContainerAcl', JPATH_PLUGINS . '/media/azure/library/factory/getcontaineracl.php');

use WindowsAzure\Blob\Models\ListContainersOptions;
use WindowsAzure\Blob\Models\ListContainersResult;

/**
 * ListContainers Class
 *
 * PHP Version 5.4
 *
 * @category	Library
 * @package		JAzure
 * @subpackage	JAzure.ListContainers
 * @author		Brian Bolli <brian.bolli@arctg.com>
 * @copyright	Copyright (C) 2014 Arc Technology Group, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later.
 * @link		http://www.arctg.com
 */
class ListContainers extends JAzureQuery
{

	/**
	 * Name of remote procedure call this object represents.
	 *
	 * @var string
	 */
	protected $name = 'listContainers';

	/**
	 *
	 * @var string
	 */
	protected $container;

	protected $connectionString;

	protected $containers;

	/**
	 * Constructor method
	 *
	 * @param string $container - container target blob resides in
	 * @param string $blob - name of target blob
	 * @param JBlobProperties $options - options object to implement for this object
	 *
	 * @return void
	 */
	public function __construct($connectionString, $containers = array(), $options = null) {
		$this->connectionString = $connectionString;
		$this->containers = $containers;
		$this->container = null;
		$this->options = $this->processOptions($options);
	}

	/**
	 * Method to tailor the options parameter before making the
	 * API remote procedure call.
	 *
	 * @param mixed $options - options object to convert to BlobPropeties Object and encapsulate in array
	 *
	 * @see JAzureQuery::processOptions()
	 */
	protected function processOptions($options) {
		return array($options);
	}

	protected function processOptionsOld($options) {
		if (!(is_null($options)) && is_array($options) || is_object($options)) {
			$listOptions = new ListContainersOptions();
			foreach ($options as $method => $value) {
				if (method_exists(ListContainersOptions, $method)) {
					$listOptions->$method($value);
				} else {
					$this->throwJoomlaError("Invalid List Container Option " . $method . " parameter passed into listContainers method");
				}
			}
		} elseif (is_null($options)) {
			$listOptions = null;
		} elseif ($options instanceof ListContainersOptions) {
			$listOptions = $options;
		} else {
			$this->throwJoomlaError("Invalid List Container Option " . $method . " parameter passed into listContainers method");
			return false;
		}

		return $listOptions;
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
		if ($results instanceof ListContainersResult) {
			$containers = array();

			foreach ($results->getContainers() as $container) {
				$properties = $container->getProperties();

				$c = array();
				$c['name'] = $container->getName();
				$acl = new GetContainerAcl($c['name'], null);
				$c['public_access'] = $acl->execute($this->connectionString, $this->containers);
				$c['url'] = $container->getUrl();
				$c['metadata'] = $container->getMetadata();
				$c['last_modified'] = date("Y-m-d H:i:s", $properties->getLastModified()->getTimestamp());
				$c['e_tag'] = $properties->getETag();

				$containers[$container->getName()] = $c;
			}

			return $containers;
		} else {
			return false;
		}
	}
}
