<?php
require_once('./logic/classes/blog_viewer.class.php');
$BlogViewerObj = new blog_viewer;
if (is_object($BlogViewerObj)) {
	$PostList = $BlogViewerObj->listPosts();
}
chdir(dirname(__FILE__));
?>
<!DOCTYPE html>
<html>
	<head>
		<title>EmbedBlog</title>
	</head>
	<body>

		<?php 
			if (is_array($PostList)) {
				print_r($PostList);
			}
		?>


	</body>
</html>