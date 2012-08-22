<?php
/**
 * undocumented class
 *
 * @package default
 * @author Aaron Cox
 **/
abstract class Epic_Mongo_Schema
{
	protected static $_typeMap;
	protected static $_extends = null;
	protected $_extendSchema = null;
	protected $_connection = null;	
	protected $_db = null;
	
	function __construct() {
		if(static::$_extends) {
			$this->_extendSchema = new static::$_extends;
			if($this->_db) {
				$this->_extendSchema->setDb($this->_db);	
			}
			if($this->_connection !== null) {
				$this->_extendSchema->setConnection($this->_connection);	
			}
		}
		$this->init();
	}
	
	function init() {
		
	}
	
	public function getMongoDb() {
		return Epic_Mongo::getConnection($this->getConnection())->selectDB($this->getDb());
	}
	
	public function getConnection() {
		if($this->_connection == null && $this->_extendSchema) {
			return call_user_func(array($this->_extendSchema, 'getConnection'));	
		} 
		if($this->_connection == null) {
			return 'default';
		}
		return $this->_connection;
	}
	
	public function setConnection($connection) {
		$this->_connection = $connection;
	}
	
	public function getDb() {
		if(!is_string($this->_db)) {
			if($this->_extendSchema) {
				return $this->_extendSchema->getDb();	
			}
			throw new Epic_Mongo_Exception('No db defined');
		}
		return $this->_db;
	}
	
	public function setDb($db) {
		$this->_db = $db;
	}
	
	public static function map() {
		// Create the typeMap if it doesn't exist
		if(!static::$_typeMap instanceOf Epic_Mongo_Map) {
			$initial = static::$_typeMap;
			if(static::$_extends) {
				static::$_typeMap = call_user_func(array(static::$_extends, 'map'));
			} else {
				static::$_typeMap = new Epic_Mongo_Map;				
			}
			if(is_array($initial)) {
				static::$_typeMap->addType($initial);				
			}
		}
		return static::$_typeMap;
	}
	
	public function resolve() {
		$args = func_get_args();
		if(is_string($args[0])) {
			return static::map()->getStatic($args[0]);
		}
	}
} // END class Epic_Mongo_Schema
/*

class Test_Schema extends Epic_Mongo_Schema {
	protected $_types = array(
		'user' => array('Epic_Mongo_user')
	);
}

class Epic_Schema extends Test_Schema {
	protected $_types = array(
		'profile' => 'Something_Mongo_Profile',
		'user' => 'Something_Mongo_User',
	);
}

Epic_Mongo::addSchema( 'test', new Epic_Schema );
*/