<?php
class blog_admin {

	private $DBObj;

	function __construct () {
		chdir(dirname(__FILE__));
		require_once('./blog_db.class.php');
		$BlogDBObj = new blog_db;
		if ($BlogDBObj->connectDB() && is_object($BlogDBObj->getDBObj())) {
			$this->DBObj = $BlogDBObj->getDBObj();
		}
	}


	/**
	* Setters
	*/



	/**
	* Getters
	*/



	/**
	* Public functions
	*/

	public function isAuthorized () {
		return FALSE;
	}


	/**
	* Private functions
	*/


}