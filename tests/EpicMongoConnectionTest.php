<?php
/**
 * undocumented class
 *
 * @package default
 * @author Aaron Cox
 **/
class EpicMongoConnectionTest extends PHPUnit_Framework_TestCase
{

	public function testExists()
	{
		$this->assertTrue(class_exists('Epic_Mongo_Connection'));
	}

	public function testGetDefaultConnection() {
		$connection = Epic_Mongo::getConnection();
		$info = $connection->getConnectionInfo();
		$this->assertEquals(TEST_CONNECTION_STRING, $info['connectionString']);
		$this->assertInstanceOf('Mongo', $connection);
	}
	
	/**
   * @expectedException Epic_Mongo_Exception
   */
	public function testAddConnectionException() {
		Epic_Mongo::addConnection('default');
	}

	/**
   * @expectedException Epic_Mongo_Exception
   */
	public function testGetConnectionException() {
		Epic_Mongo::getConnection('doesnt_exist');
	}
	
} // END class EpicMongoConnectionTest extends PHPUnit_Framework_TestCase