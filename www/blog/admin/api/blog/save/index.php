<?php
require_once('../../../../logic/classes/blog_admin.class.php');
$BlogAdminObj = new blog_admin;
$OA['success'] = FALSE;
if (is_object($BlogAdminObj)) {
	$BlogAdminObj->setPostID($_POST['id']);
	$BlogAdminObj->setURLPath($_POST['url_path']);
	$BlogAdminObj->setTitle($_POST['title']);
	$BlogAdminObj->setSummary($_POST['summary']);
	$BlogAdminObj->setImg($_POST['img']);
	$BlogAdminObj->setContent($_POST['content']);
	$BlogAdminObj->setMetaTitle($_POST['meta_title']);
	$BlogAdminObj->setMetaDescription($_POST['meta_description']);
	$BlogAdminObj->setPublished($_POST['published']);
	if ($BlogAdminObj->updatePost()) {
		$OA['success'] = TRUE;
	}
	else {
		$OA['error'] = $BlogAdminObj->getErrorMsg();
	}
}
else {
	$OA['error'] = $BlogAdminObj->getErrorMsg();
}
header('Access-Control-Allow-Methods: GET, POST');
header("Content-type: application/json");
print json_encode($OA);