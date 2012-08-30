<?php
/**
 * undocumented class
 *
 * @package default
 * @author Aaron Cox
 **/
abstract class Epic_Mongo_Schema
{
	protected $_typeMap = null;
	protected $_extends = null;
	protected $_extendSchema = null;
	protected $_connection = null;
	protected $_db = null;

	function __construct() {
		if($this->_extends) {
			$this->_extendSchema = new $this->_extends;
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

	public function map() {
		// Create the typeMap if it doesn't exist
		if(!$this->_typeMap instanceOf Epic_Mongo_Map) {
			$initial = $this->_typeMap;
			if($this->_extends) {
				$this->_typeMap = $this->_extendSchema->map();
			} else {
				$this->_typeMap = new Epic_Mongo_Map($this);
			}
			if(is_array($initial)) {
				$this->_typeMap->addType($initial);
			}
		}
		return $this->_typeMap;
	}

	public function resolve() {
		$return = $this;
		$argv = func_get_args();
		$argc = count($argv);
		if($argc >= 1 && is_string($argv[0])) {
			$return = call_user_func_array(array($this, 'resolveString'), $argv);
		}
		return $return;
	}

	public function resolveString($type) {
		$argv = func_get_args();
		$map = $this->map();
		if (preg_match("/^(doc|set|cursor)(?::(.*))?$/", $type, $matches)) {
			$docType = $matches[1];
			$mapKey = @$matches[2];
			$pass = $argv;
			if ($map->hasClass($type)) {
				$pass[0] = $type;
			} else if ($mapKey) {
				if ($docType=="doc") {
					$pass[0] = $mapKey;
				} else if ($docType == "set") {
					while(count($pass) < 3) {
						$pass[] = array();
					}
					$pass[0] = "set";
					if (!isset($pass[2]["requirements"])) {
						$pass[2]["requirements"] = array();
					}
					if (!isset($pass[2]["requirements"]["$"])) {
						$pass[2]["requirements"]["$"] = "doc:".$mapKey;
					}
				} else {
					$pass[0] = "cursor";
					if(!isset($pass[2]['schemaKey'])) {
						$pass[2]["schemaKey"] = "doc:".$mapKey;
					}
				}
			} else {
				$pass[0] = $type;
			}
			$return = call_user_func_array(array($map, 'getInstance'), $pass);
		} else {
			$return = $map->getStatic($type);
		}
		return $return;
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