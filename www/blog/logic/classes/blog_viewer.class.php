<?php
class blog_viewer {

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

	public function listPosts () {
		if (is_object($this->DBObj)) {
			
		}
		else {
			return FALSE;
		}
	}


	/**
	* Private functions
	*/


}