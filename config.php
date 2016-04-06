<?php

// Used for the session ID, must be unique if running more than one WebCP on a server
$cpid = 'MyServerName';

// Link and site name used in the header
$homeurl = 'http://localhost/';
$sitename = 'My Server Name';

// Password salt, this must be the same as in EOSERV's config.ini
$salt = 'ChangeMe';
$seose_compat = false;
$seose_compat_key = 'D4q9_f30da%#q02#)8';

// Database connection info
$dbtype = 'mysql';
$dbhost = 'localhost';
$dbuser = 'eoserv';
$dbpass = 'eoserv';
$dbname = 'eoserv';

// Template file to use, directory ./tpl/$template/ must exist
$template = 'green';

// Page file extension, keep this as .php unless you know you can change it
$phpext = '.php';

// Server details, the online list is grabbed from here
$serverhost = 'localhost';
$serverport = 8078;

// Purely cosmetic, number of players that can be online at once
$maxplayers = 200;

// How many items to show per page (eg. "All Character" list)
$perpage = 100;

// How many players are shown on the top players page
$topplayers = 100;

// How many guilds are shown on the top guilds page 
$topguilds = 100;

// How many seconds to keep the online list/status cached, reducing this will increase the accuracy of the online list/status, but increase server load
$onlinecache = 60;

// Where the pub files are found, no trailing slash
$pubfiles = './pub';

// Caches pub file data to a native PHP format, disabling this will use a lot more CPU power than neccessary
$pubcache = true;

// Turning this on will cause HTTP 400 errors if you refresh a form, but provides a little more security
$dynamiccsrf = false;

// Rate-limits authentication requests by IP address
// Driver can either be 'none', 'file' or 'db'
// DB driver requires an additional table added to the database (see install.sql)
//$loginrate_driver = 'none';
$loginrate_driver = 'file';
//$loginrate_driver = 'db';

// File path for loginrate 'file' driver, requires a trailing slash
// For privacy reasons this path shouldn't be accessible via your webserver
$loginrate_file_path = './.htloginrate/';

// Filename salt for loginrate 'file' driver
// This should be changed to something random
$loginrate_file_salt = 'ChangeMe';

// Database table for loginrate 'db' driver
$loginrate_db_table = 'webcp_loginrate';

//   Require a CAPTCHA after:
//    - more than 5 requests in an hour
//    - or, more than 20 requests in a day
//   Make blank to disable.
$loginrate_captcha = '5:3600; 20:86400';
//   Rejects requests after:
//    - more than 2 requests in 10 seconds
//    - or, more than 10 requests in 5 minutes
//    - or, more than 100 requests in 24 hours
$loginrate = '2:10; 10:300; 100:86400';

// List of fonts to use for CAPTCHA generation
// Leave blank to use PHP's basic pixel font instead.
$captcha_fonts = array(
	'/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf',
	'/usr/share/fonts/truetype/dejavu/DejaVuSerif.ttf',
);

// Setting this to false will disable the display the sum of bank gold on the front page
// Setting this to true will always display the sum of bank gold on the front page
// Leaving it unset will display only when there are less than 10,000 characters
//$showbankgold = false;

// Optional path to a key file to use for encrypting player information
// Should contain a number of random bytes (54 bytes) and not be made available via web
// A key will be automatically generated if the file is present but empty
// If not used, player IP addresses, computer names and HDIDs will be shown to all admins
//$ipcrypt = '/home/www-user/webcp-ipcrypt.key';

// Print debug info at the bottom of the page (never use this on live servers)
$DEBUG = false;

if (is_file("./config.local.php"))
{
	include "./config.local.php";
}
