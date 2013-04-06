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

	public function testIsDocumentClass()
	{
		$this->assertTrue(Epic_Mongo_Document::isDocumentClass());
	}

	public function testDefault() {
		$doc = new Epic_Mongo_Document(array('key' => 'value'));
		$this->assertEquals('value', $doc->getProperty('key'));
		$this->assertEquals(null, $doc->getProperty('empty'));
	}

	public function testConfig() {
		$doc = new Epic_Mongo_Document(array(),array('test'=>true));
		$this->assertTrue($doc->getConfig('test'));
		$this->assertTrue(is_array($doc->getConfig()));
		$this->assertTrue(is_null($doc->getConfig('notset')));
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

	public function testExtend()
	{
		$schema = new Test_Document_Mongo_Schema;
		$doc = $schema->resolve('doc');
		$doc->extend(array(
			'test' => true,
			'testArray' => array(
				array(
					'test' => true
				),
				array(
					'test' => true
				)
			)
		));
		$this->assertTrue($doc->test);
		$this->assertTrue($doc->testArray[0]->test);
		$this->assertTrue($doc->testArray[1]->test);
		$doc->extend(array(
			'test' => false,
			'testArray' => array(
				null,
				array(
					'test' => false
				),
				array(
					'test' => true
				)
			),
			'omg' => 'testing',
		));
		$this->assertFalse($doc->test);
		$this->assertTrue($doc->testArray[0]->test);
		$this->assertFalse($doc->testArray[1]->test);
		$this->assertTrue($doc->testArray[2]->test);
		
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
		$schema = new Test_Document_Mongo_Schema();
		$doc = $schema->resolve("doc", $data);
		$this->assertInstanceOf("Epic_Mongo_DocumentSet", $doc->set);
		$this->assertEquals("2", $doc->set[1]);
		$this->assertEquals("test", $doc->set[3]->test);
	}

	public function testIsEmpty()
	{
		$doc = new Epic_Mongo_Document();
		$this->assertTrue($doc->isEmpty());
		$doc->test = new Epic_Mongo_Document();
		$this->assertTrue($doc->isEmpty());
		$doc->test2 = true;
		$this->assertFalse($doc->isEmpty());
		$this->assertTrue($doc->test->isEmpty());
		$doc->test2 = null;
		$this->assertTrue($doc->isEmpty());
		$doc->test->test2 = true;
		$this->assertFalse($doc->isEmpty());
		$this->assertFalse($doc->test->isEmpty());

	}

	/**
	 * @expectedException Epic_Mongo_Exception
	 */
	public function testCreateReferenceException() {
		$doc = new Epic_Mongo_Document();
		$doc->createReference();
	}

	/**
	 * @expectedException Epic_Mongo_Exception
	 */
	public function testCreateReferenceException2() {
		$doc = new Epic_Mongo_Document(array(),array(
			'collection' => 'test_document',
			'pathToDocument' => 'test'
		));
		$doc->createReference();
	}

	public function testSave()
	{
		$schema = new Test_Document_Mongo_Schema;
		$doc = $schema->resolve('doc:test');
		$doc->testSave = true;
		$doc->save();

		$test = $schema->resolve('test')->findOne(array('testSave'=>true));
		$this->assertEquals($doc->_id, $test->_id);

		$doc->testSave = null;
		$doc->testMagic = new Epic_Mongo_Document();
		$doc->testMagic->test = true;
		$doc->save();

		$test = $schema->resolve('test')->findOne(array('_id'=>$doc->_id));
		$this->assertTrue($test->testMagic->test);
		$this->assertTrue(is_null($test->testSave));

		$doc->testMagic->test2 = true;
		$doc->save();
		$test = $schema->resolve('test')->findOne(array('_id'=>$doc->_id));
		$this->assertTrue($test->testMagic->test2);
		

		$doc->testSet = $schema->resolve('set');
		$testSetDoc = $schema->resolve('doc:doc');
		$testSetDoc->testSetDoc = true;
		$doc->testSet->setProperty(null,$testSetDoc);
		// should push
		$doc->testSet[0]->save();

		$test = $schema->resolve('test')->findOne(array('_id'=>$doc->_id));
		$this->assertTrue($test->testSet[0]->testSetDoc);

		// test save again to catch the early release line of code
		$test->save();
	}

	public function testSaveReferences() {
		$schema = new Test_Document_Mongo_Schema;
		$ref = $schema->resolve('doc:test');
		$ref->testSaveReferences = true;
		$ref->save();

		$doc = $schema->resolve('doc:testRequirements');
		$doc->testSet[] = $ref;
		$doc->save();

		$export = $doc->export();
		$this->assertTrue(MongoDbRef::isRef($export['testSet'][0]));
	}
	
	public function testOverwriteDocumentSet() {
		$schema = new Test_Document_Mongo_Schema;
		// Create the Document we're going to be embedding things on
		$doc = $schema->resolve('doc:testEmbed');
		// Create Embedded Document #1
		$embed1 = $schema->resolve('doc:testEmbedded');
		$embed1->value = 1;
		// Create Embedded Document #2
		$embed2 = $schema->resolve('doc:testEmbedded');
		$embed2->value = 2;
		// Save the 1st embedded document and save
		$doc->test = $embed1;
		$doc->save();
		// Save the 2nd embedded document and save
		$doc->test = $embed2;
		$doc->save();
		// Attempt to load the Document from the collection to see what's saved on it
		$loaded = $schema->resolve('testEmbed')->findOne(array('_id' => $doc->_id));
		$this->assertEquals($loaded->test->_id, $doc->test->_id);
		$this->assertEquals($loaded->test->value, $doc->test->value);
		$this->assertEquals($loaded->test->value, 2);
	}

	/**
	 * @expectedException Epic_Mongo_Exception
	 */
	public function testSaveException() {
		$schema = new Test_Document_Mongo_Schema;
		$doc = $schema->resolve('doc:doc');
		// if you set a collection after initialization, criteria will still be empty
		$this->assertFalse($doc->hasCollection());
		$this->assertFalse($doc->hasKey());
		$this->assertTrue(is_null($doc->_id));
		$doc->setCollection('test_document');
		$doc->test = true;
		$this->assertEquals(array(),$doc->getCriteria());
		$this->assertTrue(is_null($doc->_id));
		$doc->save();
	}

	/**
	 * @expectedException Epic_Mongo_Exception
	 */
	public function testSaveException2() {
		$schema = new Test_Document_Mongo_Schema;
		$doc = $schema->resolve('doc:test');
		$doc->save();
		$doc->addOperation('$notAnOp','test',true);
		$doc->save();
	}
	
	/**
	 * @expectedException Epic_Mongo_Exception
	 */
	public function testNoDataException() {
		new Epic_Mongo_Document(null);
	}
	
	public function testDeleteDocument() {
		$schema = new Test_Document_Mongo_Schema;
		// Create and Save
		$doc = $schema->resolve('doc:test');
		$doc->save();
		// Delete it
		$doc->delete();
	}
	
	public function testDeleteEmbeddedDocument() {
		$schema = new Test_Document_Mongo_Schema;
		// Create and Save
		$doc = $schema->resolve('doc:test');
		// Create the Embedded
		$doc->embed = $schema->resolve('doc:test');
		$doc->save();
		// Delete it
		$doc->embed->delete();
	}

	/**
	 * @expectedException Epic_Mongo_Exception
	 */
	public function testDeleteNoCollectionDocument() {
		$schema = new Test_Document_Mongo_Schema;
		// Create and Save
		$doc = $schema->resolve('doc:testNoCollection');
		// Delete it
		$doc->delete();
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
		$schema = new Test_Document_Mongo_Schema;
		$doc = $schema->resolve("doc", $data);

		$exported = $doc->export();
		unset($data['testNull']);
		$data['testEmptyDoc'] = null;
		$this->assertEquals($data, $exported);
	}

	public function testDoc()
	{
		$schema = new Test_Document_Mongo_Schema;
		$doc = $schema->resolve("doc");
		$test = $doc->doc('test',array('test'=>true));
		$this->assertInstanceOf("Epic_Mongo_Document", $test);
		$this->assertTrue($test->test);
		$this->assertSame($test, $doc->doc('test', array('heh'=>true)));
		$this->assertTrue($test->heh);
	}

	public function testExportReferences()
	{
		$schema = new Test_Document_Mongo_Schema;
		$test = array(
			'test' => true
		);
		$testRef = $schema->resolve('doc:test');
		$testRef->test = true;
		$testRef->save();
		$brokenRef = array(
			'$ref' => 'test_document',
			'$id' => 'non_exist'
		);
		$testRef = $testRef->createReference();
		$data = array(
			'testDoc' => $test,
			'testArray' => array(
				'test' => 'test'
			),
			'testSet' => array(
				$testRef,
				$brokenRef
			),
			'testProp' => $testRef,
			'testMulti' => $brokenRef 
		);
		$doc = $schema->resolve('doc:testRequirements', $data);
		$this->assertTrue($doc->testSet[0]->isRootDocument());
		$this->assertEquals(null, $doc->testSet[1]);
		$this->assertEquals("clean", $doc->isReference('testProp'));
		$this->assertInstanceOf('Epic_Mongo_Document', $doc->testProp);
		$this->assertTrue($doc->testProp->isRootDocument());
		$this->assertEquals("data", $doc->isReference('testProp'));
		$this->assertInstanceOf('Epic_Mongo_Document', $doc->testMulti);
		$export = $doc->export();
		$this->assertTrue(MongoDbRef::isRef($export['testSet'][0]));
		$this->assertEquals("test", $export["testArray"]["test"]);
	}

	public function testIsReference()
	{
		$schema = new Test_Document_Mongo_Schema;
		$doc = $schema->resolve('doc:testRequirements');
		$this->assertInstanceOf('Test_Document_Mongo_Document', $doc->doc('testMulti'));
		$this->assertEquals("requirement", $doc->isReference('testMulti'));
	}

	public function testRequirements()
	{
		$schema = new Test_Document_Mongo_Schema;
		$doc = $schema->resolve("doc:testRequirements");
		$this->assertFalse($doc->hasRequirement('test','auto'));
		$this->assertTrue($doc->hasRequirement('testDoc','doc'));
		$this->assertEquals('test', $doc->getRequirement('testDoc','doc'));
		$this->assertFalse($doc->hasRequirement('testDoc','auto'));
		$this->assertTrue($doc->hasRequirement('testLong','long'));
		$this->assertTrue($doc->getRequirement('testLong','long'));
		$this->assertFalse($doc->getRequirement('testLong','doc'));
		$this->assertTrue($doc->hasRequirement('testFloat','float'));
		$this->assertTrue($doc->hasRequirement('testMulti','doc'));
		$this->assertTrue($doc->hasRequirement('testMulti','auto'));
		$this->assertTrue($doc->hasRequirement('testMulti','ref'));
		$this->assertInstanceOf('Epic_Mongo_DocumentSet', $doc->testSet);
		$this->assertTrue($doc->testSet->hasRequirement('$','doc'));
		$this->assertTrue(is_array($doc->testArray));
		$this->assertInstanceOf('Test_Document_Mongo_Document', $doc->testMulti);

		$docExtend = $schema->resolve('doc:testRequirements',array(),array(
			'requirements' => array(
				'testExtend' => 'doc'
			)
		));

		$this->assertTrue($docExtend->hasRequirement('testSet','set'));
		$this->assertEquals("",$docExtend->getRequirement('testSet','set'));
		$this->assertInstanceOf('Epic_Mongo_DocumentSet', $docExtend->testSet);
		$this->assertTrue($docExtend->hasRequirement('testExtend','doc'));

		$testReq = array('$' => array('doc'=>'test', 'ref'=>null));

		$this->assertEquals($testReq,$docExtend->getRequirements('testSet.'));
		$this->assertEquals($testReq,$docExtend->testSet->getRequirements());
	}
} // END class EpicMongoDocumentTest extends PHPUnit_Framework_TestCase

