<?php
/**
 * undocumented class
 *
 * @package default
 * @author Aaron Cox
 **/
class EpicMongoExceptionTest extends PHPUnit_Framework_TestCase
{
	/**
	 * testEpicMongoExceptionExists
	 *
	 * @return void
	 * @author Aaron Cox
	 **/
	public function testEpicMongoExceptionExists()
	{
		$this->assertTrue(class_exists('Epic_Mongo_Exception'));
	}

	public function testEpicMongoExceptionInstanceOf() {
		$exception = new Epic_Mongo_Exception;
		$this->assertInstanceOf('Exception', $exception);
		$this->assertInstanceOf('Epic_Mongo_Exception', $exception);
	}
} // END class EpicMongoTest extends PHPUnit_Framework_TestCase