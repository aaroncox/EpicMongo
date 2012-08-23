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
	
	/**
	 * @expectedException Epic_Mongo_Exception
	 */
	public function testGetSchemaException() {
		Epic_Mongo::getSchema('doesnt-exist');
	}
	/**
	 * @expectedException Epic_Mongo_Exception
	 */
	public function testAddSchemaException() {
		$schema = new Mongo_Mongo_Schema;
		Epic_Mongo::addSchema('dupe', $schema);
		Epic_Mongo::addSchema('dupe', $schema);		
	}
	
	public function testGetSchema() {
		$schema = new Mongo_Mongo_Schema;
		Epic_Mongo::addSchema('test', $schema);
		$this->assertInstanceOf('Epic_Mongo_Schema', Epic_Mongo::getSchema('test'));
	}

	public function testMagicMethod() {
		$schema = new Mongo_Mongo_Schema;
		Epic_Mongo::addSchema('magic', $schema);
		$this->assertInstanceOf('Mongo_Mongo_Schema', Epic_Mongo::magic());
		$this->assertInstanceOf('Mongo_Mongo_Document_Test', Epic_Mongo::magic('test'));
	}
	
	// public function testEpicMongoConnect() 
	// {
	// 	$this->assertTrue();
	// }
} // END class EpicMongoTest extends PHPUnit_Framework_TestCase

class Mongo_Mongo_Schema extends Epic_Mongo_Schema {
	protected $_typeMap = array(
		'test' => 'Mongo_Mongo_Document_Test'
	);
}

class Mongo_Mongo_Document_Test extends Epic_Mongo_Document {
	
}