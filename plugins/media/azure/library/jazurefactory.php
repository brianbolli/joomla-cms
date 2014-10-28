<?php
// no direct access
defined('_JEXEC') or die;

require_once 'sdk/WindowsAzure/WindowsAzure.php';

use WindowsAzure\Blob\Models\PublicAccessType;
use WindowsAzure\Common\ServicesBuilder;
use WindowsAzure\Common\ServiceException;

/**
 * JAzureFactory Class
 *
 * PHP Version 5.4
 *
 * @category	Library
 * @package		JAzure
 * @subpackage	JAzure.JAzureFactory
 * @author		Brian Bolli <brian.bolli@arctg.com>
 * @copyright	Copyright (C) 2014 Arc Technology Group, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later.
 * @link		http://www.arctg.com
 */
class JAzureFactory
{
	/**
	 *
	 * @var array
	 */
	protected static $containers = array();

	/**
	 * Connection string with endpoint, account name and account key parameters
	 * passed into constrcture incoporated for authentication of REST API calls.
	 *
	 * @var string
	 */
	protected $connectionString;

	/**
	 * Private property to store end-user's chosen endpoint value
	 *
	 * @var string
	 */
	protected $endpoint;

	/**
	 * Private property to store end-user's chosen account name value
	 *
	 * @var string
	 */
	protected $name;

	/**
	 * Private property to store end-user's chosen account key value
	 *
	 * @var string
	 */
	protected $key;

	/**
	 *
	 * @param string $endpoint - endpoint protocal to be used in REST connection string
	 * @param string $name - account name to be used in REST connection string
	 * @param string $key - account key to be used in REST connection string
	 *
	 * @return void
	 */
	protected function __construct($endpoint, $name, $key) {
		$this->connectionString = "DefaultEndpointsProtocol=" . $endpoint . ";AccountName=" . $name . ";AccountKey=" . $key;

		$this->endpoint = $endpoint;
		$this->name = $name;
		$this->key = $key;

		self::$containers = $this->listContainers();
	}

	/**
	 * Internal entry point for all queries.
	 *
	 * @param string $type - name of REST method to execute
	 * @param array $options - (optional) options for REST method
	 *
	 * @return mixed
	 */

	private function query($class, Array $args = array()) {

		if (!is_array(self::$containers)) {
			self::$containers = array();
		}

		require_once 'factory/' . strtolower($class) . '.php';
		if (Class_exists($class, false)) {
			$object = new ReflectionClass($class);
			$query = $object->newInstanceArgs($args);
			return $query->execute($this->connectionString, self::$containers);
		} else {
			return $this->throwJoomlaError("Reference to non-existant Azure Query " . $type . ".");
		}
	}

	/**
	 * Method to enque a Joomla alert and return failure to pass back to
	 * calling method.
	 *
	 * @param string $msg - message text for Joomla alert
	 * @param string $type - alert type for Joomla alert
	 *
	 * @return boolean
	 */
	private function throwJoomlaError($msg, $type = 'error') {
		JFactory::getApplication()->enqueueMessage(JText::_($msg), $type);
		return false;
	}

	/**
	 * Constructs a provided containers url
	 *
	 * @param string $container
	 * @return string
	 */
	private function buildContainerUrl($container) {
		return $this->endpoint . '://' . $this->name . '.blob.core.windows.net/' . $container . '/';
	}

	public function getBaseUrl() {
		return $this->endpoint . '://' . $this->name . '.blob.core.windows.net/';
	}

	/**
	 * Returns array of container access types
	 *
	 * @return array
	 */
	public function getAccessTypes() {
		return array(
			PublicAccessType::NONE => 'private',
			PublicAccessType::BLOBS_ONLY => 'blob',
			PublicAccessType::CONTAINER_AND_BLOBS => 'container'
		);
	}

	/**
	 * Method to access Azure Blob properties.
	 *
	 * @param string $container
	 * @param string $blob
	 *
	 * @return JBlobProperties
	 */
	public function getBlob($container, $blob, $options = null) {
		$url = $this->buildContainerUrl($container);
		return $this->query('GetBlob', array($container, $blob, $options, $url));
	}

