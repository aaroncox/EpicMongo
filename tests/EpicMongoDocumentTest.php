<?php
/**
 * undocumented class
 *
 * @package default
 * @author Aaron Cox
 **/
class EpicMongoDocumentTest extends PHPUnit_Framework_TestCase
{
	public function testEpicMongoDocumentExists()
	{
		$this->assertTrue(class_exists('Epic_Mongo_Document'));
	}

	public function testDefault() {
		$doc = new Epic_Mongo_Document(array('key' => 'value'));
		$this->assertEquals('value', $doc->getProperty('key'));
		$this->assertEquals(null, $doc->getProperty('empty'));
	}

	public function testProperties() {
		$doc = new Epic_Mongo_Document();
		$this->assertFalse($doc->hasProperty('key'));
		$doc->setProperty('key', 'value');
		$this->assertTrue($doc->hasProperty('key'));
		$this->assertEquals('value', $doc->getProperty('key'));
		$this->assertEquals(array('key'), $doc->getPropertyKeys());
	}

	public function testArrayAccess() {
		$doc = new Epic_Mongo_Document();
		$this->assertFalse(isset($doc['key']));
		$doc['key'] = 'value';
		$this->assertTrue(isset($doc['key']));
		$this->assertEquals('value', $doc['key']);
		unset($doc['key']);
		$this->assertFalse(isset($doc['key']));
	}

	public function testObjectAccess() {
		$doc = new Epic_Mongo_Document;
		$this->assertEquals(null, $doc->key);
		$doc->key = 'value';
		$this->assertEquals('value', $doc->key);
		unset($doc->key);
		$this->assertFalse(isset($doc->key));
	}

	public function testCountable() {
		$doc = new Epic_Mongo_Document();
		$doc->setProperty('key', 'value');
		$this->assertEquals(1, count($doc));
		$doc->setProperty('key2', 'value2');
		$this->assertEquals(2, count($doc));
	}

	public function testIterator() {
		$data = array(
			'k1' => 'v1',
			'k2' => 'v2',
			'k3' => 'v3',
		);
		$doc = new Epic_Mongo_Document($data);
		$count = 0;
		foreach($doc as $k => $v) {
			$count++;
			$this->assertEquals($data[$k], $v, "Data matched for key $k");
		}
		$this->assertEquals(3, $count, "Iterated over all three keys");
		$iterator = $doc->getIterator();
		$iterator->seek('k3');
		$this->assertEquals('v3', $iterator->current());
	}

	public function testDirtyDataIterator()
	{
		$cleanData = array(
			'k1' => 'v1',
			'k2' => 'v2',
			'k3' => 'v3'
		);
		$doc = new Epic_Mongo_Document($cleanData);
		$doc->k1 = null;
		$doc->k2 = 'v4';
		$doc->k5 = 'v5';
		$expected = array(
			'k2' => 'v4',
			'k3' => 'v3',
			'k5' => 'v5'
		);
		$count = 0;
		foreach($doc as $k => $v) {
			$count++;
			$this->assertEquals($expected[$k], $v, "Data matched for key $k");
		}
		$this->assertEquals(3, count($expected), "Iterated over all expected keys");

	}

	public function testRecursiveIterator() {
		$data = array(
			'k1' => new Epic_Mongo_Document(array('test' => 'value')),
		);
		$doc = new Epic_Mongo_Document($data);
		$iterator = $doc->getIterator();
		$iterator->seek('k1');
		$this->assertTrue($iterator->hasChildren());
		$children = $iterator->getChildren();
		$this->assertInstanceOf('Epic_Mongo_Iterator_Document', $children);
		$children->seek('test');
		$this->assertEquals('value', $children->current());
	}

	public function testCollectionMethods()
	{
		$schema = new Test_Document_Mongo_Schema();
		$cursor = $schema->resolve('test')->find();
		$this->assertInstanceOf("Epic_Mongo_Iterator_Cursor", $cursor);
	}

	/**
	 * @expectedException OutOfBoundsException
	 */
	public function testIteratorException() {
		$doc = new Epic_Mongo_Document();
		$doc->getIterator()->seek('test');
	}

