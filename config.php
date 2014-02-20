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

// Print debug info at the bottom of the page (never use this on live servers)
$DEBUG = false;

if (is_file("./config.local.php"))
{
	include "./config.local.php";
}
