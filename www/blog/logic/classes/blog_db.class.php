<?php
class blog_db {
	
	private $DBObj;
	private $DBSettings;
	
	function __construct () {
		chdir(dirname(__FILE__));
		require('../../config.php');
		$this->DBSettings = $DBSettings;
	}

	public function getDBObj () {
		if (is_object($this->DBObj)) {
			return $this->DBObj;
		}
		else {
			return FALSE;
		}
	}
	
	public function connectDB () {
		if (is_array($this->DBSettings)) {
			try {
				$this->DBObj = new PDO("mysql:host=".$this->DBSettings['host'].";dbname=".$this->DBSettings['dbname'],$this->DBSettings['username'],$this->DBSettings['password']);
				if ($this->prepUnicode($this->DBObj)) {
					return TRUE;
				}
				else {
					return FALSE;
				}
			} catch (PDOException $e) {
				echo 'Connection failed: ' . $e->getMessage();
			}
		}
		else {
			return FALSE;
		}
	}
	
	private function prepUnicode ($DatabaseObj) {
		if (is_object($DatabaseObj)) {
			$result = $DatabaseObj->query('SET NAMES utf8');
			$result = $DatabaseObj->query('SET CHARACTER SET utf8');
			return TRUE;
		}
	}
	
}