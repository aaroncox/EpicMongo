<?php
/*	
	Laravel Bundle Startup
	start.php
*/

Autoloader::map(array(
	'Epic_Mongo' => path('bundle').'/epic_mongo/Mongo.php'
));

// Ensure the User has created the epicmongo.php config file in application/config
if(!Config::has('epicmongo')) {
	throw new Exception("Please create 'epicmongo.php' in your application/config folder (template found in bundles/epic_mongo/examples/laravel_config)");
}

// Establish a Default Connection
Epic_Mongo::addConnection('default', Config::get('epicmongo.host'));
class Laravel_Mongo_Schema extends Epic_Mongo_Schema {
	public function __construct() {
		// Get the DB Name from the Configuration File
		$this->_db = Config::get('epicmongo.dbname');
		// Get the TypesMap from the Configuration File
		$this->_typeMap = Config::get('epicmongo.typemap');
		
		// var_dump(path('app').Config::get('epicmongo.models')); exit;
		
		// Construct from parent
		parent::__construct();
	}
}

// Add the Schema to EpicMongo
Epic_Mongo::addSchema('db', new Laravel_Mongo_Schema);
