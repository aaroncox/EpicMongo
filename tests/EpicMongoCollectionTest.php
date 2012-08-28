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

	public function testAddSchema() {
		Epic_Mongo::addSchema('testCollection', new Collection_Mongo_Schema);
		$this->assertInstanceOf('Epic_Mongo_Schema', Epic_Mongo::getSchema('testCollection'));
	}

	/**
	 * @depends testAddSchema
	 **/
	public function testGetCollection() {
		$this->assertInstanceOf('Epic_Mongo_Collection', Epic_Mongo::testCollection('test'));
		$this->assertEquals(Epic_Mongo::testCollection(), Epic_Mongo::testCollection('test')->getSchema());
	}

	public function testIsDocumentClass() {
		$this->assertFalse(Collection_Mongo_Collection::isDocumentClass());
	}

	/**
	 * @expectedException Epic_Mongo_Exception
	 */

	public function testRequiredSchema()
	{
		$collection = new Collection_Mongo_Collection;
		$collection->getSchema();
	}

	public function testFind() {
		$this->assertInstanceOf('MongoCursor', Epic_Mongo::testCollection('test')->find());
	}
} // END class EpicMongoCollectionTest extends PHPUnit_Framework_TestCase



class Collection_Mongo_Schema extends Epic_Mongo_Schema {
	protected $_typeMap = array(
		'test' => 'Collection_Mongo_Collection',
	);
	public function init() {
		$this->_db = 'test_'.time();
	}
	public function __destroy() {
		$this->getMongoDb()->command(array("dropDatabase" => 1));
	}
}

class Collection_Mongo_Collection extends Epic_Mongo_Collection {
	protected $_collection = 'test';
}