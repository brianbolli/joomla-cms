<?php
// no direct access
defined('_JEXEC') or die;

JLoader::register('IQuery', JPATH_PLUGINS . '/media/azure/library/factory/iquery.php');

use WindowsAzure\Common\ServicesBuilder;
use WindowsAzure\Common\ServiceException;
use WindowsAzure\Blob\BlobRestProxy;

/**
 * JAzureQuery Class
 *
 * PHP Version 5.4
 *
 * @category	Library
 * @package		JAzure
 * @subpackage	JAzure.JAzureQuery
 * @author		Brian Bolli <brian.bolli@arctg.com>
 * @copyright	Copyright (C) 2014 Arc Technology Group, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later.
 * @link		http://www.arctg.com
 */
abstract class JAzureQuery implements IQuery
{
	/**
	 *
	 * @var unknown
	 */
	protected $options;

	protected $response;

	/**
	 *
	 * @param unknown $options
	 */
	abstract protected function processOptions($options);

	/**
	 *
	 * @param unknown $results
	 */
	abstract protected function processResults($results);

	/**
	 *
	 * @param unknown $containers
	 */
	public function setContainers($containers) {
		$this->allContainers = $containers;
	}

	/**
	 * Endpoint to execute all Azure Blob REST calls
	 *
	 * @param string $connection - connection string to connect to REST services
	 * @param array $containers - array of valid containers for error checking
	 *
	 * @return mixed
	 *
	 * @see IQuery::execute()
	 */
	public function execute($connection, Array $containers) {
		$result = null;

		$proxy = ServicesBuilder::getInstance()->createBlobService($connection);

		if ($this->containerExists($containers) || is_null($this->container)) {
			try {
				if (is_null($this->options)) {
					$result = call_user_func(array($proxy, $this->name), $this->container);
				} elseif (is_object($this->options)) {
					$result = call_user_func(array($proxy, 'parent::' . $this->name), $this->options);
				} elseif (is_array($this->options)) {
					$result = call_user_func_array(array($proxy, $this->name), $this->options);
				} else {
					$result = call_user_func(array($proxy, $this->name), $this->options);
				}
			} catch(HTTP_Request2_ConnectionException $http2_e) {
				return $this->throwExceptionErrorInJoomla($http2_e);
			} catch(ServiceException $se) {
				return $this->throwServiceExceptionErrorInJoomla($se);
			} catch (Exception $e) {
				return $this->throwExceptionErrorInJoomla($e);
			}

		} else {
			$message = $this->containerTestErrorMessage($this->container);
			return $this->throwJoomlaError($message);
		}

		return $this->processResults($result);
	}

	protected function containerTestErrorMessage($container) {
		return JText::sprintf(JText::_('COM_LOFT_J_AZURE_CONTAINER_DOES_NOT_EXIST_ERROR'), $container);
	}

	/**
	 *
	 * @param array $containers
	 *
	 * @return boolean
	 */
	protected function containerExists(Array $containers) {
		if (is_null($this->container)) {
			return false;
		}

		if (array_key_exists($this->container, $containers) === false) {
			return false;
		} else {
			return true;
		}
	}

	/**
	 * Method to capture Azure exception, fromat as string and pass to be
	 * enqued in Joomla's core alerts
	 *
	 * @param ServiceException $e
	 */
	protected function throwServiceExceptionErrorInJoomla(ServiceException $se) {
		$error_message = "Service Exception Error Code [" . $se->getCode() . "] : " . $se->getMessage();
		return $this->throwJoomlaError($error_message);
	}

	/**
	 * Method to capture Azure exception, fromat as string and pass to be
	 * enqued in Joomla's core alerts
	 *
	 * @param ServiceException $e
	 */
	protected function throwExceptionErrorInJoomla(Exception $e) {
		$error_message = "Exception Error Code [" . $e->getCode() . "] : " . $e->getMessage();
		return $this->throwJoomlaError($error_message);
	}

	/**
	 * Method to enque a passd in notification and notification level to
	 * Joomla core alerts
	 *
	 * @param string $msg
	 * @param string $type
	 *
	 * @return boolean
	 */
	protected function throwJoomlaError($msg, $type = 'Warning') {
		if (defined('LOFT_J_AZURE_CONFIGURE') && (int) LOFT_J_AZURE_CONFIGURE !== 1) {
			$this->response->message = $msg;
			$this->response->type = $type;
		}
		return false;
	}

	/**
	 * Takes a set of provided Azure PHP SDK options and custom Joomla options
	 * objects and uses getters to set all properties in Azure PHP SDK object
	 *
	 * @param object $azureOptions
	 * @param object $joomlaOptions
	 *
	 * @return object
	 */
	protected function loadAzureOptionsObject($azureOptions, $joomlaOptions) {
		foreach ($joomlaOptions as $key => $value) {
			$method = $this->generatePropertyNameFromKey($key);

			if (method_exists($azureOptions, $method)) {
				$azureOptions->$method($value);
			} else {
				$this->throwJoomlaError('Unable to set property ' . $key . ', method ' . $method . ' does not exist in options class');
			}
		}
		return $azureOptions;
	}

	/**
	 * Generates the proper method name string value.  Derived from passed in joomla
	 * options object with specific property naming convetion to faciliate this conversion
	 * and check.
	 *
	 * @param string $key
	 *
	 * @return string
	 */
	private function generatePropertyNameFromKey($key) {
		$parts = explode('_', $key);

		$name = "set";

		foreach ($parts as $part) {
			$name .= ucfirst($part);
		}

		return $name;
	}
}