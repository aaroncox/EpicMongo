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
	protected $_operations = array();

	public function __construct($data = array(), $config = array()) {
		// guaruntees that the requirements get parsed
		$this->setRequirements($this->_typeMap?:array());
		parent::__construct($config);
		if(!is_array($data)) {
			throw new Epic_Mongo_Exception("Data must be an array.");
		}
		$this->_cleanData = $data;

		if($this->isNewDocument() && $this->hasKey()) {
			$this->_id = new MongoId();
		}
		if($this->hasId()) {
			$criteria = array();
			$criteria[$this->getPathToProperty('_id')] = $this->_id;
			$this->setCriteria($criteria);
		}
	}

	public function getConfig($key = null)
	{
		if (is_null($key)) {
			return $this->_config;
		}
		if (!array_key_exists($key,$this->_config)) {
			return null;
		}
		return $this->_config[$key];
	}

	public function isNewDocument()
	{
		return empty($this->_cleanData);
	}

	public function hasId()
	{
		return !is_null($this->_id);
	}

	public function hasKey()
	{
		return $this->isRootDocument() && $this->hasCollection();
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
					$value = false;
				}
				break;

			// all others are boolean types
			default:
				$value = true;
				break;
		}
		return $value;
	}

	public function getRequirements($prefix = null)
	{
		if ($prefix===null) {
			return $this->_requirements;
		}
		$filtered = array();
		foreach ($this->_requirements as $key=>$value) {
			if (substr($key, 0, strlen($prefix)) == $prefix) {
				$filtered[substr($key,strlen($prefix))] = $value;
			}
		}
		return $filtered;
	}

	public function setRequirements(array $requirements)
	{
		// Force all property values to be an array
		$this->_requirements = $this->_parseRequirementsArray($this->_requirements);

		// Merge requirement modifiers with existing requirements
		$this->_requirements = array_merge_recursive($this->_requirements, $this->_parseRequirementsArray($requirements));
		return $this;
	}

	public function addOperation($operation, $property = null, $value = null)
	{
		// Prime the specific operation
		if (!array_key_exists($operation, $this->_operations)) {
			$this->_operations[$operation] = array();
		}

		// Save the operation
		$this->_operations[$operation][$this->getPathToProperty($property)] = $value;
	}

	public function getOperations($includeChildren = false)
	{
		$operations = array();
		if($includeChildren) {
			foreach($this as $key=>$value) {
				if ($value instanceOf Epic_Mongo_Document && !$this->hasRequirement($key, 'ref')) {
					$operations = array_merge($operations, $value->getOperations(true));
				}
			}
		}
		return array_merge($operations,$this->_operations);
	}

	public function purgeOperations($includeChildren = false)
	{
		$this->_operations = array();
		if($includeChildren) {
			foreach($this as $key=>$value) {
				if ($value instanceOf Epic_Mongo_Document && !$this->hasRequirement($key, 'ref')) {
					$value->purgeOperations();
				}
			}
		}
	}

	protected function processChanges(array $data = array(), $cleanData = null)
	{
		if (is_null($cleanData)) {
			$cleanData = $this->_cleanData;
		}
		foreach ($data as $key => $value) {
			$cleanExists = array_key_exists($key, $cleanData);
			if ((!$cleanExists || $cleanData[$key] !== $value) &&
				!($key == '_id' && $this->isRootDocument())) {
				if ($cleanExists && $this->$key instanceOf Epic_Mongo_Document && !$this->hasRequirement( $key, 'ref' )) {
					$this->$key->processChanges($data[$key], $cleanData[$key]);
				} else {
					$this->addOperation('$set', $key, $value);
				}
			}
		}

		foreach ($cleanData as $key => $value) {
			if (!array_key_exists($key, $data)) {
				$this->addOperation('$unset', $key, 1);
			}
		}
	}

	public function save($wholeDocument = false)
	{
		$ops = array();
		$exportData = $this->export();

		// TODO: Check Requirements

		$new = $this->isNewDocument();
		$root = $this->isRootDocument();
		$set = $this->getConfig("parentIsSet");
		if ($root && ($new || $wholeDocument)) {
			$ops = $exportData;
		} else {
			if (!$root && $new && $set) {
				$set->addOperation('$push', null, $exportData);
				$ops = $set->getOperations();
			} else {
				$this->processChanges($exportData);
				$ops = $this->getOperations(true);
			}
			if (empty($ops)) {
				return true;
			}
		}

		$criteria = $this->getCriteria();
		if(empty($criteria)) {
			throw new Epic_Mongo_Exception("No search criteria to save");
		}

		$db = $this->getSchema()->getMongoDb();
		$q = array(
			'findAndModify' => $this->getCollection(),
			'query' => $criteria,
			'update' => $ops,
			'upsert' => true,
			'new' => true,
		);
		$result = $db->command($q);

		if ($ops != $exportData) {
			$this->purgeOperations(true);
			if ($set) {
				$set->purgeOperations();
			}
		}
		if (array_key_exists('errmsg', $result)) {
			throw new Epic_Mongo_Exception( $result['errmsg'] );
		}
		if ($root) {
			$this->_cleanData = $result["value"];
		} else {
			$this->_cleanData = $exportData;
		}
		return $result["ok"];
	}
	
	/**
	 * Delete this document
	 * 
	 * $return boolean Result of delete
	 */
	public function delete()
	{
		// Make sure it's in a collection
		if (!$this->hasCollection()) {
			throw new Epic_Mongo_Exception('Can not delete document, it does not belong to a collection.');
		}
		
		// Get the name of it's collection
		$name = $this->getCollection();
		
		// Get the collection itself
		$collection = $this->getSchema()->getMongoDB()->$name;
		
		// Is this a root document or an embedded document?
		if (!$this->isRootDocument()) {
			// If embedded, unset the document on the root document
			$result = $collection->update($this->getCriteria(), array('$unset' => array($this->getPathToDocument() => 1)));
		} else {
			// Else just remove it by the query
			$result = $collection->remove($this->getCriteria(), array('justOne' => true));
		}
		
		// Return the Results
		return $result;
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
		if (!$this->isRootDocument()) {
			throw new Epic_Mongo_Exception('Can not create reference. Document is not root');
		}
		return MongoDBRef::create($this->getCollection(), $this->_id);
	}

	public function getCriteria()
	{
		if (!array_key_exists("criteria",$this->_config)) {
			return array();
		}
		return $this->_config["criteria"];
	}

	public function setCriteria(array $criteria)
	{
		if (!array_key_exists("criteria", $this->_config)) {
			$this->_config["criteria"] = array();
		}
		$this->_config["criteria"] = $criteria + $this->_config["criteria"];
		return $this;
	}

	protected function getConfigForProperty($key, $data) {
		$config = array(
			'requirements' => $this->getRequirements($key.'.')
		);
		if(!$this->isReference($key)) {
			$config['collection'] = $this->getCollection();
			$config['pathToDocument'] = $this->getPathToProperty($key);
			$config['criteria'] = $this->getCriteria();
		}
		if ($this->_schema) {
			$config['schema'] = $this->_schema;
		}
		return $config;
	}

	public function extend($data = null)
	{
		if (!is_null($data)) {
			foreach($data as $key=>$value) {
				if(!array_key_exists($key,$this->_data)) {
					if(is_array($value)) {
						$this->_resolveProperty($key,$value);
					} else {
						$this->setProperty($key,$value);
					}
				} else if ($this->_data[$key] instanceOf Epic_Mongo_Document) {
					$this->_data[$key]->extend($value);
				} else {
					$this->setProperty($key,$value);
				}
			}
		}
		return $this;
	}

	public function isReference( $key ) {
		if(array_key_exists($key, $this->_data)) {
			$data = $this->_data[$key];
			if ($data instanceOf Epic_Mongo_Document && $data->getConfig('isReference')) {
				return "data";
			}
		}
		if(array_key_exists($key, $this->_cleanData) && MongoDbRef::isRef($this->_cleanData[$key])) {
			return "clean";
		} 
		if($this->hasRequirement($key,'ref')) {
			return "requirement";
		}
		return false;
	}

	public function doc($key, $data = null)
	{
		if(array_key_exists($key,$this->_data)) {
			return $this->_data[$key]->extend($data);
		}
		if (!is_array($data)) {
			$data = array();
		}
		$set = $this->hasRequirement($key,'set');
		$doc = $this->hasRequirement($key,'doc');
		$ref = MongoDbRef::isRef($data);
		$config = array();
		if ($ref) {
			$config['collection'] = $data['$ref'];
			$config['isReference'] = true;
			$data = MongoDBRef::get($this->getSchema()->getMongoDB(), $data);
			// If this is a broken reference then no point keeping it for later
			if (!$data) {
				if($this->hasRequirement($key,'auto')) {
					$data = array();
				} else {
					return $this->_data[$key] = null;
				}
			}
		}
		if(!($doc || $set)) {
			$set = $this->_dataIsSimpleArray($data);
		}
		$schemaType = $set ? 'set' : 'doc';
		if($documentClass = $this->getRequirement($key, $schemaType)) {
			$schemaType .= ":" . $documentClass;
		}
		$doc = $this->getSchema()->resolve($schemaType, $data, $config);
		$this->setProperty($key,$doc);
		return $doc;
	}

	protected function _resolveProperty($key, $data)
	{
		$auto = $this->hasRequirement($key,'auto');

		// array type forced
		if($this->hasRequirement($key,'array')) {
			if (!$data) {
				$data = array();
			}
			return $this->_data[$key] = $data;
		}
		if($auto || is_array($data)) {
			$data = $this->doc($key, $data);
		}
		if (!is_null($data)) {
			$this->_data[$key] = $data;
		}
		return $data;
	}

	public function getProperty($key) {
		// if the data has already been loaded
		if(array_key_exists($key, $this->_data)) {
			return $this->_data[$key];
		}
		// read from cleanData
		if(array_key_exists($key, $this->_cleanData)) {
			return $this->_resolveProperty($key, $this->_cleanData[$key]);
		}
		return $this->_resolveProperty($key, null);
	}

	public function setProperty($key, $value) {
		if ($value instanceof Epic_Mongo_Document && !$this->hasRequirement($key, 'ref')) {
			$config = $this->getConfigForProperty($key,$value);
			$value->setConfig($config);
		}

		$this->_data[$key] = $value;
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
			if(is_null($value) || ($value instanceOf Epic_Mongo_Document && $value->isEmpty())) {
				$ignore[] = $key;
			} else {
				$keyList[] = $key;
			}
		}
		foreach($this->_cleanData as $key=>$value) {
			if(in_array($key, $ignore) || in_array($key,$keyList)) {
				continue;
			}
			if(!is_null($value)) {
				$keyList[] = $key;
			}
		}
		return $keyList;
	}

	public function isEmpty()
	{
		$doNoCount = array();

		foreach ($this->_data as $key => $value) {
			if ($value instanceof Epic_Mongo_Document) {
				if (!$value->isEmpty()) {
					return false;
				}
			} else if (!is_null($value)) {
				return false;
			}
			$doNoCount[] = $key;
		}

		foreach ($this->_cleanData as $key => $value) {
			if (!(in_array($key, $doNoCount) || is_null($value))) {
				return false;
			}
		}

		return true;
	}

	public function isRootDocument()
	{
		return !(array_key_exists("pathToDocument",$this->_config) && $this->_config["pathToDocument"]);
	}

	public function getPathToDocument()
	{
		return $this->_config["pathToDocument"];
	}

	public function setPathToDocument($path="")
	{
		$this->_config["pathToDocument"] = $path;
		return $this;
	}

	public function getPathToProperty($property = null)
	{
		if (is_null($property)) {
			return $this->getPathToDocument();
		}
		return $this->isRootDocument() ? $property : $this->getPathToDocument() . '.' . $property;
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
