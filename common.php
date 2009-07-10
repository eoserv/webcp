<?php

function Number($b1, $b2 = 254, $b3 = 254, $b4 = 254)
{
	if ($b1 == 0 || $b1 == 254) $b1 = 1;
	if ($b2 == 0 || $b2 == 254) $b2 = 1;
	if ($b3 == 0 || $b3 == 254) $b3 = 1;
	if ($b4 == 0 || $b4 == 254) $b4 = 1;

	--$b1;
	--$b2;
	--$b3;
	--$b4;

	return ($b4*16194277 + $b3*64009 + $b2*253 + $b1);
}

function timeago_full($pre,$now=NULL,$suffix=true)
{
	if ($now === NULL)
	{
		$now = time();
	}

	$times = array(
		array(1,'second'),
		array(60,'minute'),
		array(60*60,'hour'),
		array(24*60*60,'day'),
		array(7*60*60*24,'week'),
		array(52*60*60*24*7,'year'),
	);

	$diff = $now - $pre;

	if ($suffix)
	{
		$ago = ($diff >= 0)?' ago':' from now';
	}
	else
	{
		$ago = '';
	}

	$diff = abs($diff);
	$text = '';

	for ($i=count($times)-1; $i>=0; --$i)
	{
		$x = floor($diff/$times[$i][0]);
		$diff -= $x*$times[$i][0];
		if ($x > 0)
		{
			$text .= "$x ".$times[$i][1].(($x == 1)?'':'s').', ';
		}
	}

	if ($text == '')
	{
		$text = '0 seconds, ';
	}

	return substr($text,0,-2).$ago;
}

function webcp_error_handler($errno, $errstr, $errfile, $errline)
{
	global $tpl;
	$errfile = basename($errfile);
	if ((error_reporting() & $errno) != $errno)
	{
		return;
	}
	if (isset($tpl) && !$tpl->MainExecuted())
	{
		$tpl->error = "$errstr ($errfile:$errline)";
		$tpl->Execute('error');
		exit;
	}
	else
	{
		exit("<br><b>Error:</b> $errstr ($errfile:$errline)<br>");
	}
}
set_error_handler("webcp_error_handler");

function webcp_exception_handler($e)
{
	$classname = 'Exception';
	$reflector = new ReflectionClass($e);
	$classname = $reflector->getName();
	webcp_error_handler(E_ERROR, "Uncaught $classname: ".$e->getMessage(), $e->getFile(), $e->getLine());
}
set_exception_handler("webcp_exception_handler");

function webcp_debug_info()
{
	global $db;
	global $starttime;
	$exectime = number_format((microtime(true) - $starttime)*1000, 1);
	echo "Total execution time: $exectime ms<br>";
	foreach ($db->Debug() as $query)
	{
		$exectime = number_format($query[1], 1);
		echo htmlentities($query[0])." -- ($exectime ms)<br>";
	}
}

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
define('RACE_ORC', 3);
define('RACE_PANDA', 4);
define('RACE_SKELETON', 5);
define('RACE_FISH', 6);

require 'config.php';

if (!empty($DEBUG))
{
	$starttime = microtime(true);
	register_shutdown_function('webcp_debug_info');
}

require 'class/Database.class.php';
require 'class/Template.class.php';
require 'class/Session.class.php';

try
{
	$db = new Database($dbtype, $dbhost, $dbuser, $dbpass, $dbname);
}
catch (Exception $e)
{
	exit("Database connection failed. (".$e->getMessage().")");
}
$tpl = new Template('tpl/'.$template, true);
$sess = new Session($cpid.'_EOSERVCP');

$tpl->pagetitle = $pagetitle;
$tpl->sitename = $sitename;
$tpl->homeurl = $homeurl;
$tpl->php = $phpext;
$tpl->onlinecharacters = 0;
$tpl->maxplayers = $maxplayers;
$tpl->serverhost = $serverhost;
$tpl->serverport = $serverport;


if (!is_dir($pubfiles))
{
	exit("Directory not found: $pubfiles");
}

if (!is_file($pubfiles.'/dat001.eif'))
{
	exit("File not found: $pubfiles/dat001.eif");
}

if (!is_file($pubfiles.'/dat001.ecf'))
{
	exit("File not found: $pubfiles/dat001.ecf");
}

