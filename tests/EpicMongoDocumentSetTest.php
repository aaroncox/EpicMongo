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

	public function testSetPush()
	{
		$set = new Epic_Mongo_DocumentSet();
		$doc = new Epic_Mongo_Document();
		$doc2 = new Epic_Mongo_Document();
		$set->setProperty(null, $doc);
		$set->setProperty(null, $doc2);
		$this->assertEquals($doc, $set[0]);
		$this->assertEquals($doc2, $set[1]);
		$set->setProperty(0, null);
		$set->setProperty(null, $doc);
		$this->assertEquals($doc, $set[2]);
	}

}