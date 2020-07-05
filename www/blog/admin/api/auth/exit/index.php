<?php
require_once('../../../../logic/classes/blog_admin.class.php');
$BlogAdminObj = new blog_admin;
if (is_object($BlogAdminObj) && $BlogAdminObj->signOut()) {
	
}
header('Location: ../../../');