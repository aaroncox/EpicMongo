<?php
// Test Sequenced Documents, the Collection and Incremenenting
class EpicMongoDocumentSequencedTest extends PHPUnit_Framework_TestCase {
	public function testEpicMongoDocumentExists()
	{
		$this->assertTrue(class_exists('Epic_Mongo_Document_Sequenced'));
	}
	
	public function testSequenceIdGeneration() {
		$schema = new Test_Document_Sequenced_Mongo_Schema;
		// Create a Blank Document and Save
		$doc = $schema->resolve('doc:test');
		$doc->save();
		// Assert that it gave it a sequence ID
		$this->assertEquals($doc->id, 1);
	}

	/**
	 * @expectedException Epic_Mongo_Exception
	 */
	public function testSequenceWithoutKey() {
		$schema = new Test_Document_Sequenced_Mongo_Schema;
		// Create a Blank Document and Save
		$doc = $schema->resolve('doc:testNoKey');
		$doc->save();
	}

	public function testSetSequenceKey() {
		$schema = new Test_Document_Sequenced_Mongo_Schema;
		// Create a Blank Document and Save
		$doc = $schema->resolve('doc:testNoKey');
		$doc->setNextSequence('test', 10);
	}

}

class Test_Document_Sequenced_Mongo_Schema extends Epic_Mongo_Schema {
	protected $_typeMap = array(
		'test' => 'Test_Document_Sequenced_Mongo_Document',
		'testNoKey' => 'Test_Document_Sequenced_NoKey_Mongo_Document',
	);
	public function init() {
		$this->_db = MongoDb_TestHarness::getInstance()->dbName;
	}
}

class Test_Document_Sequenced_Mongo_Document extends Epic_Mongo_Document_Sequenced {
	protected $_sequenceKey = 'test';
	protected $_collection = 'test_document_sequenced';
}

class Test_Document_Sequenced_NoKey_Mongo_Document extends Epic_Mongo_Document_Sequenced {
	protected $_collection = 'test_document_sequenced';
}