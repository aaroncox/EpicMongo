<?php
/**
 * undocumented class
 *
 * @package default
 * @author Aaron Cox
 **/
class EpicMongoTest extends PHPUnit_Framework_TestCase
{
	/**
	 * testEpicMongoExists
	 *
	 * @return void
	 * @author Aaron Cox
	 **/
	public function testEpicMongoExists()
	{
		$this->assertTrue(class_exists('Epic_Mongo'));
	}
	
	// public function testEpicMongoConnect() 
	// {
	// 	$this->assertTrue();
	// }
} // END class EpicMongoTest extends PHPUnit_Framework_TestCase