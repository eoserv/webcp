<?php

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

$serverconn = fsockopen($serverhost, $serverport, $errno, $errstr, 0.5);
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
				$checklogin = $db->SQL("SELECT username FROM accounts WHERE username = '$' AND password = '$'", strtolower($_POST['username']), substr($_POST['password'],0,12));
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

function trans_form($buffer)
{
	global $csrf;
	$buffer = str_replace('</form>','<input type="hidden" name="csrf" value="'.$csrf.'">'."\n".'</form>', $buffer);
	return $buffer;
}

ob_start('trans_form',0);
