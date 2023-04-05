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
		$this->LeadingImgPath = '//'.$_SERVER['HTTP_HOST'].$SiteSettings['basepath'].'/img/tn/';
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

	public function getPubDate ($i) {
		if (is_numeric($i) && is_array($this->ArticleList[$i])) {
			return date('Y-m-d',strtotime($this->ArticleList[$i]['time_published']));
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

	public function countArticles () {
		$QueryString = "SELECT COUNT(*) AS count ";
		$QueryString .= "FROM ";
		$QueryString .= "blog_posts";
		$q = $this->DBObj->prepare($QueryString);
		$q->execute();
		if (is_array($Row = $q->fetch(PDO::FETCH_ASSOC)) && is_numeric($Row['count'])) {
			return $Row['count'];
		}
		else {
			return FALSE;
		}
	}

	public function genPagination ($hits,$start,$limit,$max_page_numbers) {
		if (is_numeric($hits) && is_numeric($start) && is_numeric($limit) && is_numeric($max_page_numbers)) {
			// Validate/correct Start
			if (is_numeric($start)) {
				if ($start <= 0) {
					$start = 0;
				}
				elseif ($start >= $hits) {
					if ($hits >= 1) {
						$start = $hits-1;
					}
					else {
						$start = 0;
					}
				}
				else {
					// OK, passed
				}
			}
			else {
				$start = 0;
			}
			$OutputArray['has_prev_page'] = TRUE;
			$OutputArray['has_next_page'] = TRUE;
			// Make page numbers
			for ($i=0;$i<=$hits;$i=$i+$limit) {
				$PageCounter++;
				if ($i >= $start-($limit-1+($limit*floor($max_page_numbers/2)))) {
					if ($i >= $hits) {
						break;
					}
					if ($PageLimitCounter <= $max_page_numbers) {
						$PageLimitCounter++;
						$OutputArray['pages'][$PageCounter]['pagenumber'] = $PageCounter;
						$OutputArray['pages'][$PageCounter]['start'] = $i;
						if ($start >= $i && $start < ($i+$limit)) {
							$OutputArray['pages'][$PageCounter]['current_page'] = TRUE;
							$OutputArray['start'] = $i;
							$OutputArray['next_page'] = $i + $limit;
							$OutputArray['prev_page'] = $i - $limit;
						}
						else {
							$OutputArray['pages'][$PageCounter]['current_page'] = FALSE;
						}
					}
					// Find last number
					$OutputArray['end'] = $OutputArray['start']+$limit;
					if ($OutputArray['end'] >= $hits) {
						$OutputArray['end'] = $hits;
					}
				}
			}
			// Fix prev/next
			if ($OutputArray['next_page'] >= $hits) {
				$OutputArray['next_page'] = $hits-$limit;
				$OutputArray['has_next_page'] = FALSE;
			}
			if ($OutputArray['prev_page'] < 0) {
				$OutputArray['prev_page'] = 0;
				$OutputArray['has_prev_page'] = FALSE;
			}
			if ($OutputArray['next_page'] == $OutputArray['prev_page']) {
				$OutputArray['next_page'] = 0;
				$OutputArray['has_next_page'] = FALSE;
			}
			if ($OutputArray['next_page'] < 0) {
				$OutputArray['next_page'] = 0;
			}
			// Output
			if (is_array($OutputArray) && $hits > 0) {
				if (is_array($OutputArray['pages'])) {
					$TmpPages = $OutputArray['pages'];
					unset($OutputArray['pages']);
					foreach ($TmpPages as $Page) {
						$OutputArray['pages'][] = $Page;
					}
				}
				return $OutputArray;
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
	* Public functions
	*/

	public function listArticles ($Offset=0,$Limit=FALSE) {
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
			if (is_numeric($Offset) && $Offset>=0 && is_numeric($Limit) && $Limit>0) {
				$QueryString .= "LIMIT ".$Offset.",".$Limit;
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
			if (is_array($TagIDArray) && count($TagIDArray)>0) {
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