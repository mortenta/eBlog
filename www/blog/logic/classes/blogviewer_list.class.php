<?php
class blogviewer_list {

	private $DBObj;
	private $ArticleList = array();

	private $DateFormat = 'Y-m-d';
	private $DefaultImage = 'https://via.placeholder.com/200x150';
	private $LeadingImgPath;

	function __construct () {
		chdir(dirname(__FILE__));
		require('../../config.php');
		require_once('./blog_db.class.php');
		$BlogDBObj = new blog_db;
		if ($BlogDBObj->connectDB() && is_object($BlogDBObj->getDBObj())) {
			$this->DBObj = $BlogDBObj->getDBObj();
		}
		$this->LeadingImgPath = '//'.$_SERVER['HTTP_HOST'].'/blog/img/tn/';
	}


	/**
	* Setters
	*/

	public function setDefaultImage ($string) {
		$this->DefaultImage = $string;
		return TRUE;
	}

	public function setDateFormat ($string) {
		$this->DateFormat = $string;
		return TRUE;
	}

	public function setLeadingImgPath ($string) {
		$this->LeadingImgPath = $string;
		return TRUE;
	}

	/**
	* Getters
	*/

	public function getListIndex () {
		if (is_array($this->ArticleList)) {
			return array_keys($this->ArticleList);
		}
		else {
			return array_keys(array());
		}
	}

	public function getTitle ($i) {
		if (is_numeric($i) && is_array($this->ArticleList[$i])) {
			return $this->ArticleList[$i]['title'];
		}
		else {
			return FALSE;
		}
	}

	public function getPath ($i) {
		if (is_numeric($i) && is_array($this->ArticleList[$i])) {
			return $this->ArticleList[$i]['url_path'];
		}
		else {
			return FALSE;
		}
	}

	public function getSummary ($i) {
		if (is_numeric($i) && is_array($this->ArticleList[$i])) {
			return $this->ArticleList[$i]['summary'];
		}
		else {
			return FALSE;
		}
	}

	public function getTimeCreated ($i,$format=FALSE) {
		if (is_numeric($i) && is_array($this->ArticleList[$i])) {
			if (!is_string($format)) {
				$format = $this->DateFormat;
			}
			return date($format,strtotime($this->ArticleList[$i]['time_created']));
		}
		else {
			return FALSE;
		}
	}

	public function getTimeUpdated ($i,$format=FALSE) {
		if (is_numeric($i) && is_array($this->ArticleList[$i])) {
			if (!is_string($format)) {
				$format = $this->DateFormat;
			}
			return date($format,strtotime($this->ArticleList[$i]['time_updated']));
		}
		else {
			return FALSE;
		}
	}

	public function getImage ($i) {
		if (is_numeric($i) && is_array($this->ArticleList[$i])) {
			if (is_string($this->ArticleList[$i]['img']) && strlen($this->ArticleList[$i]['img'])>5) {
				return $this->LeadingImgPath.$this->ArticleList[$i]['img'];
			}
			else {
				return $this->DefaultImage;
			}
		}
		else {
			return FALSE;
		}
	}

	

	/**
	* Public functions
	*/

	public function listArticles ($Limit=FALSE) {
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
						$this->ArticleList[$Post['id']] = $Post;
						$this->ArticleList[$Post['id']]['taglist'] = $this->getTags($Post['tag_ids'],$TagArray);
					}
				}
			}
			return TRUE;
		}
		else {
			return FALSE;
		}
	}

	public function returnImage ($Img,$Path,$Default) {
		if (is_string($Img) && strlen($Img)>5) {
			return $Path.$Img;
		}
		else {
			return $Default;
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

}