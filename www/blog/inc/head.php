<?php 
$hide = FALSE;
?><!DOCTYPE HTML>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html;charset=UTF-8" />
		<title><?php print $title; ?></title>
		<meta name="description" content="<?php print $description; ?>" />
		<meta name="keywords" content="<?php print $keywords; ?>" />
		<meta property="og:type" content="<?php if ($type) { print $type; } else { print 'website'; } ?>" />
		<meta property="og:title" content="<?php print $title; ?>" />
		<meta property="og:description" content="<?php print $description; ?>" />
<?php if ($image) { ?>		<meta property="og:image" content="<?php print $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'].$image; ?>" />
<?php } ?>
<?php if ($url_path) { ?>		<meta property="og:url" content="<?php print $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'].$url_path; ?>" />
<?php } ?><?php print $head; ?>
		<link href="https://fonts.googleapis.com/css?family=Quicksand:300,400,500,700|Handlee" rel="stylesheet">
		<link rel="stylesheet" type="text/css" href="/blog/css/screen.css" media="screen"/>
		<meta name='viewport' content='width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0'/>
		<link rel="shortcut icon" href="/favicon.ico" type="image/x-icon">
		<link rel="icon" href="/favicon.ico" type="image/x-icon">
		<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" integrity="sha256-4+XzXVhsDmqanXGHaHvgh1gMQKX40OUvDEBTu8JcmNs=" crossorigin="anonymous"></script>
	</head>
	<body style="<?php if (is_string($bgColor)) { print "background-color: ".$bgColor.";"; } ?>">
		<div id="head">
			<div class="inner">
				<a href="/" style="color:#fff;text-decoration:none;font-size: 1.4em;position: relative;top: 5px;"><?php print $SiteSettings['name']; ?></a>
				<nav class="menu" id="mainmenu">
					<label for="menutoggle" class="togglebars"><i style="font-style:normal;">&#x2630;</i></label>
					<ul id="menutoggle">
						<li class="mobilehide"><i style="font-style:normal;font-size:3em;line-height:.5em;">&times;</i></li>
						<li><a href="/">Home</a></li>
					</ul>
				</nav>
			</div>
		</div>
		
		<div id="pagewrapper">
			
			