if (!empty($NEEDPUB))
{
	require 'class/EIFReader.class.php';

	if ($pubcache && file_exists('eif.cache') && filemtime('eif.cache') < filemtime($pubfiles.'/dat001.eif'))
	{
		$eoserv_items = unserialize(file_get_contents('eif.cache'));
	}
	else
	{
		$eoserv_items = new EIFReader("$pubfiles/dat001.eif");
		if ($pubcache)
		{
			file_put_contents('eif.cache', serialize($eoserv_items));
		}
	}

	require 'class/ECFReader.class.php';

	if ($pubcache && file_exists('ecf.cache') && filemtime('ecf.cache') < filemtime($pubfiles.'/dat001.ecf'))
	{
		$eoserv_classes = unserialize(file_get_contents('ecf.cache'));
	}
	else
	{
		$eoserv_classes = new ECFReader("$pubfiles/dat001.ecf");
		if ($pubcache)
		{
			file_put_contents('ecf.cache', serialize($eoserv_classes));
		}
	}
}

if (((isset($checkcsrf) && $checkcsrf) || $_SERVER['REQUEST_METHOD'] == 'POST') && (!isset($_REQUEST['csrf']) || !isset($sess->csrf) || $_REQUEST['csrf'] != $sess->csrf))
{
	header('HTTP/1.1 400 Bad Request');
	exit("<h1>400 - Bad Request</h1>");
}

if ($dynamiccsrf || !isset($sess->csrf))
{
	$tpl->csrf = $sess->csrf = $csrf = mt_rand();
}
else
{
	$tpl->csrf = $csrf = $sess->csrf;
}

if (!file_exists('online.cache') || filemtime('online.cache')+$onlinecache < time())
{
	$serverconn = @fsockopen($serverhost, $serverport, $errno, $errstr, 2.0);
	$tpl->online = $online = (bool)$serverconn;
	$onlinelist = array();
	if ($online)
	{
		$request_online = chr(5).chr(254).chr(1).chr(22).chr(254).chr(255);
		fwrite($serverconn, $request_online);
		$raw = fread($serverconn, 1024*256); // Read up to 256KB of data
		fclose($serverconn);
		$raw = substr($raw, 5); // length, ID, replycode
		$chars = Number(ord($raw[0]), ord($raw[1])); $raw = substr($raw, 2); // Number of characters
		$raw = substr($raw, 1); // separator
		for ($i = 0; $i < $chars; ++$i)
		{
			$newchar = array(
				'name' => '',
				'title' => '',
				'admin' => '',
				'class' => '',
				'guild' => '',
			);

			$pos = strpos($raw, chr(255));
			$newchar['name'] = substr($raw, 0, $pos);
			$raw = substr($raw, $pos+1);

			$pos = strpos($raw, chr(255));
			$newchar['title'] = substr($raw, 0, $pos);
			$raw = substr($raw, $pos+1);
			
			$raw = substr($raw, 1); // ?

			$newchar['admin'] = Number(ord(substr($raw, 0, 1)));
			$newchar['admin'] = ($newchar['admin'] == 4 || $newchar['admin'] == 5 || $newchar['admin'] == 9 || $newchar['admin'] == 10);
			$raw = substr($raw, 1);

			$newchar['class'] = Number(ord(substr($raw, 0, 1)));
			$raw = substr($raw, 1);

			$newchar['guild'] = trim(substr($raw, 0, 3));
			$raw = substr($raw, 3);

			$raw = substr($raw, 1); // separator

			$onlinelist[] = $newchar;
		}
		ksort($onlinelist);
		file_put_contents('online.cache', serialize($onlinelist));
	}
	else
	{
		file_put_contents('online.cache', 'OFFLINE');
	}
}
else
{
	$onlinedata = file_get_contents('online.cache');
	if ($onlinedata == 'OFFLINE')
	{
		$tpl->online = $online = false;
	}
	else
	{
		$tpl->online = $online = true;
		$onlinelist = unserialize($onlinedata);
	}
}

$tpl->onlinecharacters = isset($onlinelist)?count($onlinelist):0;

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

$tpl->GUARDIAN = $GUARDIAN = false;
$tpl->GM = $GM = false;
$tpl->HGM = $HGM = false;

$chardata_guilds = array();
if (isset($userdata[0]))
{
	$userdata = $userdata[0];
	$chardata = $db->SQL("SELECT * FROM characters WHERE account = '$'", $sess->username);
	foreach ($chardata as $cd)
	{
		if ($cd['admin'] >= ADMIN_GUARDIAN)
		{
			$tpl->GUARDIAN = $GUARDIAN = true;
		}

		if ($cd['admin'] >= ADMIN_GM)
		{
			$tpl->GM = $GM = true;
		}

		if ($cd['admin'] >= ADMIN_HGM)
		{
			$tpl->HGM = $HGM = true;
		}
		
		if ($cd['guild'])
		{
			if (!isset($chardata_guilds[$cd['guild']]))
			{
				$chardata_guilds[$cd['guild']] = array(
					'leader' => false
				);
			}
			if ($cd['guild_rank'] == 1)
			{
				$chardata_guilds[$cd['guild']]['leader'] = true;
			}
		}
	}
}
else
{
	$chardata = array();
}

