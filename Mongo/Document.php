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
		$this->setRequirements(array());
		parent::__construct($config);
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
					array_merge($operations, $value->getOperations());
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

	protected function processChanges(array $data = array())
	{
		foreach ($data as $key => $value) {
			if ($key === '_id') continue;

			if (!array_key_exists($key, $this->_cleanData) || $this->_cleanData[$key] !== $value) {
				$this->addOperation('$set', $key, $value);
			}
		}

		foreach ($this->_cleanData as $key => $value) {
			if (array_key_exists($key, $data)) continue;

			$this->addOperation('$unset', $key, 1);
		}
	}

	public function save($wholeDocument = false)
	{
		$ops = array();
		$exportData = $this->export();

		// TODO: Check Requirements

		$new = $this->isNewDocument();
		$root = $this->isRootDocument();
		if ($root && ($new || $wholeDocument)) {
			$ops = $exportData;
		} else {
			if (!$root && $new && $this->getConfig("parentIsSet")) {
				$this->addOperation('$push', null, $exportData);
			} else {
				$this->processChanges($exportData);
			}
			$ops = $this->getOperations(true);
			if (empty($ops)) {
				return true;
			}
		}

		$criteria = $this->getCriteria();
		if(empty($criteria)) {
			throw new Epic_Mongo_Exception("No search criteria to save");
		}

		$db = $this->getSchema()->getMongoDb();
		$result = $db->command(array(
			'findAndModify' => $this->getCollection(),
			'query' => $criteria,
			'update' => $ops,
			'upsert' => true,
			'new' => true,
		));

		if ($ops != $exportData) {
			$this->purgeOperations(true);
		}
		if (array_key_exists('errmsg', $result)) {
			throw new Epic_Mongo_Exception( $result['errmsg'] );
		}
		if ($root) {
			$this->_cleanData = $result["value"];
		} else {
			$this->_cleanData = $exportData;
		}
		return $result["lastErrorObject"]["ok"];
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
		if(MongoDBRef::isRef($data)) {
			$config['collection'] = $data['$ref'];
		} else if (!$this->hasRequirement('ref',$key)) {
			$config['collection'] = $this->getCollection();
			$config['pathToDocument'] = $this->getPathToProperty($key);
			$config['criteria'] = $this->getCriteria();
		}
		if ($this->_schema) {
			$config['schema'] = $this->_schema;
		}
		return $config;
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
			$config = $this->getConfigForProperty($key,$data);
			$reference = MongoDBRef::isRef($data);
			if ($reference) {
				$data = MongoDBRef::get($this->getSchema()->getMongoDB(), $data);
				// If this is a broken reference then no point keeping it for later
				if (!$data) {
					if ($required) {
						$data = array();
					} else {
						$this->_data[$key] = null;
						return $this->_data[$key];
					}
				}
			}
			if(!($doc || $set)) {
				$set = $this->_dataIsSimpleArray($data);
				$documentClass = $set ? "Epic_Mongo_DocumentSet" : "Epic_Mongo_Document";
			} else {
				$documentClass = $this->getRequirement($key, $doc?'doc':'set');
			}

			$data = new $documentClass($data, $config);
		}
		if (!is_null($data)) {
			$this->_data[$key] = $data;
		}
		return $data;
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