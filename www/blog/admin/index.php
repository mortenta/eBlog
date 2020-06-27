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
			<script src="https://code.jquery.com/jquery-3.5.1.min.js" integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous"></script>
		</head>
		<body>
			<form onsubmit="return false;">
				<input type="email" name="username" placeholder="Username">
				<input type="passord" name="password" placeholder="Password">
				<button data-action="signin">Sign in</button>
			</form>
		</body>
	</html>
<?php
}
?>