<?php
require_once('../logic/classes/blog_admin.class.php');
chdir(dirname(__FILE__));
$BlogAdminObj = new blog_admin;
if (is_object($BlogAdminObj) && $BlogAdminObj->isAuthorized()) {
?>
	<!DOCTYPE html>
	<html>
		<head>
			<title><?php print $BlogAdminObj->getSiteName(); ?></title>
			<link rel="stylesheet" type="text/css" href="./css/screen.css" media="screen"/>
			<meta name='viewport' content='width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0'/>
			<script src="https://code.jquery.com/jquery-3.5.1.min.js" integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0=" crossorigin="anonymous"></script>
			<script src="./js/ejs.min.js" type="text/javascript"></script>
			<script src="./js/tinymce/js/tinymce/tinymce.min.js" type="text/javascript"></script>
			<script src="./js/tinymce/js/tinymce/jquery.tinymce.min.js" type="text/javascript"></script>
			<script src="./js/moment.min.js" type="text/javascript"></script>
			<script src="./js/admin.js" type="text/javascript"></script>
		</head>
		<body>
			<div id="head">
				<div class="inner" style="color:#fff;padding: 10px;font-size: 1.3em;">
					<div class="grid">
						<div class="row">
							<div class="col col-6"><?php print $BlogAdminObj->getSiteName(); ?></div>
							<div class="col col-6 right"><a href="./api/auth/exit/" class="menu">Sign out</a></div>
						</div>
					</div>
				</div>
			</div>
			<div id="body" class="inner"></div>
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
				<input type="email" name="username" value="username@example.com" placeholder="Username">
				<input type="password" name="password" value="password" placeholder="Password">
				<button data-action="signin">Sign in</button>
			</form>
			<script>
				$(document).ready(function(){
					$('button[data-action="signin"]').click(function(){
						console.log('click');
						$.ajax({
							type:'POST',
							url:'./api/auth/signin/',
							data:{
								username:$('input[name="username"]').val(),
								password:$('input[name="password"]').val()
							},
							dataType:'text',
							cache: false,
							success:function(result) {
								console.log(result);
							}
						});
					});
				});
			</script>
		</body>
	</html>
<?php
}
?>