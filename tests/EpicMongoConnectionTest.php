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
		$this->assertInstanceOf('MongoClient', $connection);
	}

	public function testDefaultConnection() {
		// Once it doesn't auto connect, remove this if block
		if(TEST_CONNECTION_STRING == '127.0.0.1') {
			$connection = Connect_Test_Epic_Mongo::getConnection();
			$info = $connection->getConnectionInfo();
			$this->assertEquals("127.0.0.1", $info['connectionString']);			
		}
	}

	/**
	 * @expectedException Epic_Mongo_Exception
	 */
	public function testAddConnectionException() {
		Epic_Mongo::addConnection('default');
	}

	public function testNewConnection()
	{
		// Once it doesn't auto connect, remove this if block
		if(TEST_CONNECTION_STRING == '127.0.0.1') {
			$connection = new Epic_Mongo_Connection;
			$info = $connection->getConnectionInfo();
			$this->assertEquals('127.0.0.1', $info['connectionString']);
			Connect_Test_Epic_Mongo::addConnection('test-new-connection', $connection);			
		}
	}

	public function testNewConnectionString()
	{
		Connect_Test_Epic_Mongo::addConnection('test-new-connection-string', TEST_CONNECTION_STRING);
		$info = Connect_Test_Epic_Mongo::getConnection('test-new-connection-string')->getConnectionInfo();
		$this->assertEquals(TEST_CONNECTION_STRING, $info['connectionString']);
	}


	/**
	 * @expectedException Epic_Mongo_Exception
	 */
	public function testGetConnectionException() {
		Epic_Mongo::getConnection('doesnt_exist');
	}

} // END class EpicMongoConnectionTest extends PHPUnit_Framework_TestCase

class Connect_Test_Epic_Mongo extends Epic_Mongo {
	protected static $_connections = array();
}