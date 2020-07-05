<?php
// Timezone
date_default_timezone_set('Etc/Greenwich');
// Database settings
$DBSettings['host'] = 'localhost'; // MySQL/MariaDB database server name
$DBSettings['dbname'] = 'embedblog'; // Database name
$DBSettings['username'] = 'embedblog'; // Database username
$DBSettings['password'] = 'password'; // Password to the database
// Site
$SiteSettings['name'] = '&#9832; Your Blog Name'; // Unicode icons is your logo? Find at https://unicode-table.com/en/
$SiteSettings['default_meta_title'] = 'Your Blog Name'; // The title of the main page
$SiteSettings['default_meta_description'] = 'Description of the site'; // The description of the main page
$SiteSettings['siteurl'] = 'https://www.yoursite.com'; // Website URL without trailing slash
$SiteSettings['sharethis_property_id'] = ''; // Register at sharethis.com, and insert property ID here for share buttons
$SiteSettings['ga_ua_id'] = ''; // Google Analytics UA id (UA-xxxxxxx-1)