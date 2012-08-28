<?php
/**
 * undocumented class
 *
 * @package default
 * @author Aaron Cox
 **/
class Epic_Mongo_Document extends Epic_Mongo_Collection implements ArrayAccess, Countable, IteratorAggregate
{
	protected $_cleanData = array();
	protected $_data = array();
	public function __construct($data = array(), $config = array()) {
		parent::__construct($config);
		$this->_cleanData = $data;
	}
	public function getProperty($key) {
		// if the data has already been loaded
		if(array_key_exists($key, $this->_data)) {
			return $this->_data[$key];
		}
		$data = null;
		// read from cleanData
		if(array_key_exists($key, $this->_cleanData)) {
			$data = $this->_cleanData[$key];
		}
		// if the cleanData is an array, we do special things, otherwise, we just return it.
		// if(is_array($data)) {
		// }
		return $this->_data[$key] = $data;
	}
	public function setProperty($key, $value) {
		$this->_data[$key]= $value;
		return $value;
	}
	public function hasProperty($key) {
		if(array_key_exists($key, $this->_data)) {
			return !is_null($this->_data[$key]);
		}
		return array_key_exists($key, $this->_cleanData) && !is_null($this->_cleanData[$key]); 
	}
	public function getPropertyKeys() {
		$keyList = array();
		$ignore = array();
		foreach($this->_data as $key=>$value) {
			if(is_null($value)) {
				$ignore[] = $key;
			} else {
				$keyList[] = $key;
			}
		}
		foreach($this->_cleanData as $key=>$value) {
			if(in_array($key, $ignore)) {
				continue;
			}
			if(!is_null($value)) {
				$keyList[] = $key;
			}
		}
		return $keyList;
	}
	
	public function __get($property) {
		return $this->getProperty($property);
	}
	
	public function __set($property, $value) {
		return $this->setProperty($property, $value);		
	}
	/**
	 * Get an offset
	 * 
	 * @param string $offset
	 * @return mixed
	 */
	public function offsetGet($offset)
	{
		return $this->getProperty($offset);
	}

	/**
	 * set an offset
	 * 
	 * @param string $offset
	 * @param mixed $value
	 */
	public function offsetSet($offset, $value)
	{
		return $this->setProperty($offset, $value);
	}

	/**
	 * Test to see if an offset exists
	 * 
	 * @param string $offset
	 */
	public function offsetExists($offset)
	{
		return $this->hasProperty($offset);
	}

	/**
	 * Unset a property
	 * 
	 * @param string $offset
	 */
	public function offsetUnset($offset)
	{
		$this->setProperty($offset, null);
	}

	/**
	 * Count all properties in this document
	 * 
	 * @return int
	 */
	public function count()
	{
		return count($this->getPropertyKeys());
	}

	/**
	 * Get the document iterator
	 * 
	 * @return Shanty_Mongo_DocumentIterator
	 */
	public function getIterator()
	{
		return new Epic_Mongo_Iterator_Document($this);
	}
} // END class Epic_Mongo_Document