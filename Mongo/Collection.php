<?php
/**
 * undocumented class
 *
 * @package default
 * @author Aaron Cox
 **/
class Epic_Mongo_Collection
{
	protected $_collection = null;
	protected $_schema = null;
	protected $_config = array();

	public function __construct($config = array()) {
		$this->setConfig($config);
	}

	public function setConfig(array $config) {
		$class = get_called_class();
		foreach($config as $k => $v) {
			$this->_config[$k] = $v;
			$method = 'set' . ucfirst($k);
			if(method_exists($class, $method)) {
				call_user_func(array($this, $method), $v);
			}
		}
	}

	public function getConfig($key)
	{
		if(array_key_exists($key,$this->_config)) {
			return $this->_config[$key];
		}
		return null;
	}

	public function setSchema(Epic_Mongo_Schema $schema) {
		$this->_schema = $schema;
		return $this;
	}

	public function getSchema() {
		if (!$this->_schema) {
			throw new Epic_Mongo_Exception("Schema required");
		}
		return $this->_schema;
	}

	public function setCollection($name) {
		$this->_collection = $name;
		return $this;
	}

	public function hasCollection()
	{
		return !!$this->_collection;
	}

	public function getCollection() {
		return $this->_collection;
	}

	public function find($query = array(), $fields = array()) {
		$db = $this->getSchema()->getMongoDb();
		$collection = $db->selectCollection($this->getCollection());
		$cursor = $collection->find($query, $fields);
		$config = array(
			"schema" => $this->getSchema(),
			"collection" => $this->getCollection(),
			"schemaKey" => "doc:".$this->_config['schemaKey'],
		);
		return $this->getSchema()->resolve("cursor:".$this->_config['schemaKey'], $cursor, $config);
	}

	public function findOne($query = array(), $fields = array()) {
		$db = $this->getSchema()->getMongoDb();
		$collection = $db->selectCollection($this->getCollection());
		$document = $collection->findOne($query, $fields);
		if(!$document) {
			return null;
		}
		$config = array(
			"schema" => $this->getSchema(),
			"collection" => $this->getCollection(),
		);
		return $this->getSchema()->resolve("doc:".$this->_config['schemaKey'], $document, $config);
	}

	/**
	 * Is this class a document class
	 *
	 * @return boolean
	 */
	public static function isDocumentClass()
	{
		$class = get_called_class();
		return $class == 'Epic_Mongo_Document' ||  is_subclass_of(get_called_class(), 'Epic_Mongo_Document');
	}
} // END class Epic_Mongo_Collection