class Test_Document_Requirements_Document extends Test_Document_Mongo_Document {
	protected $_requirements = array(
		'testDoc' => 'doc:test',
		'testSet' => array('set', 'auto'),
		'testSet.$' => array('doc:test', 'ref'),
		'testRequired' => 'required',
		'testArray' => 'array',
		'testLong' => 'long',
		'testFloat' => 'float',
		'testMulti' => array('doc:test', 'auto', 'ref'),
		'testMulti.test' => 'required'
	);
}

class Test_Document_Mongo_Schema extends Epic_Mongo_Schema {
	protected $_typeMap = array(
		'test' => 'Test_Document_Mongo_Document',
		'testRequirements' => 'Test_Document_Requirements_Document',
		'testEmbed' => 'Test_Document_Mongo_Document_Embed',
		'testEmbedded' => 'Test_Document_Mongo_Document_Embedded',
		'testNoCollection' => 'Test_Document_Mongo_Document_NoCollection',
	);
	public function init() {
		$this->_db = MongoDb_TestHarness::getInstance()->dbName;
	}
}

class Test_Document_Mongo_Document extends Epic_Mongo_Document {
	protected $_collection = 'test_document';
}

class Test_Document_Mongo_Document_Embed extends Epic_Mongo_Document {
	protected $_collection = 'test_document';
	protected $_requirements = array(
		'test' => array('doc:testEmbedded')
	);
}
class Test_Document_Mongo_Document_Embedded extends Epic_Mongo_Document {
	protected $_collection = 'test_document';
}
class Test_Document_Mongo_Document_NoCollection extends Epic_Mongo_Document {
}
