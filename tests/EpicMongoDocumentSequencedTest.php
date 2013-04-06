<?php

class EpicMongoDocumentSequencedTest extends PHPUnit_Framework_TestCase {
	public function testEpicMongoDocumentExists()
	{
		$this->assertTrue(class_exists('Epic_Mongo_Document_Sequenced'));
	}
}
