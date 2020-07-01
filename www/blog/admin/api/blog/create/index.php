<?php
require_once('../../../../logic/classes/blog_admin.class.php');
$BlogAdminObj = new blog_admin;
$OA['success'] = FALSE;
if (is_object($BlogAdminObj) && $BlogAdminObj->setTitle($_POST['title']) && $BlogAdminObj->createPost()) {
	$OA['success'] = TRUE;
	$OA['id'] = $BlogAdminObj->getPostID();
}
else {
	$OA['error'] = $BlogAdminObj->getErrorMsg();
}
header('Access-Control-Allow-Methods: GET, POST');
header("Content-type: application/json");
print json_encode($OA);