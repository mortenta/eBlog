<?php
require_once('./logic/classes/blog_viewer.class.php');
$BlogViewerObj = new blog_viewer;
if (is_object($BlogViewerObj) && 
	$BlogViewerObj->setURLPath(str_replace("/","",$_REQUEST['url_path'])) && 
	is_array($PostArray = $BlogViewerObj->loadPost())
) {
	chdir(dirname(__FILE__));
	// Render site
	$title = $PostArray['meta_title'];
	$description = $PostArray['summary'];
	$type = 'article';
	$url_path = '/blog/post/'.date("Y-m-d",strtotime($PostArray['time_published'])).'/'.$PostArray['url_path'].'/';
	$image = '/blog/img/full/'.$PostArray['img'];
	$head = '<script type="text/javascript" src="//platform-api.sharethis.com/js/sharethis.js#property=5a5390f39a57d200135faa0a&product=inline-share-buttons"></script>'."\n";
	$head .= '<script>var headBgFixedBlack = true;</script>'."\n";
	//$bgColor = 'rgb(252,252,252)';
	include('./inc/head.php');
?>


<div class="mainc" style="margin:100px 0 20px 0;">
	<div class="article_layout">
		<article>
			<h1><?php print $PostArray['title']; ?></h1>
			
			<p class="summary">
				<?php print $PostArray['summary']; ?>
			</p>

			<div class="meta">
				<?php if (date("F j, Y",strtotime($PostArray['time_published'])) !== date("F j, Y",strtotime($PostArray['time_updated']))) { ?><i>Updated <time datetime="<?php print date(DATE_ATOM,strtotime($PostArray['time_updated'])); ?>"><?php print date("F j, Y",strtotime($PostArray['time_updated'])); ?></time></i><?php } ?>
			</div>
			
			<?php print $PostArray['content']; ?>
			
			<div class="meta">
				Published <time datetime="<?php print date(DATE_ATOM,strtotime($PostArray['time_published'])); ?>"><?php print date("F j, Y",strtotime($PostArray['time_published'])); ?></time> | 
				<?php if (date("F j, Y",strtotime($PostArray['time_published'])) !== date("F j, Y",strtotime($PostArray['time_updated']))) { ?><i>Updated <time datetime="<?php print date(DATE_ATOM,strtotime($PostArray['time_updated'])); ?>"><?php print date("F j, Y",strtotime($PostArray['time_updated'])); ?></time></i> | <?php } ?>
			</div>

			<br/>
			<div class="sharethis-inline-share-buttons"></div>
		</article>
		<aside>
			<div class="presentation">
				
			</div>
			<ul class="aside_articles">
				<?php
					if (is_array($PostList = $BlogViewerObj->listPosts(7))) {
						foreach ($PostList as $Post) {
				?>
				<li>
					<div class="picture" style="background: url('/blog/img/tn/<?php print $Post['img']; ?>') no-repeat center center;background-size: cover;" onclick="location.href='/blog/post/<?php print date("Y-m-d",strtotime($Post['time_published'])).'/'.$Post['url_path'].'/'; ?>'">
					</div>
					<div class="content">
						<h2><a href="/blog/post/<?php print date("Y-m-d",strtotime($Post['time_published'])).'/'.$Post['url_path'].'/'; ?>"><?php print $Post['title']; ?></a></h2>
					</div>
				</li>
				<?php } } ?>
			</ul>
		</aside>
	</div>
</div>

<?php
include('./inc/foot.php');
}
else {
	print_r('Not found');
}
