<?php
require_once('../../../../logic/classes/blog_admin.class.php');
$BlogAdminObj = new blog_admin;
$OA['success'] = FALSE;
if (is_object($BlogAdminObj)) {
	$BlogAdminObj->setDisplay($_REQUEST['display']);
	$BlogAdminObj->setSkipID($_REQUEST['skipid']);
	$BlogAdminObj->setQuery($_REQUEST['q']);
	$BlogAdminObj->setLimit($_REQUEST['limit']);
	$BlogAdminObj->setOffset($_REQUEST['offset']);
	$OA['success'] = TRUE;
	$OA['list'] = $BlogAdminObj->listPosts();
}
else {
	$OA['error'] = $BlogAdminObj->getErrorMsg();
}
header('Access-Control-Allow-Methods: GET, POST');
header("Content-type: application/json");
print json_encode($OA);