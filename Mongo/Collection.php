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
	
	public function __construct($config = array()) {
		$class = get_called_class();
		foreach($config as $k => $v) {
			$method = 'set' . ucfirst($k);
			if(method_exists($class, $method)) {
				call_user_func(array($class, $method), $v);
			}
		}
	}
	
	public function setSchema(Epic_Mongo_Schema $schema) {
		$this->_schema = $schema;
		return $this;
	}
	
	public function getSchema() {
		return $this->_schema;
	}
	
	// public function find($query = array()) {
	// 	if(!$this->_collection) {
	// 		throw new Epic_Mongo_Exception("Collection not specified.");
	// 	}	
	// }
	
	/**
	 * Is this class a document class
	 * 
	 * @return boolean
	 */
	public static function isDocumentClass()
	{
		return is_subclass_of(get_called_class(), 'Epic_Mongo_Document');
	}
} // END class Epic_Mongo_Collection