<?php
$testFolder = dirname(__FILE__);
require($testFolder . '/../Mongo.php');
if(file_exists($testFolder . '/config.php')) {
	require($testFolder . '/config.php');
}
if(!defined('TEST_CONNECTION_STRING')) {
	define('TEST_CONNECTION_STRING', '127.0.0.1');
}
Epic_Mongo::addConnection('default', TEST_CONNECTION_STRING);


/**
 * @codeCoverageIgnore
 */
class MongoDb_TestHarness {
	static private $_instance = null;
	public static function getInstance() {
		if (static::$_instance) {
			return static::$_instance;
		}
		return static::$_instance = new self();
	}

	public $dbName;
	private function __construct() {
		$this->dbName = 'test-' . md5(time() . rand());
	}
	public function __destruct() {
		$this->getMongoDb()->command(array("dropDatabase" => 1));
	}
	public function getMongoDb() {
		return Epic_Mongo::getConnection('default')->selectDB($this->dbName);
	}
}