$tpl->numchars = $numchars = count($chardata);
$tpl->userdata = $sess->userdata = $userdata;
$tpl->chardata_guilds = $chardata_guilds;

function trans_form($buffer)
{
	global $csrf;
	$buffer = str_replace('</form>','<input type="hidden" name="csrf" value="'.$csrf.'">'."\n".'</form>', $buffer);
	return $buffer;
}

ob_start('trans_form',0);

function generate_pagination($pages, $page, $prefix = '')
{
	if (strpos($prefix, '?') === false)
	{
		$prefix .= '?';
	}
	else
	{
		$prefix .= '&';
	}
	$ret = "<div class=\"pagination\">";
	if ($page == 1)
	{
		$ret .= "&lt;&lt; ";
	}
	else
	{
		$ret .= "<a href=\"{$prefix}page=".($page-1)."\">&lt;&lt;</a> ";
	}
	$elip = false;
	for ($i = 1; $i <= $pages; ++$i)
	{
		if ($pages < 15 || abs($i - $page) < 3 || abs($i - $pages) < 2 || abs($i - 1) < 2)
		{
			if ($i == $page)
			{
				$ret .= "<span class=\"current\">$i</span> ";
			}
			else
			{
				$ret .= "<a href=\"{$prefix}page=$i\">$i</a> ";
			}
			$elip = true;
		}
		else
		{
			if ($elip)
			{
				$ret .= "... ";
				$elip = false;
			}
		}
	}
	
	if ($page == $pages)
	{
		$ret .= "&gt;&gt;";
	}
	else
	{
		$ret .= "<a href=\"{$prefix}page=".($page+1)."\">&gt;&gt;</a>";
	}
	
	$ret .= "</div>";

	return $ret;
}

function unserialize_inventory($str)
{
global $eoserv_items;
	$items = explode(';', $str);
	array_pop($items);

	foreach ($items as &$item)
	{
		$xitem = explode(',', $item);
		$item = array(
			'id' => (int)$xitem[0],
			'name' => $eoserv_items->Get($xitem[0])->name,
			'amount' => $xitem[1]
		);
	}
	unset($item);
	
	return $items;
}

function unserialize_paperdoll($str)
{
global $eoserv_items;
	$items = explode(',', $str);
	array_pop($items);
	
	if (count($items) != 15)
	{
		$items = array_fill(0, 15, 0);
	}

	foreach ($items as &$item)
	{
		$item = array(
			'id' => (int)$item,
			'slot' => EIFReader::TypeString($eoserv_items->Get($item)->type),
			'name' => $eoserv_items->Get($item)->name
		);
	}
	unset($item);

	return $items;
}

function unserialize_guildranks($str)
{
global $eoserv_items;
	$ranks = explode(',', $str);
	array_pop($ranks);
	
	if (count($ranks) != 9)
	{
		$ranks = array_fill(0, 9, 0);
	}

	return $ranks;
}

function unserialize_spells()
{
	return array();
}

function karma_str($karma)
{
	// NOTE: These values are unconfirmed guesses
	$table = array(
		0    => 'Demonic',
		250  => 'Doomed',
		500  => 'Cursed',
		750  => 'Evil',
		1000 => 'Neutral',
		1250 => 'Good',
		1500 => 'Blessed',
		1750 => 'Saint',
		2000 => 'Pure'
	);
	
	$last = $table[0];
	
	foreach ($table as $k => $v)
	{
		if ($karma < $k)
		{
			return $last;
		}
		$last = $v;
	}
	
	return $last;
}

function haircolor_str($color)
{
	$table = array(
		'Brown',
		'Green',
		'Pink',
		'Red',
		'Yellow',
		'Blue',
		'Purple',
		'Luna',
		'White',
		'Black'
	);
	
	return isset($table[$color])?$table[$color]:'Unknown';
}

function race_str($race)
{
	$table = array(
		'Human (White)',
		'Human (Yellow)',
		'Human (Tan)',
		'Orc',
		'Panda',
		'Skeleton',
		'Fish'
	);
	
	return isset($table[$race])?$table[$race]:'Unknown';
}

function adminrank_str($admin)
{
	$table = array(
		'Player',
		'Light Guide',
		'Guardian',
		'Game Master',
		'High Game Master'
	);
	
	return isset($table[$admin])?$table[$admin]:'Unknown';
}

function class_str($class)
{
global $eoserv_classes;
	if ($class == 0)
	{
		return '-';
	}
	
	return $eoserv_classes->Get($class)->name;
}

function guildrank_str($ranks, $rank)
{
	return isset($ranks[$rank-1])?$ranks[$rank-1]:'Unknown';
}
