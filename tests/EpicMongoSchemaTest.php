<?php
/**
 * undocumented class
 *
 * @package default
 * @author Aaron Cox
 **/
class EpicMongoSchemaTest extends PHPUnit_Framework_TestCase
{
	/**
	 * testEpicMongoSchemaExists
	 *
	 * @return void
	 * @author Aaron Cox
	 **/
	public function testEpicMongoSchemaExists()
	{
		$this->assertTrue(class_exists('Epic_Mongo_Schema'));
	}
		
	public function testMap() {
		$this->assertInstanceOf('Epic_Mongo_Map', Test_Schema::map());
	}
	
	public function testSchemaInstance() {
		$schema = new Test_Schema;
		$schema2 = new Test2_Schema;
		$schema3 = new Test3_Schema;
		$this->assertEquals('epic_mongo_test', $schema->getDb());
		$this->assertEquals('epic_mongo_test2', $schema3->getDb());
		// Make sure it's default (by default)
		$this->assertEquals('default', $schema->getConnection());
		// Check to make sure schema2 is 'test'
		$this->assertEquals('test', $schema2->getConnection());
		// Go back and still make sure it's default 
		$this->assertEquals('default', $schema->getConnection());
		// Make sure schema3 extends from schema2
		$this->assertEquals('test', $schema3->getConnection());
	}
	
	public function testSchemaConnection() {
		$schema = new Test_Schema;
		$connection = $schema->getMongoDb();
		$this->assertInstanceOf('MongoDB', $connection);	
		echo $connection."";	
	}
	
	/**
	 * @expectedException Epic_Mongo_Exception
	 */
	public function testRequiredDbException() {
		$schema = new Test4_Schema;
		$schema->getDb();
	}
	
	public function testMapArray() {
		$this->assertEquals('Test_Mongo_User', Test_Schema::map()->getClass('user'));
		$this->assertEquals('Test_Mongo_Post', Test_Schema::map()->getClass('post'));
	}

	public function testMapArrayExtend() {
		$this->assertEquals('Test2_Mongo_User', Test2_Schema::map()->getClass('user'));
		$this->assertEquals('Test_Mongo_Post', Test2_Schema::map()->getClass('post'));
		$this->assertEquals('Test2_Mongo_User', Test_Schema::map()->getClass('user'));
	}
	
} // END class EpicMongoTest extends PHPUnit_Framework_TestCase

class Test_Mongo_User extends Epic_Mongo_Document {}
class Test2_Mongo_User extends Test_Mongo_User {}
class Test_Mongo_Post extends Epic_Mongo_Document {}

class Test_Schema extends Epic_Mongo_Schema {
	protected $_db = 'epic_mongo_test';
	protected static $_typeMap = array(
		'user' => 'Test_Mongo_User',
		'post' => 'Test_Mongo_Post',
	);
}

class Test2_Schema extends Epic_Mongo_Schema {
	protected $_db = 'epic_mongo_test2';
	protected static $_extends = 'Test_Schema';
	protected $_connection = 'test';
	protected static $_typeMap = array(
		'user' => 'Test2_Mongo_User'
	);
}

class Test3_Schema extends Epic_Mongo_Schema {
	protected static $_extends = 'Test2_Schema';
}

class Test4_Schema extends Epic_Mongo_Schema {}