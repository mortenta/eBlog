<?php
class blog_admin {

	private $DBObj;
	private $ErrorMsg;
	private $CookieName = 'embedblogauth';
	private $SiteSettings;
	private $CurrDateTime;
	private $ExpSec = 86400;
	private $UID;
	private $Username;
	private $PostID;
	private $Title;

	function __construct () {
		chdir(dirname(__FILE__));
		require('../../config.php');
		require_once('./blog_db.class.php');
		$this->CurrDateTime = date("Y-m-d H:i:s",time());
		$BlogDBObj = new blog_db;
		if ($BlogDBObj->connectDB() && is_object($BlogDBObj->getDBObj())) {
			$this->DBObj = $BlogDBObj->getDBObj();
		}
		$this->SiteSettings = $SiteSettings;
	}


	/**
	* Setters
	*/

	public function setTitle ($string) {
		$this->Title = $string;
		return TRUE;
	}

	public function setPostID ($id) {
		if (is_numeric($id)) {
			$this->PostID = $id;
			return TRUE;
		}
		else {
			return FALSE;
		}
	}

	/**
	* Getters
	*/

	public function getPostID () {
		return intval($this->PostID);
	}

	public function getErrorMsg () {
		return $this->ErrorMsg;
	}

	public function getSiteName () {
		return $this->SiteSettings['name'];
	}	

	/**
	* Public functions
	*/

	public function listPosts () {
		$QueryString = "SELECT ";
		$QueryString .= "blog_posts.id, ";
		$QueryString .= "blog_posts.time_created, ";
		$QueryString .= "blog_posts.time_updated, ";
		$QueryString .= "blog_posts.time_published, ";
		$QueryString .= "blog_posts.published, ";
		$QueryString .= "blog_posts.url_path, ";
		$QueryString .= "blog_posts.title ";
		$QueryString .= "FROM ";
		$QueryString .= "blog_posts ";
		$QueryString .= "ORDER BY time_updated DESC";
		$QueryString .= "";
		$q = $this->DBObj->prepare($QueryString);
		$q->execute();
		foreach ($q->fetchAll(PDO::FETCH_ASSOC) AS $Row) {
			$PostList[] = $Row;
		}
		if (is_array($PostList)) {
			return $PostList;
		}
		else {
			return array();
		}
	}

	public function createPost () {
		if (is_string($this->Title) && strlen($this->Title) > 3) {
			$QueryString = "INSERT INTO ";
			$QueryString .= "blog_posts ";
			$QueryString .= "SET ";
			$QueryString .= "title=:title, ";
			$QueryString .= "url_path=:url_path, ";
			$QueryString .= "time_created=:currentdatetime, ";
			$QueryString .= "time_updated=:currentdatetime, ";
			$QueryString .= "published=0";
			$q = $this->DBObj->prepare($QueryString);
			$q->bindParam(":title",$this->Title);
			$q->bindParam(":currentdatetime",$this->CurrDateTime);
			$q->bindParam(":url_path",$this->createUniqueURLPath($this->Title));
			if ($q->execute() && is_numeric($this->PostID = $this->DBObj->lastInsertId())) {
				return TRUE;
			}
			else {
				$this->ErrorMsg = 'Unable to save new post in database';
				return FALSE;
			}
		}
		else {
			$this->ErrorMsg = 'The title is too short';
			return FALSE;
		}
	}

