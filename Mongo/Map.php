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
	
	public function getClass($type) {
		if(!isset($this->_map[$type])) {
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
		return new $class;
	}
	
} // END class Epic_Mongo_Map