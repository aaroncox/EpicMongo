<?php
/**
 * undocumented class
 *
 * @package default
 * @author Aaron Cox
 **/
class Epic_Mongo_Document extends Epic_Mongo_Collection implements ArrayAccess, Countable, IteratorAggregate
{
	protected $_data = array(); 
	public function __construct($data = array(), $config = array()) {
		parent::__construct($config);
		$this->_data = $data;
	}
	public function getProperty($key) {
		if(array_key_exists($key, $this->_data)) {
			return $this->_data[$key];
		}
		return null;
	}
	public function setProperty($key, $value) {
		$this->_data[$key]= $value;
		return $value;
	}
	public function hasProperty($key) {
		return array_key_exists($key, $this->_data) && $this->_data[$key] !== null;
	}
	public function getPropertyKeys() {
		return array_keys($this->_data);
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