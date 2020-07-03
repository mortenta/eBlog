<?php
require_once('../../../../../logic/classes/blog_admin.class.php');
$BlogAdminObj = new blog_admin;
$OA['success'] = FALSE;
if (is_object($BlogAdminObj) && $BlogAdminObj->setPostID($_REQUEST['id'])) {
	if ($BlogAdminObj->uploadImage($_FILES['file'])) {
		$OA['success'] = TRUE;
		$OA['filename'] = $BlogAdminObj->getFilename();
	}
	else {
		$OA['success'] = FALSE;
	}
}
else {
	$OA['error'] = $BlogAdminObj->getErrorMsg();
}
header('Access-Control-Allow-Methods: GET, POST');
header("Content-type: application/json");
print json_encode($OA);