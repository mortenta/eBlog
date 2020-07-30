<?php
require_once('./logic/classes/blogviewer_article.class.php');
$BWAObj = new blogviewer_article;
if (is_object($BWAObj) && $BWAObj->setURLPath($_REQUEST['url_path']) && $BWAObj->loadArticle() && $BWAObj->loadRelated()) {
?>
<!doctype html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title><?php print $BWAObj->getMetaTitle(); ?></title>
	<meta name="description" content="<?php print $BWAObj->getMetaDescription(); ?>" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="stylesheet" href="https://unpkg.com/purecss@2.0.3/build/pure-min.css" integrity="sha384-cg6SkqEOCV1NbJoCu11+bm0NvBRc8IYLRGXkmNrqUBfTjmMYwNKPWBTIKyw9mHNJ" crossorigin="anonymous">
	<link rel="stylesheet" href="https://unpkg.com/purecss@2.0.3/build/grids-responsive-min.css">
	<style>
		a {
			color:#2196F3;
			text-decoration: none;
		}
		h1,h2 {
			margin: 0 0 1em 0;
			font-weight: 100;
		}
		h3 {
			margin: 0 0 .3em 0;
			font-weight: 100;
			font-size: 18px;
		}
		.summary {
			font-size: 1.2em;
			line-height: 1.3em;
			font-weight: 300;
			margin-bottom: 2em;
		}
		.rel {
			margin-bottom: 2em;
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
		<div class="pure-g">
			<div class="pure-u-4-5">
				<h1><?php print $BWAObj->getTitle(); ?></h1>
				<div class="summary">
					<?php print $BWAObj->getSummary(); ?>
				</div>
				<?php print $BWAObj->getContent(); ?>
			</div>
			<div class="pure-u-1-5">
				<?php
					foreach ($BWAObj->getRelatedIndex() as $i) {
				?>
					<div class="rel">
						<a href="/blog/post/<?php print $BWAObj->getRelPath($i); ?>/"><h3><?php print $BWAObj->getRelTitle($i); ?></h3></a>
						<?php print $BWAObj->getRelSummary($i); ?>
					</div>
				<?php
					}
				?>
			</div>
		</div>
	</div>
</body>
</html>
<?php 
} else {
	print "404 Not found";
}
?>