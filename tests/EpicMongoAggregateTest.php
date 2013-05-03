<?php

class EpicMongoAggregateTest extends PHPUnit_Framework_TestCase
{
	public function setUp() {
		$this->_db = MongoDb_TestHarness::getInstance()->dbName;
		Epic_Mongo::addSchema('db', new Test_Aggregate_Mongo_Schema);
	}
	
	public function testSimpleGroup() {
		$pipeline = array(
			array(
				'$group' => array(
					'_id' => '$v1',
					'count' => array('$sum' => 1),
				)
			)
		);
		$results = Epic_Mongo::db('test')->aggregate($pipeline);
		foreach($results['result'] as $result) {
			$this->assertEquals(1000, $result['count']);
		}
	}
}

class Test_Aggregate_Mongo_Schema extends Epic_Mongo_Schema {
	protected $_typeMap = array(
		'test' => 'Test_Aggregate_Mongo_Document',
	);
	public function init() {
		$this->_db = MongoDb_TestHarness::getInstance()->dbName;
	}
}

class Test_Aggregate_Mongo_Document extends Epic_Mongo_Document {
	protected $_collection = 'test';
}