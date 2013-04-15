<?php
/**
 * undocumented class
 *
 * @package default
 * @author Aaron Cox
 **/
class Epic_Mongo_Connection extends MongoClient
{
	protected $_connectionInfo = array();
	
	public function __construct($connectionString = null, array $options = array())
	{
		if (is_null($connectionString)) {
			$connectionString = '127.0.0.1';
		}
		
		// $options['connect'] = false;
		
		$this->_connectionInfo = $options;
		$this->_connectionInfo['connectionString'] = $connectionString;
		
		return parent::__construct($connectionString, $options);
	}
	
	public function getConnectionInfo()
	{
		return $this->_connectionInfo;
	}
	
} // END class Epic_Mongo_Connection