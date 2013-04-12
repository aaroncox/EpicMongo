<?php
/**
 * undocumented class
 *
 * @package default
 * @author Aaron Cox
 **/
class Epic_Mongo_Map
{
	protected $_map = array();
	
	protected $_static = array();
	
	protected $_schema = null;
	
	public function __construct(Epic_Mongo_Schema $schema = null) {
		$this->_schema = $schema;
	}
	
	public function addType($type, $class = false) {
		if(is_array($type)) {
			foreach($type as $key => $value) {
				$this->addType($key, $value);
			}
			return $this;
		}
		if(!class_exists($class)) {
			throw new Epic_Mongo_Exception($class . " is not a class.");
		}
		if(isset($this->_map[$type]) && !is_subclass_of($class, $this->_map[$type])) {
			throw new Epic_Mongo_Exception($class . ' does not extend ' . $this->_map[$type]);
		}
		$this->_map[$type] = $class;
	}
	
	public function hasClass($type) {
		return array_key_exists($type, $this->_map);
	}
	
	public function getClass($type) {
		if(!isset($this->_map[$type])) {
			if($type=='doc') {
				return $this->_map[$type] = 'Epic_Mongo_Document';
			}
			if($type=='set') {
				return $this->_map[$type] = 'Epic_Mongo_DocumentSet';
			}
			if($type=='cursor') {
				return $this->_map[$type] = 'Epic_Mongo_Iterator_Cursor';
			}
			throw new Epic_Mongo_Exception($type . " has not be defined.");
		}
		return $this->_map[$type];
	}
	
	public function getStatic($type) {
		if(isset($this->_static[$type])) {
			return $this->_static[$type];
		}
		return $this->_static[$type] = $this->getInstance($type);
	}
		
	public function getInstance($type) {
		$class = $this->getClass($type);
		$argv = func_get_args();
		$pass = array_slice($argv, 1);
		$reflector = new ReflectionClass($class);
		if(method_exists($class, 'isDocumentClass') && $this->_schema) {
			$config = $class::isDocumentClass() ? 1 : 0;
			if(!isset($pass[0])) {
				$pass[0] = array();
			}
			if(!isset($pass[$config])) {
				$pass[$config] = array();
			}
			$pass[$config]['schema'] = $this->_schema;
			$pass[$config]['schemaKey'] = $type;
		}
		return call_user_func_array(array($reflector, 'newInstance'), $pass);
	}
	
} // END class Epic_Mongo_Map