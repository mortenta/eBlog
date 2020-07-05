<?php
class blog_viewer {

	private $DBObj;
	private $URLPath;
	private $SiteSettings;

	function __construct () {
		chdir(dirname(__FILE__));
		require('../../config.php');
		require_once('./blog_db.class.php');
		$BlogDBObj = new blog_db;
		if ($BlogDBObj->connectDB() && is_object($BlogDBObj->getDBObj())) {
			$this->DBObj = $BlogDBObj->getDBObj();
		}
		$this->SiteSettings = $SiteSettings;
	}


	/**
	* Setters
	*/

	public function setURLPath ($string) {
		if (is_string($string) && strlen($string) <= 255) {
			$this->URLPath = $this->toAscii($string);
			return TRUE;
		}
		else {
			return FALSE;
		}
	}

	/**
	* Getters
	*/

	public function getSiteSettings () {
		return $this->SiteSettings;
	}

	/**
	* Public functions
	*/

	public function listPosts ($Limit=FALSE) {
		if (is_object($this->DBObj)) {
			$QueryString = "SELECT ";
			$QueryString .= "blog_posts.id, ";
			$QueryString .= "blog_posts.time_created, ";
			$QueryString .= "blog_posts.time_updated, ";
			$QueryString .= "blog_posts.time_published, ";
			$QueryString .= "blog_posts.published, ";
			$QueryString .= "blog_posts.url_path, ";
			$QueryString .= "blog_posts.title, ";
			$QueryString .= "blog_posts.summary, ";
			$QueryString .= "GROUP_CONCAT(blog_tags.id) AS tag_ids, ";
			$QueryString .= "blog_posts.img ";
			$QueryString .= "FROM ";
			$QueryString .= "blog_posts ";
			$QueryString .= "LEFT JOIN blog_post_tag_map ON blog_post_tag_map.postid = blog_posts.id ";
			$QueryString .= "LEFT JOIN blog_tags ON blog_tags.id = blog_post_tag_map.tagid ";
			$QueryString .= "WHERE ";
			$QueryString .= "published=1 ";
			if (is_numeric($this->Year)) {
				$QueryString .= "AND YEAR(blog_posts.time_published) = :year ";
			}
			if (is_string($this->Category)) {
				$QueryString .= "AND blog_tags.path = :category ";
			}
			$QueryString .= "GROUP BY blog_posts.id ";
			$QueryString .= "ORDER BY time_published DESC ";
			if (is_numeric($Limit)) {
				$QueryString .= "LIMIT ".$Limit;
			}
			$q = $this->DBObj->prepare($QueryString);
			if (is_numeric($this->Year)) {
				$q->bindParam(":year",$this->Year);
			}
			if (is_string($this->Category)) {
				$q->bindParam(":category",$this->Category);
			}
			$q->execute();
			$TagIDArray = array();
			foreach ($q->fetchAll(PDO::FETCH_ASSOC) AS $Row) {
				if (is_string($Row['tag_ids'])) {
					if (is_array($TagIDs = explode(',',$Row['tag_ids']))) {
						foreach ($TagIDs as $TagID) {
							if (is_numeric($TagID)) {
								$TagIDArray[$TagID] = $TagID;
							}
						}
					}
				}
				unset($TagIDs);
				$PostList[] = $Row;
			}
			// Get tags
			if (is_array($TagIDArray)) {
				$QueryString = "SELECT ";
				$QueryString .= "* ";
				$QueryString .= "FROM ";
				$QueryString .= "blog_tags ";
				$QueryString .= "WHERE ";
				$QueryString .= "id IN(".implode(',',$TagIDArray).") ";
				$QueryString .= "ORDER BY title";
				$q = $this->DBObj->prepare($QueryString);
				$q->execute();
				foreach ($q->fetchAll(PDO::FETCH_ASSOC) AS $Row) {
					$TagArray[$Row['id']] = $Row;
				}
			}
			// Output
			if (is_array($PostList)) {
				foreach ($PostList as $Post) {
					if (!is_array($OA[$Post['id']])) {
						$OA[$Post['id']] = $Post;
						$OA[$Post['id']]['taglist'] = $this->getTags($Post['tag_ids'],$TagArray);
					}
				}
				return $OA;
			}
			else {
				return FALSE;
			}
		}
		else {
			return FALSE;
		}
	}

	public function loadPost () {
		if (is_string($this->URLPath)) {
			$QueryString = "SELECT ";
			$QueryString .= "* ";
			$QueryString .= "FROM ";
			$QueryString .= "blog_posts ";
			$QueryString .= "WHERE ";
			$QueryString .= "url_path=:url_path ";
			$QueryString .= "LIMIT 1";
			$q = $this->DBObj->prepare($QueryString);
			$q->bindParam(":url_path",$this->URLPath);
			$q->execute();
			if (is_array($Row = $q->fetch(PDO::FETCH_ASSOC))) {
				return $Row;
			}
			else {
				return FALSE;
			}
		}
		else {
			return FALSE;
		}
	}

	/**
	* Private functions
	*/

	private function getTags ($TagIDList,$TagsArray) {
		if (is_string($TagIDList) && is_array($TagIDs = explode(',',$TagIDList)) && is_array($TagsArray)) {
			foreach ($TagIDs as $TagID) {
				if (is_numeric($TagID) && is_array($TagsArray[$TagID])) {
					$OA[] = $TagsArray[$TagID];
				}
			}
			if (is_array($OA)) {
				return $OA;
			}
			else {
				return FALSE;
			}
		}
		else {
			return FALSE;
		}
	}

	private function toAscii ($str, $replace=array(), $delimiter='-') {
		if( !empty($replace) ) {
			$str = str_replace((array)$replace, ' ', $str);
		}
		$clean = mb_convert_encoding($str,'ASCII');
		$clean = preg_replace("/[^a-zA-Z0-9\/_|+ -]/", '', $clean);
		$clean = strtolower(trim($clean, '-'));
		$clean = preg_replace("/[\/_|+ -]+/", $delimiter, $clean);
		return $clean;
	}

}