<?php
require_once('../logic/classes/blog_admin.class.php');
chdir(dirname(__FILE__));
$BlogAdminObj = new blog_admin;
if (is_object($BlogAdminObj) && $BlogAdminObj->isAuthorized()) {
?>
	<!DOCTYPE html>
	<html>
		<head>
			<title>EmbedBlog</title>
		</head>
		<body>

		</body>
	</html>
<?php
} else {
	// Unauthorized, show login
?>
	<!DOCTYPE html>
	<html>
		<head>
			<title>EmbedBlog Login</title>
		</head>
		<body>
			Login
		</body>
	</html>
<?php
}
?>