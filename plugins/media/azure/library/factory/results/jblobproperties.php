<?php
// no direct access
defined('_JEXEC') or die;

/**
 * JBlobProperties Class
 *
 * PHP Version 5.4
 *
 * @category	Library
 * @package		JAzure
 * @subpackage	JAzure.JBlobProperties
 * @author		Brian Bolli <brian.bolli@arctg.com>
 * @copyright	Copyright (C) 2014 Arc Technology Group, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later.
 * @link		http://www.arctg.com
 */
class JBlobProperties
{
	/**
	 * Name of blob
	 *
	 * @var string
	 */
	public $name;

	/**
	 * Url of blob
	 *
	 * @var string
	 */
	public $url;

	/**
	 * Last modified date of blob
	 *
	 * @var string
	 */
	public $last_modified;

	/**
	 * E-Tag of blob
	 *
	 * @var string
	 */
	public $e_tag;

	/**
	 * Content type of blob
	 *
	 * @var string
	 */
	public $content_type;

	/**
	 * Content lenght of blob
	 *
	 * @var string
	 */
	public $content_length;

	/**
	 * Content encoding of blob
	 *
	 * @var string
	 */
	public $content_encoding;

	/**
	 * Content lanugage of blob
	 *
	 * @var string
	 */
	public $content_language;

	/**
	 * Content MD5 of blob
	 *
	 * @var string
	 */
	public $content_mD5;

	/**
	 * Content range of blob
	 *
	 * @var string
	 */
	public $content_range;

	/**
	 * Cache control of blob
	 *
	 * @var string
	 */
	public $cache_control;

	/**
	 * Blob type
	 *
	 * @var string
	 */
	public $blob_type;

	/**
	 * Least status of blob
	 *
	 * @var string
	 */
	public $lease_status;

	/**
	 * Sequence Number of blob
	 *
	 * @var string
	 */
	public $sequence_number;

	/**
	 * Constructor to load properties from results object
	 *
	 * @param string $name
	 * @param string $url
	 * @param string $properties
	 *
	 * @return boolean
	 */
	public function __construct($name = '', $url = '', $properties = null) {
		$this->name = $name;
		$this->url = $url . $name;
		if (!is_null($properties)) {
			if ($properties instanceof WindowsAzure\Blob\Models\BlobProperties) {
				$this->last_modified = $properties->getLastModified()->format("Y-m-d H:i:s");
				$this->e_tag = $properties->getETag();
				$this->content_type = $properties->getContentType();
				$this->content_length = $properties->getContentLength();
				$this->content_encoding = $properties->getContentEncoding();
				$this->content_language = $properties->getContentLanguage();
				$this->content_mD5 = $properties->getContentMD5();
				$this->content_range = $properties->getContentRange();
				$this->cache_control = $properties->getCacheControl();
				$this->blob_type = $properties->getBlobType();
				$this->lease_status = $properties->getLeaseStatus();
				$this->sequence_number = $properties->getSequenceNumber();
			} else {
				return false;
			}
		}
	}
}