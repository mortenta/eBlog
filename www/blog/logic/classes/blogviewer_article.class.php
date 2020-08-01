<?php
class blogviewer_article {

	private $DBObj;
	private $URLPath;

	private $PostArray;
	private $DateFormat = 'Y-m-d';
	private $RelatedList = array();

	function __construct () {
		chdir(dirname(__FILE__));
		require('../../config.php');
		require_once('./blog_db.class.php');
		$BlogDBObj = new blog_db;
		if ($BlogDBObj->connectDB() && is_object($BlogDBObj->getDBObj())) {
			$this->DBObj = $BlogDBObj->getDBObj();
		}
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

	public function setDateFormat ($string) {
		$this->DateFormat = $string;
		return TRUE;
	}

	/**
	* Getters
	*/

	public function getTitle () {
		if (is_array($this->PostArray)) {
			return $this->PostArray['title'];
		}
		else {
			return FALSE;
		}
	}

	public function getMetaTitle () {
		if (is_array($this->PostArray)) {
			return $this->PostArray['meta_title'];
		}
		else {
			return FALSE;
		}
	}

	public function getMetaDescription () {
		if (is_array($this->PostArray)) {
			return $this->PostArray['meta_description'];
		}
		else {
			return FALSE;
		}
	}

	public function getSummary () {
		if (is_array($this->PostArray)) {
			return $this->PostArray['summary'];
		}
		else {
			return FALSE;
		}
	}

	public function getContent () {
		if (is_array($this->PostArray)) {
			return $this->PostArray['content'];
		}
		else {
			return FALSE;
		}
	}

	public function getTimeCreated ($format=FALSE) {
		if (is_array($this->PostArray)) {
			if (!is_string($format)) {
				$format = $this->DateFormat;
			}
			return date($format,strtotime($this->PostArray['time_created']));
		}
		else {
			return FALSE;
		}
	}

	public function getTimeUpdated ($format=FALSE) {
		if (is_array($this->PostArray)) {
			if (!is_string($format)) {
				$format = $this->DateFormat;
			}
			return date($format,strtotime($this->PostArray['time_updated']));
		}
		else {
			return FALSE;
		}
	}

	public function getRelatedIndex () {
		if (is_array($this->RelatedList)) {
			return array_keys($this->RelatedList);
		}
		else {
			return array_keys(array());
		}
	}

	public function getRelTitle ($i) {
		if (is_numeric($i) && is_array($this->RelatedList[$i])) {
			return $this->RelatedList[$i]['title'];
		}
		else {
			return FALSE;
		}
	}

	public function getRelPath ($i) {
		if (is_numeric($i) && is_array($this->RelatedList[$i])) {
			return $this->RelatedList[$i]['url_path'];
		}
		else {
			return FALSE;
		}
	}

	public function getRelSummary ($i) {
		if (is_numeric($i) && is_array($this->RelatedList[$i])) {
			return $this->RelatedList[$i]['summary'];
		}
		else {
			return FALSE;
		}
	}

	/**
	* Public functions
	*/

	public function loadArticle () {
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
				$this->PostArray = $Row;
				return TRUE;
			}
			else {
				return FALSE;
			}
		}
		else {
			return FALSE;
		}
	}

	public function loadRelated () {
		if (is_string($this->URLPath)) {
			$QueryString = "SELECT ";
			$QueryString .= "blog_posts.id, ";
			$QueryString .= "blog_posts.title, ";
			$QueryString .= "blog_posts.url_path, ";
			$QueryString .= "blog_posts.summary, ";
			$QueryString .= "blog_posts.img ";
			$QueryString .= "FROM ";
			$QueryString .= "blog_posts as obp ";
			$QueryString .= "LEFT JOIN blog_post_relations ON blog_post_relations.postid=obp.id ";
			$QueryString .= "LEFT JOIN blog_posts ON blog_posts.id=blog_post_relations.relpostid ";
			$QueryString .= "WHERE ";
			$QueryString .= "obp.url_path=:url_path ";
			$QueryString .= "AND blog_posts.published=1 ";
			$QueryString .= "ORDER BY blog_post_relations.`order`";
			$q = $this->DBObj->prepare($QueryString);
			$q->bindParam(":url_path",$this->URLPath);
			$q->execute();
			foreach ($q->fetchAll(PDO::FETCH_ASSOC) AS $Row) {
				$this->RelatedList[] = $Row;
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