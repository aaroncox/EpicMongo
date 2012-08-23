<?php
// /**
//  * undocumented class
//  *
//  * @package default
//  * @author Aaron Cox
//  **/
// class EpicMongoUsageTest extends PHPUnit_Framework_TestCase
// {
// 
// } // END class EpicMongoCollectionTest extends PHPUnit_Framework_TestCase
// 
// class Usage_Mongo_User extends Epic_Mongo_Document {
// 	protected $_collection = 'users';
// }
// class Usage_Mongo_Post extends Epic_Mongo_Document {
// 	protected $_collection = 'posts';
// 	protected $_requirements = array(
// 		'author' => array('ref' => 'Usage_Mongo_Use', 'req' => true),	
// 	);
// }
// 
// class Usage_Mongo_Schema extends Epic_Mongo_Schema {
// 	protected $_db = 'epic_mongo_test';
// 	protected $_typeMap = array(
// 		'user' => 'Usage_Mongo_User',
// 		'post' => 'Usage_Mongo_Post',
// 	);
// }