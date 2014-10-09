<?php
// no direct access
defined('_JEXEC') or die;

interface IQuery
{
	public function execute($connection, Array $containers);
}