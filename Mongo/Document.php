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
	protected $_requirements = array();

	public function __construct($data = array(), $config = array()) {
		// guaruntees that the requirements get parsed
		$this->setRequirements(array());
		parent::__construct($config);
		$this->_cleanData = $data;
	}

	public function export() {
		return iterator_to_array(new Epic_Mongo_Iterator_Export($this->getIterator()));
	}

	protected function _parseRequirementsArray(array $requirements)
	{
		foreach ($requirements as $property => $requirementList) {
			if (!is_array($requirementList)) {
				$requirements[$property] = array($requirementList);
			}

			$newRequirements = array();
			foreach ($requirements[$property] as $key => $requirement) {
				if (is_numeric($key)) {
					$parts = explode(':', $requirement, 2);
					if ( count($parts) > 1 ) {
						$newRequirements[$parts[0]] = $parts[1];
					} else {
						$newRequirements[$requirement] = null;
					}
				} else {
					$newRequirements[$key] = $requirement;
				}
			}

			$requirements[$property] = $newRequirements;
		}

		return $requirements;
	}

	public function hasRequirement($property, $requirement) {
		// if the property has no requirements, it has no requirement
		if (!array_key_exists($property, $this->_requirements)) {
			return false;
		}

		$requirements = $this->_requirements[$property];
		return array_key_exists($requirement, $requirements);

	}

	public function getRequirement($property, $requirement) {
		if(!$this->hasRequirement($property, $requirement)) {
			return false;
		}
		switch($requirement) {
			case "doc":
			case "set":
				$value = $this->_requirements[$property][$requirement];
				if (!$value) {
					$value = $requirement === "doc" ? "Epic_Mongo_Document" : "Epic_Mongo_DocumentSet";
				}
				break;

			// all others are boolean types
			default:
				$value = true;
				break;
		}
		return $value;
	}

	public function setRequirements(array $requirements)
	{
		// Force all property values to be an array
		$this->_requirements = $this->_parseRequirementsArray($this->_requirements);

		// Merge requirement modifiers with existing requirements
		$this->_requirements = array_merge_recursive($this->_requirements, $this->_parseRequirementsArray($requirements));
		return $this;
	}

	// internal function to determine if the array $data has any non-numeric keys
	protected function _dataIsSimpleArray(array $data)
	{
		$keys = array_keys($data);
		foreach($keys as $k){
			if (is_string($k)) {
				return false;
			}
		}
		return true;
	}

	public function createReference()
	{
		if (!$this->hasCollection()) {
			throw new Epic_Mongo_Exception('Can not create reference. Document does not belong to a collection');
		}
		return MongoDBRef::create($this->getCollection(), $this->_id);
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
		// array type forced
		if($this->hasRequirement($key,'array')) {
			if (!$data) {
				$data = array();
			}
			return $this->_data[$key] = $data;
		}
		$required = $this->hasRequirement($key,'required');
		$set = $this->hasRequirement($key,'set');
		$doc = $this->hasRequirement($key,'doc');
		if($required && $data === null) {
			if($doc || $set) {
				$data = array();
			}
		}
		// if the cleanData is an array, we do special things, otherwise, we just return it.
		if(is_array($data)) {
			if(!($doc || $set)) {
				$set = $this->_dataIsSimpleArray($data);
				$documentClass = $set ? "Epic_Mongo_DocumentSet" : "Epic_Mongo_Document";
			} else {
				$documentClass = $this->getRequirement($key, $doc?'doc':'set');
			}
			$data = new $documentClass($data, $this->_config);
		}
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