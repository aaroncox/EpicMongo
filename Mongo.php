<?php
$baseDir = dirname(__FILE__);
require_once($baseDir . "/Mongo/Collection.php");
require_once($baseDir . "/Mongo/Connection.php");
require_once($baseDir . "/Mongo/Document.php");
require_once($baseDir . "/Mongo/Document/Sequenced.php");
require_once($baseDir . "/Mongo/DocumentSet.php");
require_once($baseDir . "/Mongo/Exception.php");
require_once($baseDir . "/Mongo/Iterator/Cursor.php");
require_once($baseDir . "/Mongo/Iterator/Document.php");
require_once($baseDir . "/Mongo/Iterator/Export.php");
require_once($baseDir . "/Mongo/Map.php");
require_once($baseDir . "/Mongo/Schema.php");
/**
*	Epic_Mongo 
*/
class Epic_Mongo
{	
	static protected $_schemas = array();
	
	static protected $_connections = array();
	
	static public function addConnection($name, $string = null, $options = array()) {
		if(isset(static::$_connections[$name])) {
			throw new Epic_Mongo_Exception($name . ' already exists.');
		}
		if ($string instanceOf Epic_Mongo_Connection) {
			static::$_connections[$name] = $string;
		} else {
			static::$_connections[$name] = new Epic_Mongo_Connection($string, $options);
		}
	}
	
	static public function getConnection($name = 'default') {
		if(!isset(static::$_connections[$name])) {
			if($name === 'default') {
				static::addConnection('default');
			} else {
				throw new Epic_Mongo_Exception($name . ' is not a defined connection.');
			}
		}
		return static::$_connections[$name];
	}

	static public function addSchema($name, Epic_Mongo_Schema $schema) {
		if(isset(static::$_schemas[$name])) {
			throw new Epic_Mongo_Exception($name . ' already exist.');
		}
		return static::$_schemas[$name] = $schema;
	}
	
	static public function getSchema($name) {
		if(!isset(static::$_schemas[$name])) {
			throw new Epic_Mongo_Exception($name . ' does not exist.');
		}
		return static::$_schemas[$name];
	}
	
	static public function __callStatic($name, $args) {
		$schema = static::getSchema($name);
		return call_user_func_array(array($schema, 'resolve'), $args);
	}
	
}
