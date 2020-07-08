<?php
require_once('./logic/classes/blog_viewer.class.php');
$BlogViewerObj = new blog_viewer;
if (is_object($BlogViewerObj)) {
	$PostList = $BlogViewerObj->listPosts();
}
chdir(dirname(__FILE__));
$SiteSettings = $BlogViewerObj->getSiteSettings();
// Render site
$title = $SiteSettings['default_meta_title'];
$description = $SiteSettings['default_meta_description'];
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

<div class="mainc light" style="margin-top: 30px;">
	<div class="mainc_inner vpad-3x">
		<div class="icontainer break-md">
			<ul class="bloglist">
				<?php
					if (is_array($PostList)) {
						foreach ($PostList as $Post) {
				?>
				<li>
					<div class="picture" style="background: url('/blog/img/tn/<?php print $Post['img']; ?>') no-repeat center center;background-size: cover;" onclick="location.href='/blog/post/<?php print $Post['url_path'].'/'; ?>'">
						<div class="date">
							<div class="m"><?php print date("M",strtotime($Post['time_published'])); ?></div>
							<div class="d"><?php print date("j",strtotime($Post['time_published'])); ?></div>
							<div class="y"><?php print date("Y",strtotime($Post['time_published'])); ?></div>
						</div>
					</div>
					<div class="content">
						<h2><a href="/blog/post/<?php print $Post['url_path'].'/'; ?>"><?php print $Post['title']; ?></a></h2>
						<div class="summary"><?php print $Post['summary']; ?></div>
						<?php if (is_array($Post['taglist'])) { ?>
						<ul class="taglist">
							<?php foreach ($Post['taglist'] as $Tag) { ?>
								<li><a href="/blog/archive/?c=<?php print $Tag['path']; ?>"><?php print $Tag['title']; ?></a></li>
							<?php } ?>
						</ul>
						<?php } ?>
					</div>
				</li>
				<?php } } ?>
			</ul>
		</div>
	</div>
</div>

<?php
include('./inc/foot.php');
?>