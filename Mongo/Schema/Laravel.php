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
		// Run parent construct
		parent::__construct();
	}	
} // END class Epic_Mongo_Schema_Laravel extends Epic_Mongo_Schema