<?php
/**
 * undocumented class
 *
 * @package default
 * @author Aaron Cox
 **/
class Epic_Mongo_Schema_Laravel extends Epic_Mongo_Schema
{
	public function __construct() {
		// Get the DB Name from the Configuration File
		$this->_db = Config::get('epicmongo.dbname');
		// Get the TypesMap from the Configuration File
		$this->_typeMap = Config::get('epicmongo.typemap');
		// Check if we're using Epic_Mongo_Auth_Laravel, if we are, add it to the typeMap
		if(Config::has('auth.model') && Config::get('auth.driver') === 'epic_mongo') {
			$this->_typeMap += array('user' => Config::get('auth.model'));
		}
		// Run parent construct
		parent::__construct();
	}	
} // END class Epic_Mongo_Schema_Laravel extends Epic_Mongo_Schema