<?php
require_once('./logic/classes/blog_viewer.class.php');
$BlogViewerObj = new blog_viewer;
if (is_object($BlogViewerObj)) {
	$PostList = $BlogViewerObj->listPosts();
}
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

<div class="mainc dark head_default" style="background:url('./img/head_bg.jpg') no-repeat fixed center center; background-size:cover;">
	<div class="mainc_inner vpad-5x-top">
		<div class="jumbotron">
			<h1>Blog</h1>
			<h2>Updates, tips & tricks and more</h2>
		</div>
	</div>
</div>

<div class="mainc light">
	<div class="mainc_inner vpad-3x">
		<div class="icontainer break-md">
			<ul class="bloglist">
				<?php
					if (is_array($PostList)) {
						foreach ($PostList as $Post) {
				?>
				<li>
					<div class="picture" style="background: url('/blog/img/tn/<?php print $Post['img']; ?>') no-repeat center center;background-size: cover;" onclick="location.href='/blog/post/<?php print date("Y-m-d",strtotime($Post['time_published'])).'/'.$Post['url_path'].'/'; ?>'">
						<div class="date">
							<div class="m"><?php print date("M",strtotime($Post['time_published'])); ?></div>
							<div class="d"><?php print date("j",strtotime($Post['time_published'])); ?></div>
							<div class="y"><?php print date("Y",strtotime($Post['time_published'])); ?></div>
						</div>
					</div>
					<div class="content">
						<h2><a href="/blog/post/<?php print date("Y-m-d",strtotime($Post['time_published'])).'/'.$Post['url_path'].'/'; ?>"><?php print $Post['title']; ?></a></h2>
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