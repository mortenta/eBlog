<?php
// Timezone
date_default_timezone_set('Etc/Greenwich');
// Database settings
$DBSettings['host'] = 'localhost'; // MySQL/MariaDB database server name
$DBSettings['dbname'] = 'embedblog'; // Database name
$DBSettings['username'] = 'embedblog'; // Database username
$DBSettings['password'] = 'password'; // Password to the database
// Paths
$SiteSettings['basepath'] = '/blog'; // Path to blog without trailing slash. Leave blank if on root.
// Site
$SiteSettings['name'] = 'eBlog'; // Unicode icons is your logo? Find at https://unicode-table.com/en/
$SiteSettings['siteurl'] = 'https://www.yoursite.com'; // Website URL without trailing slash
$SiteSettings['use_date_in_url'] = FALSE;