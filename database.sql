# ************************************************************
# Sequel Pro SQL dump
# Version 5446
#
# https://www.sequelpro.com/
# https://github.com/sequelpro/sequelpro
#
# Host: 127.0.0.1 (MySQL 5.7.22)
# Database: embedblog
# Generation Time: 2020-07-28 11:27:26 +0000
# ************************************************************


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
SET NAMES utf8mb4;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


# Dump of table blog_admins
# ------------------------------------------------------------

CREATE TABLE `blog_admins` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `sid` varchar(255) DEFAULT NULL,
  `exp` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table blog_post_image_map
# ------------------------------------------------------------

CREATE TABLE `blog_post_image_map` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `postid` int(11) unsigned NOT NULL,
  `filename` varchar(255) DEFAULT NULL,
  `date_uploaded` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `blog_post_image_post_rel` (`postid`),
  CONSTRAINT `blog_post_image_post_rel` FOREIGN KEY (`postid`) REFERENCES `blog_posts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table blog_post_relations
# ------------------------------------------------------------

CREATE TABLE `blog_post_relations` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `postid` int(11) unsigned NOT NULL,
  `relpostid` int(11) unsigned NOT NULL,
  `order` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `blog_post_relations_post` (`postid`),
  KEY `blog_post_relations_relpost` (`relpostid`),
  CONSTRAINT `blog_post_relations_post` FOREIGN KEY (`postid`) REFERENCES `blog_posts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `blog_post_relations_relpost` FOREIGN KEY (`relpostid`) REFERENCES `blog_posts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table blog_post_tag_map
# ------------------------------------------------------------

CREATE TABLE `blog_post_tag_map` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `postid` int(11) unsigned NOT NULL,
  `tagid` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `blog_post_tag_post_rel` (`postid`),
  KEY `blog_post_tag_tag_rel` (`tagid`),
  CONSTRAINT `blog_post_tag_post_rel` FOREIGN KEY (`postid`) REFERENCES `blog_posts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `blog_post_tag_tag_rel` FOREIGN KEY (`tagid`) REFERENCES `blog_tags` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table blog_posts
# ------------------------------------------------------------

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
  `meta_title` varchar(255) DEFAULT NULL,
  `meta_description` varchar(255) DEFAULT NULL,
  `notes` longtext,
  PRIMARY KEY (`id`),
  KEY `url_path` (`url_path`),
  FULLTEXT KEY `fulltext` (`title`,`summary`,`meta_title`,`meta_description`,`content`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table blog_tags
# ------------------------------------------------------------

CREATE TABLE `blog_tags` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) DEFAULT NULL,
  `path` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;




/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
