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
		foreach($doc as $k => $v) {
			$this->assertEquals($data[$k], $v);
		}
		$iterator = $doc->getIterator();
		$iterator->seek('k3');
		$this->assertEquals('v3', $iterator->current());
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
	
	/**
   * @expectedException OutOfBoundsException
   */
	public function testIteratorException() {
		$doc = new Epic_Mongo_Document();
		$doc->getIterator()->seek('test');
	}
} // END class EpicMongoDocumentTest extends PHPUnit_Framework_TestCase