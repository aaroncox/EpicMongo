<?php

class Epic_Mongo_Auth_Laravel extends \Laravel\Auth\Drivers\Driver {

	/**
	 * Get the current user of the application.
	 *
	 * If the user is a guest, null should be returned.
	 *
	 * @param  int         $id
	 * @return mixed|null
	 */
	public function retrieve($id)
	{
		if (!$id) {
			return NULL;
		}
		$query = array(
			'_id' => new MongoId($id)
		);
		return Epic_Mongo::db('user')->findOne($query);
	}

	/**
	 * Attempt to log a user into the application.
	 *
	 * @param  array  $arguments
	 * @return void
	 */
	public function attempt($arguments = array())
	{
		$username = Config::get('auth.username');
		if(!Config::has('auth.username')) {
			throw new Exception('The username in application/config/auth.php must be defined.');
		}
		
		$model = Config::get('auth.model');
		// Add the username to the query
		$query = array(
			'$or' => array(
				array(
					Config::get('auth.username') => $arguments[Config::get('auth.username')]
				)
			), 
		);
		// If we've specified an 'username_alt' field in the config, add that to the $OR
		if(Config::has('auth.username_alt')) {
			$query['$or'][] = array(
				Config::get('auth.username_alt') => $arguments[Config::get('auth.username')]
			);
		}
		$user = Epic_Mongo::db('user')->findOne($query);
		// This driver uses a basic username and password authentication scheme
		// so if the credentials match what is in the database we will just
		// log the user into the application and remember them if asked.
		$password = $arguments[Config::get('auth.password')];
		// if ( ! is_null($user) and Hash::check($password, $user->password))
		if (!is_null($user) and Hash::check($password, $user->password)) {
			return $this->login($user->_id, array_get($arguments, 'remember'));
		} else {
			if(!is_null($user) and md5($password) == $user->password) {
				return $this->login($user->_id, array_get($arguments, 'remember'));				
			}
		}

		return false;
	}
}