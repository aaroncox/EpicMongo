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
	throw new Exception("Please create 'epicmongo.php' in your application/config folder (template found in bundles/epic_mongo/examples/laravel_config).");
}

// Establish a Default Connection
Epic_Mongo::addConnection('default', Config::get('epicmongo.host'));

// Include the Laravel Schema
require_once(dirname(__FILE__) . "/Mongo/Schema/Laravel.php");

// Load the default schema and ensure it exists
$schema = Config::get('epicmongo.schema');
if(!Config::has('epicmongo.schema')) {
	throw new Exception("Schema not found, please specify a schema in 'EpicMongo.php' in your application/config folder (example found on the template found in bundles/epic_mongo/examples/laravel_config).");
}
// Add the Schema to EpicMongo
Epic_Mongo::addSchema('db', new $schema);

// Include the Laravel Auth 
require_once(dirname(__FILE__) . "/Mongo/Auth/Laravel.php");

Auth::extend('epic_mongo', function() {
    return new Epic_Mongo_Auth_Laravel(Config::get('auth.model'));
});
// 
// Testing Authentication
// 
// $credentials = array('username' => 'username', 'password' => 'password');
// 
// if (Auth::attempt($credentials))
// {
// 	var_dump(Auth::user()->email); exit;
// }