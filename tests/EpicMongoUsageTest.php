<?php
/**
 * undocumented class
 *
 * @package default
 * @author Aaron Cox
 **/
class EpicMongoCollectionTest extends PHPUnit_Framework_TestCase
{

} // END class EpicMongoCollectionTest extends PHPUnit_Framework_TestCase

class Test_Mongo_User extends Epic_Mongo_Document {
	protected $_collection = 'users';
}
class Test_Mongo_Post extends Epic_Mongo_Document {
	protected $_collection = 'posts';
	protected $_requirements = array(
		'author' => array('ref' => 'Test_Mongo_User', 'req' => true),	
	);
}

class Test_Mongo_Schema extends Epic_Mongo_Schema {
	protected $_db = 'epic_mongo_test';
	protected static $_typeMap = array(
		'user' => 'Test_Mongo_User',
		'post' => 'Test_Mongo_Post',
	);
}