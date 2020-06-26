CREATE TABLE `blog_posts` (
	`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
	`time_created` datetime DEFAULT NULL,
	`time_updated` datetime DEFAULT NULL,
	`time_published` datetime DEFAULT NULL,
	`published` tinyint(1) DEFAULT '0',
	`url_path` varchar(255) DEFAULT NULL,
	`img` varchar(255) DEFAULT NULL,
	`title` varchar(255) DEFAULT NULL,
	`summary` text,
	`content` longtext,
	PRIMARY KEY (`id`),
	KEY `url_path` (`url_path`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE `blog_tags` (
	`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
	`title` varchar(255) DEFAULT NULL,
	`path` varchar(255) DEFAULT NULL,
	PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE `blog_post_tag_map` (
	`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
	`postid` int(11) unsigned NOT NULL,
	`tagid` int(11) unsigned NOT NULL,
	PRIMARY KEY (`id`),
	KEY `blog_post_tag_post_rel` (`postid`),
	KEY `blog_post_tag_tag_rel` (`tagid`),
	CONSTRAINT `blog_post_tag_post_rel` FOREIGN KEY (`postid`) REFERENCES `blog_posts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
	CONSTRAINT `blog_post_tag_tag_rel` FOREIGN KEY (`tagid`) REFERENCES `blog_tags` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;