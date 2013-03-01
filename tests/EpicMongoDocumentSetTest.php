<?php
/**
 * undocumented class
 *
 * @package default
 * @author Aaron Cox
 **/
class EpicMongoDocumentSetTest extends PHPUnit_Framework_TestCase
{
	public function testEpicMongoDocumentSetExists()
	{
		$this->assertTrue(class_exists('Epic_Mongo_DocumentSet'));
	}

	public function testIsDocumentClass()
	{
		$this->assertTrue(Epic_Mongo_DocumentSet::isDocumentClass());
	}

	/**
	 * @expectedException Epic_Mongo_Exception
	 */
	public function testSetPropertyException()
	{
		$set = new Epic_Mongo_DocumentSet();
		$doc = new Epic_Mongo_Document();
		$set->setProperty('test', $doc);
	}

	/**
	 * @expectedException Epic_Mongo_Exception
	 */
	public function testSetPropertyException2()
	{
		$set = new Epic_Mongo_DocumentSet();
		$set->setProperty(0,"test");
	}

	public function testDoc()
	{
		$schema = new Test_DocumentSet_Schema;
		$set = $schema->resolve('set:test');
		$this->assertInstanceOf('Test_DocumentSet_DocumentSet', $set);
		$doc = $set->doc();
		$this->assertInstanceOf('Test_DocumentSet_Document', $doc);
		$doc = $set->doc(5);
		$this->assertInstanceOf('Test_DocumentSet_Document', $doc);
	}

	public function testSetPush()
	{
		$set = new Epic_Mongo_DocumentSet();
		$doc = new Epic_Mongo_Document(array("test"=>"test"));
		$doc2 = new Epic_Mongo_Document(array("test"=>"test"));
		$set->setProperty(null, $doc);
		$set->setProperty(null, $doc2);
		$this->assertEquals($doc, $set[0]);
		$this->assertEquals($doc2, $set[1]);
		$set->setProperty(0, null);
		$set->setProperty(null, $doc);
		$this->assertEquals($doc, $set[2]);
	}
}

class Test_DocumentSet_DocumentSet extends Epic_Mongo_DocumentSet {
	protected $_requirements = array(
		'$' => array('doc:test')
	);
}

class Test_DocumentSet_Document extends Epic_Mongo_DocumentSet {
	protected $_requirements = array(
		'set' => array('set:test', 'auto')
	);
}

class Test_DocumentSet_Schema extends Epic_Mongo_Schema {
	protected $_typeMap = array(
		'test' => 'Test_DocumentSet_Document',
		'set:test' => 'Test_DocumentSet_DocumentSet',
	);
}