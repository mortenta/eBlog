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
	private $Summary;
	private $Content;
	private $Img;
	private $Published;
	private $URLPath;
	private $Filename;
	private $Display;

	function __construct () {
		chdir(dirname(__FILE__));
		require('../../config.php');
		require_once('./blog_db.class.php');
		require_once('./blog_gd.class.php');
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

	public function setSummary ($string) {
		$this->Summary = $string;
		return TRUE;
	}
	
	public function setContent ($string) {
		$this->Content = $string;
		return TRUE;
	}
	
	public function setImg ($string) {
		$this->Img = $string;
		return TRUE;
	}
	
	public function setPublished ($bool) {
		if ($bool == '1') {
			$this->Published = TRUE;
		}
		else {
			$this->Published = FALSE;
		}
		return TRUE;
	}
	
	public function setURLPath ($string) {
		$this->URLPath = $this->toAscii($string);
		return TRUE;
	}

	public function setMetaTitle ($string) {
		$this->MetaTitle = $string;
		return TRUE;
	}

	public function setMetaDescription ($string) {
		$this->MetaDescription = $string;
		return TRUE;
	}

	// List filters

	public function setDisplay ($string) {
		if (in_array(strtolower($string),array('all','published','unpublished'))) {
			$this->Display = strtolower($string);
		}
		else {
			$this->Display = 'all';
		}
		return TRUE;
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

	public function getFilename () {
		return $this->Filename;
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
		$QueryString .= "WHERE ";
		if ($this->Display=='published') {
			$QueryString .= "published=1 ";
		}
		elseif ($this->Display=='unpublished') {
			$QueryString .= "published=0 ";
		}
		else {
			$QueryString .= "(published=1 OR published=0) ";
		}
		$QueryString .= "ORDER BY time_updated DESC";
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
			$QueryString .= "meta_title=:meta_title, ";
			$QueryString .= "meta_description=:meta_description, ";
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
			$q->bindParam(":meta_title",$this->MetaTitle);
			$q->bindParam(":meta_description",$this->MetaDescription);
			if (is_string($URLPath)) {
				$q->bindParam(":url_path",$URLPath);
			}
			$q->bindParam(":currentdatetime",$this->CurrDateTime);
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

	public function publishPost () {
		if (is_numeric($this->PostID)) {
			$QueryString = "UPDATE ";
			$QueryString .= "blog_posts ";
			$QueryString .= "SET ";
			$QueryString .= "published=1, ";
			$QueryString .= "time_published = IF(time_published IS NULL, NOW(), time_published) ";
			$QueryString .= "WHERE ";
			$QueryString .= "id=:bid ";
			$QueryString .= "LIMIT 1";
			$q = $this->DBObj->prepare($QueryString);
			$q->bindParam(":bid",$this->PostID);
			if ($q->execute()) {
				return TRUE;
			}
			else {
				return FALSE;
			}
		}
		else {
			$this->ErrorMsg = 'Invalid PostID';
			return FALSE;
		}
	}

	public function unpublishPost () {
		if (is_numeric($this->PostID)) {
			$QueryString = "UPDATE ";
			$QueryString .= "blog_posts ";
			$QueryString .= "SET ";
			$QueryString .= "published=0 ";
			$QueryString .= "WHERE ";
			$QueryString .= "id=:bid ";
			$QueryString .= "LIMIT 1";
			$q = $this->DBObj->prepare($QueryString);
			$q->bindParam(":bid",$this->PostID);
			if ($q->execute()) {
				return TRUE;
			}
			else {
				return FALSE;
			}
		}
		else {
			$this->ErrorMsg = 'Invalid PostID';
			return FALSE;
		}
	}

	public function deletePost () {
		if (is_numeric($this->PostID)) {
			// List and delete blog post images
			if (is_array($ImageList = $this->listPostImages())) {
				$DirPath = '../../img/';
				foreach ($ImageList as $Image) {
					unlink($DirPath.'tn/'.$Image['filename']);
					unlink($DirPath.'full/'.$Image['filename']);
				}
			}
			// Delete blog post
			$QueryString = "DELETE FROM ";
			$QueryString .= "blog_posts ";
			$QueryString .= "WHERE ";
			$QueryString .= "id=:id ";
			$QueryString .= "LIMIT 1";
			$q = $this->DBObj->prepare($QueryString);
			$q->bindParam(":id",$this->PostID);
			if ($q->execute()) {
				return TRUE;
			}
			else {
				$this->ErrorMsg = 'Unable to delete post in database';
				return FALSE;
			}
		}
		else {
			$this->ErrorMsg = 'Invalid post ID';
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

	public function listPostImages () {
		if (is_numeric($this->PostID)) {
			$QueryString = "SELECT ";
			$QueryString .= "id, ";
			$QueryString .= "filename ";
			$QueryString .= "FROM ";
			$QueryString .= "blog_post_image_map ";
			$QueryString .= "WHERE ";
			$QueryString .= "postid=:postid ";
			$QueryString .= "ORDER BY ";
			$QueryString .= "date_uploaded";
			$q = $this->DBObj->prepare($QueryString);
			$q->bindParam(":postid",$this->PostID);
			$q->execute();
			foreach ($q->fetchAll(PDO::FETCH_ASSOC) AS $Row) {
				$OA[] = $Row;
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

	public function uploadImage ($FileArray) {
		if (is_numeric($this->PostID) && is_array($FileArray) && $FileArray['error'] == 0 && is_file($FileArray['tmp_name'])) {
			$DirPath = '../../img/';
			// Create folders if non existent
			if (!is_dir($DirPath.'tn/')) {
				mkdir($DirPath.'tn/',0755);
			}
			if (!is_dir($DirPath.'full/')) {
				mkdir($DirPath.'full/',0755);
			}
			// Create unique filename
			$Pathinfo = pathinfo($FileArray['name']);
			$Filename = date("Y-m-d").'-'.$this->toAscii($Pathinfo['filename']).'-'.substr(md5(uniqid(rand(),true)),0,10).'.'.$Pathinfo['extension'];
			$this->Filename = $Filename;
			// Scale and save
			if (is_object($GD = new blog_gd)) {
				$GD->setImgStr(file_get_contents($FileArray['tmp_name']));
				$GD->setImageType($Pathinfo['extension']);
				// Full imsage
				if ($GD->create()) {
					$GD->setNewWidth(1100);
					if ($GD->getOrigWidth() > $GD->getNewWidth()) {
						$GD->resize();
					}
					chdir(__DIR__);
					if ($GD->saveFile($DirPath.'full/'.$Filename)) {
						$FullSaved = TRUE;
					}
				}
			}
			if (is_object($GD = new blog_gd)) {
				$GD->setImgStr(file_get_contents($FileArray['tmp_name']));
				$GD->setImageType($Pathinfo['extension']);
				// Full imsage
				if ($GD->create()) {
					$GD->setNewWidth(500);
					if ($GD->getOrigWidth() > $GD->getNewWidth()) {
						$GD->resize();
					}
					chdir(__DIR__);
					if ($GD->saveFile($DirPath.'tn/'.$Filename)) {
						$TNSaved = TRUE;
					}
				}
			}
			if ($FullSaved && $TNSaved) {
				// Create db record
				$QueryString = "INSERT INTO ";
				$QueryString .= "blog_post_image_map ";
				$QueryString .= "SET ";
				$QueryString .= "postid=:postid, ";
				$QueryString .= "filename=:filename, ";
				$QueryString .= "date_uploaded=:currentdatetime";
				$q = $this->DBObj->prepare($QueryString);
				$q->bindParam(":postid",$this->PostID);
				$q->bindParam(":filename",$Filename);
				$q->bindParam(":currentdatetime",$this->CurrDateTime);
				if ($q->execute()) {
					return TRUE;
				}
				else {
					unlink($DirPath.'tn/'.$Filename);
					unlink($DirPath.'full/'.$Filename);
					$this->ErrorMsg = 'Unable to write to database';
					return FALSE;
				}
			}
			else {
				// Delete files
				unlink($DirPath.'tn/'.$Filename);
				unlink($DirPath.'full/'.$Filename);
				$this->ErrorMsg = 'Unable to resize and save image files';
				return FALSE;
			}
		}
		else {
			$this->ErrorMsg = 'Missing file or postID';
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
		if (is_object($this->DBObj)) {
			if (isset($_COOKIE[$this->CookieName])) {
				$QueryString = "UPDATE ";
				$QueryString .= "blog_admins ";
				$QueryString .= "SET ";
				$QueryString .= "sid=NULL, ";
				$QueryString .= "exp=NULL ";
				$QueryString .= "WHERE ";
				$QueryString .= "sid=:sid ";
				$QueryString .= "LIMIT 1";
				$q = $this->DBObj->prepare($QueryString);
				$q->bindParam(":sid",$_COOKIE[$this->CookieName]);
				if ($q->execute()) {
					return TRUE;
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

if(!function_exists('hash_equals')) {
	function hash_equals($str1, $str2) {
		if(strlen($str1) != strlen($str2)) {
			return false;
		}
		else {
			$res = $str1 ^ $str2;
			$ret = 0;
			for($i = strlen($res) - 1; $i >= 0; $i--) {
				$ret |= ord($res[$i]);
			}
			return !$ret;
		}
	}
}