<?php
require_once('./logic/classes/blog_viewer.class.php');
$BlogViewerObj = new blog_viewer;
if (is_object($BlogViewerObj) && 
	$BlogViewerObj->setURLPath(str_replace("/","",$_REQUEST['url_path'])) && 
	is_array($PostArray = $BlogViewerObj->loadPost())
) {
	chdir(dirname(__FILE__));
	$SiteSettings = $BlogViewerObj->getSiteSettings();
	// Render site
	$title = $PostArray['meta_title'];
	$description = $PostArray['meta_description'];
	$type = 'article';
	$url_path = '/blog/post/'.date("Y-m-d",strtotime($PostArray['time_published'])).'/'.$PostArray['url_path'].'/';
	$image = '/blog/img/full/'.$PostArray['img'];
	if (is_string($SiteSettings['sharethis_property_id']) && strlen($SiteSettings['sharethis_property_id'])>1) {
		$head .= "\t\t".'<script type="text/javascript" src="//platform-api.sharethis.com/js/sharethis.js#property='.$SiteSettings['sharethis_property_id'].'&product=inline-share-buttons"></script>'."\n";
	}
	if (is_string($SiteSettings['ga_ua_id']) && strlen($SiteSettings['ga_ua_id'])>1) {
		$head .= "\t\t".'<!-- Global site tag (gtag.js) - Google Analytics -->'."\n";
		$head .= "\t\t".'<script async src="https://www.googletagmanager.com/gtag/js?id='.$SiteSettings['ga_ua_id'].'"></script>'."\n";
		$head .= "\t\t".'<script>'."\n";
		$head .= "\t\t\t".'window.dataLayer = window.dataLayer || [];'."\n";
		$head .= "\t\t\t".'function gtag(){dataLayer.push(arguments);}'."\n";
		$head .= "\t\t\t".'gtag(\'js\', new Date());'."\n";
		$head .= "\t\t\t".'gtag(\'config\', \''.$SiteSettings['ga_ua_id'].'\');'."\n";
		$head .= "\t\t".'</script>'."\n";
	}
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
