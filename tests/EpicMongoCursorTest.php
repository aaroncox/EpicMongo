<?php
/**
 * undocumented class
 *
 * @package default
 * @author Aaron Cox
 **/
class EpicMongoIteratorCursorTest extends PHPUnit_Framework_TestCase
{
	protected $_harness = null;
	
	public function getHarness() {
		if($this->_harness) {
			return $this->_harness; 
		}
		return $this->_harness = MongoDb_TestHarness::getInstance();
	}
		/**
	 * testEpicMongoCursorTest
	 *
	 * @return void
	 * @author Aaron Cox
	 **/
	public function testEpicMongoIteratorCursorTest()
	{
		$this->assertTrue(class_exists('Epic_Mongo_Iterator_Cursor'));
	}

	/**
	 * @expectedException Epic_Mongo_Exception
	 */
	public function testGetSchemaException()
	{
		$db = $this->getHarness()->getMongoDb();
		$collection = $db->selectCollection('iteratortest');
		$cursor = $collection->find();
		$iterator = new Epic_Mongo_Iterator_Cursor($cursor, array('collection' => 'iteratortest'));
		$this->assertEquals('iteratortest',$iterator->getCollection());
		$iterator->getSchema();
	}

	public function testGetCollection()
	{
		$db = $this->getHarness()->getMongoDb();
		$collection = $db->selectCollection('iteratortest');
		$cursor = $collection->find();
		$iterator = new Epic_Mongo_Iterator_Cursor($cursor, array('collection' => 'iteratortest'));
		$this->assertEquals('iteratortest',$iterator->getCollection());
		$iterator = new Epic_Mongo_Iterator_Cursor($cursor, array());
		$this->assertEquals(null, $iterator->getCollection());
	}
		
	public function testIteratorCursor() {
		$db = $this->getHarness()->getMongoDb();
		$collection = $db->selectCollection('iteratortest');
		$collection->insert(array("name" => "Aaron Cox"), array('safe' => true));
		$collection->insert(array("name" => "Corey Frang"), array('safe' => true));
		$collection->insert(array("name" => "Jacob Frye"), array('safe' => true));
		$cursor = $collection->find();
		$iterator = new Epic_Mongo_Iterator_Cursor($cursor, array('collection' => 'iteratortest'));
		$this->assertEquals(3, $cursor->count());
		$this->assertEquals(3, $iterator->count());
		foreach($iterator as $k => $v) {
			$this->assertInstanceOf('Epic_Mongo_Document', $v);
			$this->assertEquals($k, $v->_id);
		}
	}
	
	public function testIteratorDocumentClass() {
		$db = $this->getHarness()->getMongoDb();
		$collection = $db->selectCollection('iteratordocumenttest');
		$collection->insert(array("name" => "Aaron Cox"), array('safe' => true));
		$cursor = $collection->find();
		$this->assertEquals(1, $cursor->count());
		$config = array(
			'collection' => 'iteratordocumenttest', 
			'documentClass' => 'Iterator_Mongo_Document_Test',
		);
		$iterator = new Epic_Mongo_Iterator_Cursor($cursor, $config);
		$iterator->rewind();
		$doc = $iterator->current();
		$this->assertInstanceOf('Iterator_Mongo_Document_Test', $doc);
	}
	
	public function testIteratorExport() {
		$db = $this->getHarness()->getMongoDb();
		$collection = $db->selectCollection('iteratorexporttest');
		$collection->insert(array("name" => "Aaron Cox"), array('safe' => true));
		$cursor = $collection->find();
		$config = array(
			'collection' => 'iteratorexporttest', 
		);
		$iterator = new Epic_Mongo_Iterator_Cursor($cursor, $config);
		$iterator->rewind();
		
		$export = $iterator->export();
		$this->assertEquals(1, count($export));
		$this->assertTrue(is_array($export));
		$doc = current($export);
		$this->assertEquals('Aaron Cox', $doc['name']);
		$this->assertTrue(is_array($doc));
	}
	
	protected $_numbers = null;
	
	public function getNumbersCollection() {
		if($this->_numbers) {
			return $this->numbers;
		}
		$db = $this->getHarness()->getMongoDb();
		$db->dropCollection('iteratorsnumber');
		$collection = $db->selectCollection('iteratorsnumber');
		for($i = 0; $i < 100; $i++ ) {
			$collection->insert(array("i" => $i), array('safe' => true));			
		}		
		$cursor = $collection->find();
		$this->assertEquals(100, $cursor->count());
		return $this->_numbers = $collection;
	}
		
	public function testIteratorSkip() {
		$collection = $this->getNumbersCollection();
		$cursor = $collection->find();
		$iterator = new Epic_Mongo_Iterator_Cursor($cursor, array());
		$iterator->skip(50);
		$iterator->rewind();
		$this->assertEquals(50, $iterator->current()->i);
	}
	
	public function testIteratorSort() {
		$collection = $this->getNumbersCollection();
		$cursor = $collection->find();
		$iterator = new Epic_Mongo_Iterator_Cursor($cursor, array());
		$iterator->sort(array('i' => -1));
		$iterator->rewind();
		$this->assertEquals(99, $iterator->current()->i);
	}
	
	public function testDocumentNull() {
		$collection = $this->getNumbersCollection();
		$cursor = $collection->find(array('test' => 'null'));
		$iterator = new Epic_Mongo_Iterator_Cursor($cursor, array());
		$iterator->rewind();
		$this->assertEquals(null, $iterator->current());
	}

} // END class EpicMongoCursorTest extends PHPUnit_Framework_TestCase

class Iterator_Mongo_Document_Test extends Epic_Mongo_Document {}