	public function updatePost () {
		if (is_numeric($this->PostID) && strlen($this->Title) > 3) {
			// If first time publish, update publish date
			if ($this->Published) {
				$QueryString = "SELECT ";
				$QueryString .= "time_published ";
				$QueryString .= "FROM ";
				$QueryString .= "blog_posts ";
				$QueryString .= "WHERE ";
				$QueryString .= "id=:bid ";
				$QueryString .= "LIMIT 1";
				$q = $this->DBObj->prepare($QueryString);
				$q->bindParam(":bid",$this->PostID);
				$q->execute();
				if (is_array($BlogPost = $q->fetch(PDO::FETCH_ASSOC))) {
					if (is_string($BlogPost['time_published']) && strlen($BlogPost['time_published']) > 5) {
						$DontUpdatePublishTime = TRUE;
					}
				}
			}
			// Update post
			$QueryString = "UPDATE ";
			$QueryString .= "blog_posts ";
			$QueryString .= "SET ";
			$QueryString .= "title=:title, ";
			$QueryString .= "summary=:summary, ";
			$QueryString .= "content=:content, ";
			$QueryString .= "img=:img, ";
			if (is_string($URLPath = $this->createUniqueURLPath($this->URLPath,$this->PostID))) {
				$QueryString .= "url_path=:url_path, ";
			}
			$QueryString .= "published=:published, ";
			if (!$DontUpdatePublishTime && $this->Published) {
				$QueryString .= "time_published=:currentdatetime, ";
			}
			$QueryString .= "time_updated=:currentdatetime ";
			$QueryString .= "WHERE ";
			$QueryString .= "id=:bid ";
			$QueryString .= "LIMIT 1";
			$q = $this->DBObj->prepare($QueryString);
			$q->bindParam(":title",$this->Title);
			$q->bindParam(":summary",$this->Summary);
			$q->bindParam(":content",$this->Content);
			$q->bindParam(":img",$this->Img);
			if (is_string($URLPath)) {
				$q->bindParam(":url_path",$URLPath);
			}
			$q->bindParam(":currentdatetime",$this->InitObj->CurrDateTime);
			if ($this->Published) {
				$Published = 1;
			}
			else {
				$Published = 0;
			}
			$q->bindParam(":published",$Published);
			$q->bindParam(":bid",$this->PostID);
			if ($q->execute()) {
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

	public function loadPost () {
		if (is_numeric($this->PostID)) {
			$QueryString = "SELECT ";
			$QueryString .= "blog_posts.*, ";
			$QueryString .= "blog_tags.id AS tag_id, ";
			$QueryString .= "blog_tags.title AS tag_title, ";
			$QueryString .= "blog_tags.path AS tag_path ";
			$QueryString .= "FROM ";
			$QueryString .= "blog_posts ";
			$QueryString .= "LEFT JOIN blog_post_tag_map ON blog_post_tag_map.postid = blog_posts.id ";
			$QueryString .= "LEFT JOIN blog_tags ON blog_tags.id = blog_post_tag_map.tagid ";
			$QueryString .= "WHERE ";
			$QueryString .= "blog_posts.id=:bid ";
			$q = $this->DBObj->prepare($QueryString);
			$q->bindParam(":bid",$this->PostID);
			$q->execute();
			foreach ($q->fetchAll(PDO::FETCH_ASSOC) AS $Row) {
				if (!is_array($OA)) {
					$OA = $Row;
				}
				if (is_numeric($Row['tag_id'])) {
					$OA['taglist'][] = array(
						'id'	=> $Row['tag_id'],
						'title'	=> $Row['tag_title'],
						'path'	=> $Row['tag_path']
					);
				}
			}
			if (is_array($OA)) {
				unset($OA['tag_id']);
				unset($OA['tag_title']);
				unset($OA['tag_path']);
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

	public function isAuthorized () {
		$Authorized = FALSE;
		if (is_object($this->DBObj)) {
			if (isset($_COOKIE[$this->CookieName])) {
				$QueryString = "SELECT ";
				$QueryString .= "id, username ";
				$QueryString .= "FROM ";
				$QueryString .= "blog_admins ";
				$QueryString .= "WHERE ";
				$QueryString .= "sid=:sid ";
				$QueryString .= "LIMIT 1";
				$q = $this->DBObj->prepare($QueryString);
				$q->bindParam(":sid",$_COOKIE[$this->CookieName]);
				$q->execute();
				if (is_array($Row = $q->fetch(PDO::FETCH_ASSOC))) {
					$this->UID = $Row['id'];
					$this->Username = $Row['username'];
					// Update cookie/sid
					if ($this->writeSIDCookie($this->UID,$_COOKIE[$this->CookieName])) {
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
			else {
				// Cookie not set
				return FALSE;
			}
		}
		else {
			return FALSE;
		}
	}

	public function signIn ($username,$password) {
		usleep(500000);
		if (is_object($this->DBObj)) {
			$QueryString = "SELECT ";
			$QueryString .= "id, username, password, sid, exp ";
			$QueryString .= "FROM ";
			$QueryString .= "blog_admins ";
			$QueryString .= "WHERE ";
			$QueryString .= "username=:username ";
			$QueryString .= "LIMIT 1";
			$q = $this->DBObj->prepare($QueryString);
			$q->bindParam(":username",$username);
			$q->execute();
			if (is_array($Row = $q->fetch(PDO::FETCH_ASSOC))) {
				if (hash_equals($Row['password'],crypt($password,$Row['password']))) {
					if ($this->writeSIDCookie($Row['id'])) {
						return TRUE;
					}
					else {
						$this->ErrorMsg = 'Unable to write cookie/session';
						return FALSE;
					}
				}
				else {
					$this->ErrorMsg = 'Wrong password';
					return FALSE;
				}
			}
			else {
				$this->ErrorMsg = 'User not found';
				return FALSE;
			}
		}
		else {
			$this->ErrorMsg = 'Unable to connect to database';
			return FALSE;
		}
	}

	public function signOut () {
		
	}


	/**
	* Private functions
	*/

	private function createUniqueURLPath ($Path,$BID=FALSE) {
		if (is_string($Path) && is_string($URLPath = $this->toAscii($Path)) && strlen($URLPath) > 3) {
			// Check if's unique
			$QueryString = "SELECT ";
			$QueryString .= "url_path ";
			$QueryString .= "FROM ";
			$QueryString .= "blog_posts ";
			$QueryString .= "WHERE ";
			$QueryString .= "url_path=:url_path ";
			if (is_numeric($BID)) {
				$QueryString .= " AND ";
				$QueryString .= "id != :bid ";
			}
			$QueryString .= "LIMIT 1";
			$q = $this->DBObj->prepare($QueryString);
			$q->bindParam(":url_path",$URLPath);
			if (is_numeric($BID)) {
				$q->bindParam(":bid",$BID);
			}
			$q->execute();
			if (is_array($Row = $q->fetch(PDO::FETCH_ASSOC))) {
				return $this->createUniqueURLPath($Path."-".substr(md5(uniqid(rand(),true)),0,4),$BID);
			}
			else {
				return $URLPath;
			}
		}
		else {
			return md5(uniqid(rand(),true));
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

	private function writeSIDCookie ($id,$SID=NULL) {
		if (is_numeric($id)) {
			if (!is_string($SID)) {
				$SID = md5(uniqid(rand(),true));
			}
			$Expire = time()+$this->ExpSec;
			$this->writeSID($id,$SID,$Expire);
			$this->writeCookie($SID,$Expire);
			return TRUE;
		}
		else {
			return FALSE;
		}
	}

	private function writeSID ($id,$SID,$Expire) {
		$QueryString = "UPDATE ";
		$QueryString .= "blog_admins ";
		$QueryString .= "SET ";
		$QueryString .= "sid=:sid, ";
		$QueryString .= "exp=:exp ";
		$QueryString .= "WHERE ";
		$QueryString .= "id=:id ";
		$QueryString .= "LIMIT 1";
		$q = $this->DBObj->prepare($QueryString);
		$q->bindParam(":sid",$SID);
		$q->bindParam(":exp",date("Y-m-d H:i:s",$Expire));
		$q->bindParam(":id",$id);
		if ($q->execute()) {
			return TRUE;
		}
		else {
			return FALSE;
		}
	}

	private function writeCookie ($SID,$Expire) {
		$Expire = $Expire+(60*60*24*365);
		setcookie($this->CookieName,$SID,$Expire,'/',$_SERVER['HTTP_HOST']);
	}


}