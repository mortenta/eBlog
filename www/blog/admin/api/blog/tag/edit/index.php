<?php
require_once('../../../../../logic/classes/blog_admin.class.php');
$BlogAdminObj = new blog_admin;
$OA['success'] = FALSE;
if (is_object($BlogAdminObj)) {
	$BlogAdminObj->setTagID($_POST['tid']);
	$BlogAdminObj->setTagTitle($_POST['title']);
	$BlogAdminObj->setTagPath($_POST['path']);
	if ($BlogAdminObj->editTag()) {
		$OA['success'] = TRUE;
		$OA['tid'] = $BlogAdminObj->getTagID();
		$OA['title'] = $BlogAdminObj->getTagTitle();
		$OA['path'] = $BlogAdminObj->getTagPath();
	}
}
else {
	$OA['error'] = $BlogAdminObj->getErrorMsg();
}
header('Access-Control-Allow-Methods: GET, POST');
header("Content-type: application/json");
print json_encode($OA);