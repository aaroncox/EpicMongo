<?php
/**
 * undocumented class
 *
 * @package default
 * @author Aaron Cox
 **/
class EpicMongoCollectionTest extends PHPUnit_Framework_TestCase
{
	/**
	 * testExists
	 *
	 * @return void
	 * @author Aaron Cox
	 **/
	public function testExists()
	{
		$this->assertTrue(class_exists('Epic_Mongo_Collection'));
	}

} // END class EpicMongoCollectionTest extends PHPUnit_Framework_TestCase