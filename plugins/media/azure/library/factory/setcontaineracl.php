<?php
// no direct access
defined('_JEXEC') or die;

JLoader::register('JAzureQuery', JPATH_PLUGINS . '/media/azure/library/factory/jazurequery.php');


use WindowsAzure\Blob\Models\CreateContainerOptions;
use WindowsAzure\Blob\Models\PublicAccessType;
use WindowsAzure\Blob\Models\GetContainerAclResult;
use WindowsAzure\Blob\Models\ContainerAcl;

/**
 * GetContainerACL Class
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
class SetContainerAcl extends JAzureQuery
{
	/**
	 * Name of remote procedure call this object represents.
	 *
	 * @var string
	 */
	protected $name = 'setContainerAcl';

	/**
	 *
	 * @var string
	 */
	protected $container;

	/**
	 *
	 * @var string
	 */
	protected $acl;

	/**
	 * Constructor method
	 *
	 * @param array/stdClass $options
	 * @param string $container
	 */
	public function __construct($container, $acl, $options) {
		$this->container = $container;
		$container_acl = new ContainerAcl();
		$this->acl = $container_acl->create($acl, array());
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
		return array($this->container, $this->acl, $options);
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
