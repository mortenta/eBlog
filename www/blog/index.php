<?php
require_once('./logic/classes/blogviewer_list.class.php');
$Offset = $_REQUEST['offset'];
if (!is_numeric($Offset)) {
	$Offset = 0;
}
$ArticlesPerPage = 5;
$BWLObj = new blogviewer_list;
if (is_object($BWLObj)) {
	$BWLObj->setDateFormat('Y-m-d');
	$BWLObj->setDefaultImage('https://picsum.photos/200/150');
	$BWLObj->setCategory($_REQUEST['tag']);
	if (is_numeric($ArticleCount = $BWLObj->countArticles()) && $BWLObj->listArticles($Offset,$ArticlesPerPage)) {
?>
<!doctype html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title>eBlog</title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="stylesheet" href="https://unpkg.com/purecss@2.0.3/build/pure-min.css" integrity="sha384-cg6SkqEOCV1NbJoCu11+bm0NvBRc8IYLRGXkmNrqUBfTjmMYwNKPWBTIKyw9mHNJ" crossorigin="anonymous">
	<link rel="stylesheet" href="https://unpkg.com/purecss@2.0.3/build/grids-responsive-min.css">
	<style>
		a {
			color:#2196F3;
			text-decoration: none;
		}
		h2 {
			margin: 0;
			font-weight: 100;
		}
	</style>
</head>
<body style="background-color: #f6f7f9;">
	
	<div style="background-color:#363e49;">
		<div style="color:#fff;padding: 8px 0;width: 100%;max-width: 900px;margin: 0 auto;">
			eBlog
		</div>
	</div>
	<div style="width: 100%;max-width: 900px;margin: 1em auto;">
		<?php
			foreach ($BWLObj->getListIndex() as $i) {
		?>
		<div class="pure-g" style="margin: 3em 0;">
			<div class="pure-u-1-5"><img src="<?php print $BWLObj->getImage($i);?>" style="width: 150px;" alt=""/></div>
			<div class="pure-u-4-5">
				<a href="/blog/post/<?php print $BWLObj->getPath($i);?>/"><h2><?php print $BWLObj->getTitle($i); ?></h2></a>
				<p><?php print $BWLObj->getSummary($i);?></p>
				<small>Created: <?php print $BWLObj->getTimeCreated($i);?> | Updated: <?php print $BWLObj->getTimeUpdated($i);?></small>
				<ul>
					<?php foreach ($BWLObj->getTagList($i) as $Tag) { ?>
						<li><a href="./?tag=<?php print $BWLObj->getTagPath($Tag); ?>"><?php print $BWLObj->getTagTitle($Tag); ?></a></li>
					<?php } ?>
				</ul>
			</div>
		</div>
		<?php
			}
			if (is_array($PageArray = $BWLObj->genPagination($ArticleCount,$Offset,$ArticlesPerPage,3))) {
				print '<ul class="pagination">';
				if ($PageArray['has_prev_page']) {
					print '<li><a href="./?offset='.$PageArray['prev_page'].'">Previous</a></li>';
				}
				if (is_array($PageArray['pages'])) {
					foreach ($PageArray['pages'] as $Page) {
						print '<li><a href="./?offset='.$Page['start'].'" class="';
						print $Page['current_page']?'selected':'';
						print '">'.$Page['pagenumber'].'</a></li>';
					}
				}
				if ($PageArray['has_next_page']) {
					print '<li><a href="./?offset='.$PageArray['next_page'].'">Next</a></li>';
				}
				
				print '</ul>';
			}
		?>
	</div>

</body>
</html>
<?php
	}
}
?>