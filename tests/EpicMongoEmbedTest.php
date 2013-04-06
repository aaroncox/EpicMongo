<?php
/**
 * undocumented class
 *
 * @package default
 * @author Aaron Cox
 **/
class EpicMongoEmbedTest extends PHPUnit_Framework_TestCase
{
	public function testEmbedDocReplacement() {
		$schema = new Test_Embed_Schema;
		$base = $schema->resolve('doc:base');
		$base->id = "embed-test-2";
		$fakeset1 = $schema->resolve('doc:fakeset');
		$fakeset1->test = "rawr";
		$base->embedded = $fakeset1;
		$base->save();

		// Load the same document from the DB
		$base2 = $schema->resolve('base')->findOne(array("id" => "embed-test-2"));
		$fakeset2 = $schema->resolve('doc:fakeset');
		$fakeset2->test = "rawr again!";
		$base2->embedded = $fakeset2;
		$base2->save();
		
		// Load the same document again
		$base3 = $schema->resolve('base')->findOne(array("id" => "embed-test-2"));
		$this->assertEquals($base2->embedded->test, $base3->embedded->test);
	}
	
	public function testEmbedReplacement() {
		// Create a new Schema
		$schema = new Test_Embed_Schema;
		// This is our base document, it contains another document that kind of acts like a DocumentSet, except has strings
		$base = $schema->resolve('doc:base');
		// Setting an ID so we can load it later on
		$base->id = "embed-test";
		// This is the "Fake Set", it has 5x fields, test1 - test5, all of which are references to embedded docs
		$fakeset = $schema->resolve('doc:fakeset');
		// Storage for Debugging (Incorrect Refs)
		$incorrectRefs = array();
		// Foreach of the fields, 1-5...
		foreach(array(1,2,3,4,5) as $idx) {
			// The field we are writing is 'test'.$idx
			$field = 'test' . $idx;
			// Create a new 'embed' document
			$embed = $schema->resolve("doc:embed");
			// Save it to it's collection
			$embed->save();
			// Save a reference to it in the $incorrectRefs array for debugging
			$incorrectRefs[$field] = $embed->createReference();
			// Set the field on the 'Fake Set' to the embedded document
			$fakeset->$field = $embed;
		}
		// Insert the 'Fake Set' to the embedded field on the base document
		$base->embedded = $fakeset;
		// Save the base document
		$base->save();
		// Creating a new fake set
		$fakeset = $schema->resolve('doc:fakeset');
		// Storage for Debugging (Correct Refs)
		$correctRefs = array();
		// Foreach of the fields, 1-5...
		foreach(array(1,2,3,4,5) as $idx) {
			// The field we are writing is 'test'.$idx
			$field = 'test' . $idx;
			// Create a new 'embed' document
			$embed = $schema->resolve("doc:embed");
			// Save it to it's collection
			$embed->save();
			// Save a reference to it in the $correctRefs array for debugging
			$correctRefs[$field] = $embed->createReference();
			// Set the field on the 'Fake Set' to the embedded document
			$fakeset->$field = $embed;
		}
		// Place the new fake set ontop of the old one
		$base->embedded = $fakeset;
		// Save
		$base->save();
		// Load the document we just saved twice into a new variable.
		$loaded = $schema->resolve("base")->findOne(array("id" => "embed-test"));
		// Foreach of it's embedded documents
		foreach($loaded->embedded as $key => $doc) {
			// Assert that the MongoIDs match the IDs stored in the 2nd iteration and not the 1st.
			$this->assertEquals((string) $correctRefs[$key]['$id'], (string) $doc->_id);
			// ** Uncomment the code below to see how the 1st pass docs still are there even though the 2nd pass should have overwritten it
			// var_dump(
			// 	"=========",
			// 	(string) "1st Pass: ".$incorrectRefs[$key]['$id'], 
			// 	(string) "2nd Pass: ".$correctRefs[$key]['$id'], 
			// 	(string) "Actual: ".$doc->_id
			// 	);
		}
		// exit;
	}
} // END class EpicMongoDocumentTest extends PHPUnit_Framework_TestCase

class Test_Embed_Schema extends Epic_Mongo_Schema {
	protected $_typeMap = array(
		'base' => 'Test_Embed_Document',
		'fakeset' => 'Test_Embed_Document_FakeSet',
		'embed' => 'Test_Embed_Document_Embed',
	);
	public function init() {
		$this->_db = MongoDb_TestHarness::getInstance()->dbName;
	}
}

class Test_Embed_Document extends Epic_Mongo_Document {
	protected $_collection = 'test_document_base';	
	protected $_requirements = array(
		'embedded' => 'doc:embed',
	);
}

class Test_Embed_Document_FakeSet extends Epic_Mongo_Document {
	protected $_requirements = array(
		'test1' => array('doc:embed', 'ref'),
		'test2' => array('doc:embed', 'ref'),
		'test3' => array('doc:embed', 'ref'),
		'test4' => array('doc:embed', 'ref'),
		'test5' => array('doc:embed', 'ref'),
	);
}

class Test_Embed_Document_Embed extends Epic_Mongo_Document {
	protected $_collection = 'test_document_embed';	
}