	public function testDocumentSetDetection()
	{
		$data = array(
			'set' => array(
				'1', '2', '3',
				array(
					'test' => 'test'
				)
			)
		);
		$doc = new Epic_Mongo_Document($data);
		$this->assertInstanceOf("Epic_Mongo_DocumentSet", $doc->set);
		$this->assertEquals("2", $doc->set[1]);
		$this->assertEquals("test", $doc->set[3]->test);
	}

	/**
	 * @expectedException Epic_Mongo_Exception
	 */
	public function testCreateReferenceException() {
		$doc = new Epic_Mongo_Document();
		$doc->createReference();
	}

	public function testExport()
	{
		$data = array(
			'test' => true,
			'testInner' => array(
				'tested' => true
			),
			'testNull' => null,
			'testEmptyDoc' => array(),
			'testArrayLike' => array(
				'1', '2', '3'
			)
		);
		$doc = new Epic_Mongo_Document($data);

		$exported = $doc->export();
		unset($data['testNull']);
		$data['testEmptyDoc'] = null;
		$this->assertEquals($data, $exported);
	}

	public function testExportReferences()
	{
		$test = array(
			'test' => true
		);
		$data = array(
			'testDoc' => $test,
			'testArray' => array(
				'test' => 'test'
			),
			'testSet' => array(
				$test
			),
			'testMulti' => $test
		);
		$doc = new Test_Document_Requirements_Document($data);
		$export = $doc->export();
		$this->assertEquals("test", $export["testArray"]["test"]);
	}

	public function testRequirements()
	{
		$doc = new Test_Document_Requirements_Document;
		$this->assertFalse($doc->hasRequirement('test','required'));
		$this->assertTrue($doc->hasRequirement('testDoc','doc'));
		$this->assertTrue($doc->getRequirement('testDoc','doc') === "Test_Document_Mongo_Document");
		$this->assertFalse($doc->hasRequirement('testDoc','required'));
		$this->assertTrue($doc->hasRequirement('testLong','long'));
		$this->assertTrue($doc->getRequirement('testLong','long'));
		$this->assertFalse($doc->getRequirement('testLong','doc'));
		$this->assertTrue($doc->hasRequirement('testFloat','float'));
		$this->assertTrue($doc->hasRequirement('testMulti','doc'));
		$this->assertTrue($doc->hasRequirement('testMulti','required'));
		$this->assertTrue($doc->hasRequirement('testMulti','ref'));
		$this->assertTrue(is_array($doc->testArray));
		$this->assertInstanceOf('Test_Document_Mongo_Document', $doc->testMulti);

		$docExtend = new Test_Document_Requirements_Document(array(),array(
			'requirements' => array(
				'testExtend' => 'doc'
			)
		));

		$this->assertTrue($docExtend->hasRequirement('testSet','set'));
		$this->assertTrue($docExtend->getRequirement('testSet','set') === "Epic_Mongo_DocumentSet");
		$this->assertInstanceOf('Epic_Mongo_DocumentSet', $docExtend->testSet);
		$this->assertTrue($docExtend->hasRequirement('testExtend','doc'));
	}
} // END class EpicMongoDocumentTest extends PHPUnit_Framework_TestCase

class Test_Document_Requirements_Document extends Epic_Mongo_Document{
	protected $_requirements = array(
		'testDoc' => 'doc:Test_Document_Mongo_Document',
		'testSet' => array('set', 'required'),
		'testSet.$' => 'doc:Test_Document_Mongo_Document',
		'testRequired' => 'required',
		'testArray' => 'array',
		'testLong' => 'long',
		'testFloat' => 'float',
		'testMulti' => array('doc:Test_Document_Mongo_Document', 'required', 'ref'),
	);
}

class Test_Document_Mongo_Schema extends Epic_Mongo_Schema {
	protected $_typeMap = array(
		'test' => 'Test_Document_Mongo_Document',
	);
	public function init() {
		$this->_db = MongoDb_TestHarness::getInstance()->dbName;
	}
}

class Test_Document_Mongo_Document extends Epic_Mongo_Document {
	protected $_collection = 'test';
}