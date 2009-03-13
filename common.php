<?php

if (!function_exists('hash'))
{
	exit("Could not find the the hash PHP extension.");
}

if (array_search('sha256',hash_algos()) === false)
{
	exit("Could not find the the sha256 hash algorithm.");
}

if (!function_exists('mysql_connect') && !class_exists('PDO'))
{
	exit("Could not find the the mysql or PDO PHP extensions.");
}

define('ADMIN_HGM', 4);
define('ADMIN_GM', 3);
define('ADMIN_GUARDIAN', 2);
define('ADMIN_GUIDE', 1);
define('ADMIN_PLAYER', 0);

define('RACE_WHITE', 0);
define('RACE_YELLOW', 1);
define('RACE_TAN', 2);
define('RACE_PANDA', 3);
define('RACE_SKELETON', 4);
define('RACE_FISH', 5);

require 'config.php';

require 'class/Database.class.php';
require 'class/Template.class.php';
require 'class/Session.class.php';

$db = new Database($dbtype, $dbhost, $dbuser, $dbpass, $dbname);
$tpl = new Template('tpl/'.$template);
$sess = new Session($cpid.'_EOSERVCP');

$tpl->pagetitle = $pagetitle;
$tpl->sitename = $sitename;
$tpl->homeurl = $homeurl;
$tpl->php = $phpext;
$tpl->onlinecharacters = '?';
$tpl->maxplayers = $maxplayers;

if (((isset($checkcsrf) && $checkcsrf) || $_SERVER['REQUEST_METHOD'] == 'POST') && (!isset($_REQUEST['csrf']) || !isset($sess->csrf) || $_REQUEST['csrf'] != $sess->csrf))
{
	header('HTTP/1.1 400 Bad Request');
	exit("<h1>400 - Bad Request</h1>");
}

$tpl->csrf = $sess->csrf = $csrf = mt_rand();

$serverconn = @fsockopen($serverhost, $serverport, $errno, $errstr, 0.5);
$tpl->online = $online = (bool)$serverconn;

if ($online)
{
	$statusstr = '<span class="online">Online</span>';
}
else
{
	$statusstr = '<span class="offline">Offline</span>';
}

$tpl->statusstr = $statusstr;

if (isset($_REQUEST['action']))
{
	switch ($_REQUEST['action'])
	{
		case 'logout':
			unset($sess->username);

		case 'login':
			if (isset($_POST['username'], $_POST['password']))
			{
				$password = hash('sha256',$salt.strtolower($_POST['username']).substr($_POST['password'],0,12));
				$checklogin = $db->SQL("SELECT username FROM accounts WHERE username = '$' AND password = '$'", strtolower($_POST['username']), $password);
				if (empty($checklogin))
				{
					$tpl->message = "Login failed.";
					break;
				}
				else
				{
					$sess->username = $checklogin[0]['username'];
					$tpl->message = "Logged in.";
				}
			}
			break;
	}
}

$tpl->logged = $logged = isset($sess->username);
$tpl->username = $sess->username;
$userdata = $db->SQL("SELECT * FROM accounts WHERE username = '$'", $sess->username);

if ($logged && empty($userdata))
{
	$tpl->message = "Your account has been deleted, logging out...";
	$tpl->logged = $logged = false;
}

$tpl->GM = $GM = false;
$tpl->HGM = $HGM = false;

if (isset($userdata[0]))
{
	$userdata = $userdata[0];
	$chardata = $db->SQL("SELECT * FROM characters WHERE account = '$'", $sess->username);
	foreach ($chardata as $cd)
	{
		if ($cd['admin'] >= ADMIN_GM)
		{
			$tpl->GM = $GM = true;
		}

		if ($cd['admin'] >= ADMIN_HGM)
		{
			$tpl->HGM = $HGM = true;
		}
	}
}
else
{
	$chardata = array();
}

$tpl->numchars = $numchars = count($chardata);
$tpl->userdata = $sess->userdata = $userdata;

function trans_form($buffer)
{
	global $csrf;
	$buffer = str_replace('</form>','<input type="hidden" name="csrf" value="'.$csrf.'">'."\n".'</form>', $buffer);
	return $buffer;
}

ob_start('trans_form',0);
