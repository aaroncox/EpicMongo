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
		$this->assertInstanceOf('Epic_Mongo_Map', Schema_Mongo_Schema::map());
	}
	
	public function testSchemaInstance() {
		$schema = new Schema_Mongo_Schema;
		$schema2 = new Schema_Mongo_Schema_Extend;
		$schema3 = new Schema_Mongo_Schema_Extend_Extend;
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
		$schema = new Schema_Mongo_Schema;
		$connection = $schema->getMongoDb();
		$this->assertInstanceOf('MongoDB', $connection);	
		echo $connection."";	
	}
	
	/**
	 * @expectedException Epic_Mongo_Exception
	 */
	public function testRequiredDbException() {
		$schema = new Schema_Mongo_Schema_NoExtend;
		$schema->getDb();
	}
	
	public function testMapArray() {
		$this->assertEquals('Schema_Mongo_User', Schema_Mongo_Schema::map()->getClass('user'));
		$this->assertEquals('Schema_Mongo_Post', Schema_Mongo_Schema::map()->getClass('post'));
	}

	public function testMapArrayExtend() {
		$this->assertEquals('Schema_Mongo_User_Extend', Schema_Mongo_Schema_Extend::map()->getClass('user'));
		$this->assertEquals('Schema_Mongo_Post', Schema_Mongo_Schema_Extend::map()->getClass('post'));
		$this->assertEquals('Schema_Mongo_User_Extend', Schema_Mongo_Schema::map()->getClass('user'));
	}
	
} // END class EpicMongoTest extends PHPUnit_Framework_TestCase

class Schema_Mongo_Post extends Epic_Mongo_Document {}
class Schema_Mongo_User extends Epic_Mongo_Document {}
class Schema_Mongo_User_Extend extends Schema_Mongo_User {}

class Schema_Mongo_Schema extends Epic_Mongo_Schema {
	protected $_db = 'epic_mongo_test';
	protected static $_typeMap = array(
		'user' => 'Schema_Mongo_User',
		'post' => 'Schema_Mongo_Post',
	);
}

class Schema_Mongo_Schema_Extend extends Epic_Mongo_Schema {
	protected $_db = 'epic_mongo_test2';
	protected static $_extends = 'Schema_Mongo_Schema';
	protected $_connection = 'test';
	protected static $_typeMap = array(
		'user' => 'Schema_Mongo_User_Extend'
	);
}

class Schema_Mongo_Schema_Extend_Extend extends Epic_Mongo_Schema {
	protected static $_extends = 'Schema_Mongo_Schema_Extend';
}

class Schema_Mongo_Schema_NoExtend extends Epic_Mongo_Schema {
	
}