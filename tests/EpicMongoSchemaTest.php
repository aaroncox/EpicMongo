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
		$schema = new Schema_Mongo_Schema;
		$this->assertInstanceOf('Epic_Mongo_Map', $schema->map());
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
	}
	
	/**
	 * @expectedException Epic_Mongo_Exception
	 */
	public function testRequiredDbException() {
		$schema = new Schema_Mongo_Schema_NoExtend;
		$schema->getDb();
	}
	
	public function testMapArray() {
		$schema = new Schema_Mongo_Schema;
		$this->assertEquals('Schema_Mongo_User', $schema->map()->getClass('user'));
		$this->assertEquals('Schema_Mongo_Post', $schema->map()->getClass('post'));
	}

	public function testMapArrayExtend() {
		$schema1 = new Schema_Mongo_Schema;
		$schema2 = new Schema_Mongo_Schema_Extend;
		$this->assertEquals('Schema_Mongo_User', $schema1->map()->getClass('user'));
		$this->assertEquals('Schema_Mongo_User_Extend', $schema2->map()->getClass('user'));
		$this->assertEquals('Schema_Mongo_Post', $schema2->map()->getClass('post'));
	}
	
	public function testNewDocument() {
		$schema = new Schema_Mongo_Schema;
		$doc = $schema->resolve("doc:user");
		$doc2 = $schema->resolve("doc:user", array('test' => 'test'));
		$set = $schema->resolve("set:user");
		$this->assertInstanceOf('Schema_Mongo_User', $doc);
		$this->assertEquals('test', $doc2->test);
		$this->assertFalse($doc === $doc2, 'Ensure documents are different.');
		$this->assertInstanceOf('Epic_Mongo_DocumentSet', $set);
		$this->assertEquals("user", $set->getRequirement("$","doc"));

		$static = $schema->resolve("user");
		$this->assertFalse($static === $doc);
		$this->assertFalse($static === $doc2);
		$this->assertInstanceOf('Schema_Mongo_Document', $schema->resolve('doc:collection'));
	}
	
	/**
	 * @expectedException Epic_Mongo_Exception
	 */
	public function testUnknownResolveTypeException() {
		$schema = new Schema_Mongo_Schema;
		$schema->resolve("bad:user");
	}

	/**
	 * @expectedException Epic_Mongo_Exception
	 */
	public function testBadTypeException() {
		$schema = new Schema_Mongo_Schema;
		$schema->resolve("doc:bad");
	}
	
} // END class EpicMongoTest extends PHPUnit_Framework_TestCase

class Schema_Mongo_Post extends Epic_Mongo_Document {}
class Schema_Mongo_User extends Epic_Mongo_Document {}
class Schema_Mongo_User_Extend extends Schema_Mongo_User {}
class Schema_Mongo_Collection extends Epic_Mongo_Collection {}
class Schema_Mongo_Document extends Epic_Mongo_Document {}

class Schema_Mongo_Schema extends Epic_Mongo_Schema {
	protected $_db = 'epic_mongo_test';
	protected $_typeMap = array(
		'user' => 'Schema_Mongo_User',
		'post' => 'Schema_Mongo_Post',
		'collection' => 'Schema_Mongo_Collection',
		'doc:collection' => 'Schema_Mongo_Document',
	);
}

class Schema_Mongo_Schema_Extend extends Epic_Mongo_Schema {
	protected $_db = 'epic_mongo_test2';
	protected $_extends = 'Schema_Mongo_Schema';
	protected $_connection = 'test';
	protected $_typeMap = array(
		'user' => 'Schema_Mongo_User_Extend'
	);
}

class Schema_Mongo_Schema_Extend_Extend extends Epic_Mongo_Schema {
	protected $_extends = 'Schema_Mongo_Schema_Extend';
}

class Schema_Mongo_Schema_NoExtend extends Epic_Mongo_Schema {
	
}