	/**
	 * Method to access Azure Blob properties.
	 *
	 * @param string $container
	 * @param string $blob
	 *
	 * @return JBlobProperties
	 */
	public function getBlobProperties($container, $blob, $options = null) {
		$url = $this->buildContainerUrl($container);
		return $this->query('GetBlobProperties', array($container, $blob, $options, $url));
	}
	/**
	 * Method to set Blob Properties
	 *
	 * @param string $container
	 * @param string $blob
	 * @param mixed $options
	 *
	 * @return SetBlobPropertiesResult
	 *
	 * @see SetBlobProperties()
	 */
	public function setBlobProperties($container, $blob, $options = null) {
		return $this->query('SetBlobProperties', array($container, $blob, $options));
	}

	/**
	 * Method to create a new blob
	 *
	 * @param string $container
	 * @param string $blob
	 * @param stream $content
	 * @param mixed $options
	 *
	 * @return mixed
	 *
	 * @see CreateBlockBlob()
	 */
	public function createBlockBlob($container, $blob, $content, $options = null, &$response) {
		return $this->query('CreateBlockBlob', array($container, $blob, $content, $options, &$response));
	}

	/**
	 * Method to list all containers for an Azure account
	 *
	 * @param mixed $options
	 * @return ListContainersResult
	 *
	 * @see ListContainers()
	 */
	public function listContainers($options = null) {
		return $this->query('ListContainers', array($this->connectionString, self::$containers, $options));
	}

	/**
	 * Method to list all blobs inside a container
	 *
	 * @param string $container
	 * @param mixed $options
	 *
	 * @return array
	 *
	 * @see ListBlobs()
	 */
	public function listBlobs($container, $options = null) {
		return $this->query('ListBlobs', array($container, $options));
	}

	/**
	 * Method to delte blob
	 *
	 * @param string $container
	 * @param string $blob
	 * @param mixed $options
	 * @return Ambigous <mixed, boolean>
	 */
	public function deleteBlob($container, $blob, $options = null) {
		return $this->query('DeleteBlob', array($container, $blob, $options));
	}

	/**
	 * Method to delete container
	 *
	 * @param string $container
	 * @param string $options
	 *
	 * @return Ambigous <mixed, boolean>
	 *
	 * @see DeleteBlob()
	 */
	public function deleteContainer($container, $options = null) {
		return $this->query('DeleteContainer', array($container, $options));
	}

	/**
	 *
	 * @param unknown $container
	 * @param string $options
	 * @return Ambigous <mixed, boolean>
	 */
	public function createContainer($container, $acl, $options = null) {
		return $this->query('CreateContainer', array($container, $acl, $options));
	}

	/**
	 * Method to copy a blob from one container to another
	 *
	 * @param string $destinationContainer - destination container for blob
	 * @param string $destinationBlob - destination name for blob
	 * @param string $originalContainer - original container blob was in
	 * @param string $originalBlob - original blob name
	 * @param mixed $options - options object to implement for this object
	 *
	 * @return
	 *
	 * @see CopyBlob()
	 */
	public function copyBlob($destinationContainer, $destinationBlob, $originalContainer, $originalBlob, $options = null) {
		return $this->query('CopyBlob', array($destinationContainer, $destinationBlob, $originalContainer, $originalBlob, $options));
	}

	/**
	 *
	 * @param unknown $container
	 * @param string $options
	 *
	 * @return Ambigous <mixed, boolean>
	 *
	 * @see getContainerAcl()
	 */
	public function getContainerAcl($container, $options = null) {
		return $this->query('GetContainerAcl', array($container, $options));
	}

	/**
	 *
	 * @param unknown $container
	 * @param string $options
	 *
	 * @return Ambigous <mixed, boolean>
	 *
	 * @see getContainerAcl()
	 */
	public function setContainerAcl($container, $acl, $options = null) {
		return $this->query('SetContainerAcl', array($container, $acl, $options));
	}
	/**
	 *
	 * @param unknown $container
	 * @param string $options
	 *
	 * @see GetContainerProperties()
	 */
	public function getContainerProperties($container, $options = null) {
		return $this->query('GetContainerProperties', array($container, $options));
	}
}