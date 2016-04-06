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
	echo "Total execution time: $exectime ms";
}

function webcp_trunc($word, $len)
{
	if (strlen($word) < $len + 3)
		return $word;

	return substr($word, 0, $len) . '...';
}

require 'ipcrypt.php';

if (!function_exists('hash'))
{
	exit("Could not find the the hash PHP extension.");
}

if (array_search('sha256',hash_algos()) === false)
{
	exit("Could not find the the sha256 hash algorithm.");
}

if (!class_exists('PDO'))
{
	exit("Could not find the the PDO PHP extension.");
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
define('RACE_SKELETON', 4);
define('RACE_PANDA', 5);
define('RACE_FISH', 6);

require 'config.php';

if (!empty($DEBUG))
{
	$starttime = microtime(true);
	register_shutdown_function('webcp_debug_info');
}

require 'class/Template.class.php';
require 'class/Session.class.php';

try
{
	switch ($dbtype)
	{
		case 'sqlite':
			$dsn = "sqlite:" . $dbhost;
			$db = new PDO($dsn);
			break;
	
		case 'mysql':
			$dsn = "mysql:host=" . $dbhost;

			if (isset($dbport))
				$dsn .= ";port=" . $dbport;

			if (isset($dbname))
				$dsn .= ";dbname=" . $dbname;

			$db = new PDO($dsn, $dbuser, $dbpass);
			break;

		default:
			throw new Exception("Unknown DB type");
	}

	$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}
catch (Exception $e)
{
	exit("Database connection failed. (".$e->getMessage().")");
}

function webcp_db_execute_typed($statement, $params = null)
{
	for ($i = 0; $i < count($params); ++$i)
	{
		$p = $params[$i];

		if (is_null($p))
			$statement->bindValue($i + 1, $p, PDO::PARAM_NULL);
		else if (is_int($p))
			$statement->bindValue($i + 1, $p, PDO::PARAM_INT);
		else
			$statement->bindValue($i + 1, $p, PDO::PARAM_STR);
	}

	return $statement->execute();
}

function webcp_db_execute_array($sql, $params = null)
{
	global $db;
	
	if (is_null($params))
		$params = array();

	$statement = $db->prepare($sql);
	
	if (webcp_db_execute_typed($statement, $params))
		return $statement->rowCount();
	else
		return false;
}

function webcp_db_fetchall_array($sql, $params = null)
{
	global $db;

	if (is_null($params))
		$params = array();

	$statement = $db->prepare($sql);
	
	if (webcp_db_execute_typed($statement, $params))
		return $statement->fetchAll(PDO::FETCH_ASSOC);
	else
		return array();
}

function webcp_db_execute($sql/*, $params...*/)
{
	$params = func_get_args();
	array_shift($params);

	return webcp_db_execute_array($sql, $params);
}

function webcp_db_fetchall($sql/*, $params...*/)
{
	$params = func_get_args();
	array_shift($params);

	return webcp_db_fetchall_array($sql, $params);
}


$tpl = new Template('tpl/'.$template, true);
$sess = new Session($cpid.'_EOSERVCP');

require 'class/LoginRate.class.php';

switch ($loginrate_driver)
{
	case 'none':
		require 'class/LoginRate_none.class.php';
		$loginrate_driver = new LoginRate_none();
		break;

	case 'file':
		require 'class/LoginRate_file.class.php';
		$loginrate_driver = new LoginRate_file($loginrate_file_path, $loginrate_file_salt);
		break;

	case 'db':
		require 'class/LoginRate_db.class.php';
		$loginrate_driver = new LoginRate_db($loginrate_db_table);
		break;

	default:
		exit("Invalid LoginRate driver specified");
}

$loginrate = new LoginRate($loginrate_driver, $loginrate, $loginrate_captcha);

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

if (!is_file($pubfiles.'/dsl001.esf'))
{
	exit("File not found: $pubfiles/dsl001.esf");
}

if (!is_file($pubfiles.'/dtn001.enf'))
{
	exit("File not found: $pubfiles/dtn001.enf");
}

if (!empty($NEEDPUB))
{
	if (!empty($NEEDPUB['EIF']))
	{
		require 'class/EIFReader.class.php';
		$eoserv_items_cache = null;

		if ($pubcache && file_exists('eif.cache') && filemtime('eif.cache') >= filemtime($pubfiles.'/dat001.eif'))
			$eoserv_items_cache = EIFReader::LoadCache('eif.cache');

		$eoserv_items = new EIFReader("$pubfiles/dat001.eif", $eoserv_items_cache);
	
		if ($eoserv_items->NeedCacheUpdate())
			file_put_contents('eif.cache', $eoserv_items->GetCache());
	}

	if (!empty($NEEDPUB['ECF']))
	{
		require 'class/ECFReader.class.php';

		$eoserv_classes_cache = null;

		if ($pubcache && file_exists('ecf.cache') && filemtime('ecf.cache') >= filemtime($pubfiles.'/dat001.ecf'))
			$eoserv_classes_cache = ECFReader::LoadCache('ecf.cache');
		
		$eoserv_classes = new ECFReader("$pubfiles/dat001.ecf", $eoserv_classes_cache);

		if ($eoserv_classes->NeedCacheUpdate())
			file_put_contents('ecf.cache', $eoserv_classes->GetCache());
	}

	if (!empty($NEEDPUB['ENF']))
	{
		require 'class/ENFReader.class.php';

		$eoserv_npcs_cache = null;

		if ($pubcache && file_exists('enf.cache') && filemtime('enf.cache') >= filemtime($pubfiles.'/dtn001.enf'))
			$eoserv_npcs_cache = ENFReader::LoadCache('enf.cache');
		
		$eoserv_npcs = new ENFReader("$pubfiles/dtn001.enf", $eoserv_npcs_cache);

		if ($eoserv_npcs->NeedCacheUpdate())
			file_put_contents('enf.cache', $eoserv_npcs->GetCache());
	}

	if (!empty($NEEDPUB['ESF']))
	{
		require 'class/ESFReader.class.php';

		$eoserv_spells_cache = null;

		if ($pubcache && file_exists('esf.cache') && filemtime('esf.cache') >= filemtime($pubfiles.'/dsl001.esf'))
			$eoserv_spells_cache = ESFReader::LoadCache('esf.cache');
		
		$eoserv_spells = new ESFReader("$pubfiles/dsl001.esf", $eoserv_spells_cache);

		if ($eoserv_spells->NeedCacheUpdate())
			file_put_contents('esf.cache', $eoserv_spells->GetCache());
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

function seose_to_base62($input)
{
	static $dict = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";
	
	$result = "";
	
	while ($input > 0)
	{
		$result = $dict[$input % 62] . $result;
		$input = intval($input / 62);
	}
	
	return $result;
}

function seose_hash($input, $method)
{
	$result = 0;
	
	for ($i = 0; $i < strlen($input); ++$i)
	{
		for ($j = 7; $j >= 0; --$j)
		{
			$pow = 1 << $j;
			$test_bit = (($result & 0x8000) == 0x8000) ^ ((ord($input[$i]) & $pow) == $pow);
			$result = (($result & 0x7FFF) * 2) & 0xFFFF;
			
			if ($test_bit)
				$result = $result ^ $method;
		}
	}
	
	return $result;
}

function seose_str_hash($input, $key)
{
	$result = "";

	for ($i = 0; $i < strlen($key); ++$i)
	{
		$kc = ord($key[$i]);
		
		if ($kc == ord('#'))
			$kc = 0xA3;
		
		$result .= seose_to_base62(seose_hash($input, (($i + 1) * $kc) & 0xFFFF));
	}
	
	return $result;
}

if (isset($_REQUEST['action']))
{
	switch ($_REQUEST['action'])
	{
		case 'logout':
			unset($sess->username);

		case 'login':
			if (isset($_POST['username'], $_POST['password']))
			{
				$ip_prefix = loginrate_ip_prefix($_SERVER['REMOTE_ADDR']);
				$captcha_solved = false;
				$captcha_attempted = false;

				if (isset($_POST['captcha'], $sess->captcha))
				{
					$captcha_attempted = true;

					if (strtolower($_POST['captcha']) === strtolower($sess->captcha))
					{
						$captcha_solved = true;
					}
					else
					{
						$tpl->login_need_captcha = true;
						$tpl->message = "The CAPTCHA entered did not match. Please try again.";
						$loginrate_result = $loginrate->Mark($ip_prefix);
					}
				}

				$loginrate_result = $loginrate->Check($ip_prefix, $captcha_solved);

				if ($loginrate_result[0] == LOGINRATE_CHECK_OK)
				{
					$password = substr($_POST['password'], 0, 12);

					if ($seose_compat)
						$password = seose_str_hash($password, $seose_compat_key);

					$password = hash('sha256',$salt.strtolower($_POST['username']).$password);
					$checklogin = webcp_db_fetchall("SELECT username, password FROM accounts WHERE username = ? AND password = ?", strtolower($_POST['username']), $password);
					if (empty($checklogin))
					{
						$loginrate_result = $loginrate->Mark($ip_prefix);
						$tpl->message = "Login failed.";
						break;
					}
					else
					{
						$sess->username = $checklogin[0]['username'];
						$sess->password = $checklogin[0]['password'];
						$tpl->message = "Logged in.";
					}
				}
				else if ($loginrate_result[0] == LOGINRATE_CHECK_THROTTLED)
				{
					$delta = $loginrate_result[1];

					if (!$captcha_attempted || $captcha_solved)
					{
						$message = 'Too many failed login attempts. Please try again';
					}
					else
					{
						$message = 'The CAPTCHA entered did not match.<br>Too many failed login attempts. Please try again';
						$tpl->login_need_captcha = false;
					}

					if ($delta <= 60)
					{
						$message .= ' in one minute.';
					}
					else if ($delta <= 300)
					{
						$message .= ' in 5 minutes.';
					}
					else if ($delta <= 3600)
					{
						$message .= ' in one hour.';
					}
					else
					{
						$message .= ' tomorrow.';
					}

					$tpl->message = $message;
				}
				else if ($loginrate_result[0] == LOGINRATE_CHECK_NEED_CAPTCHA)
				{
					$tpl->login_need_captcha = true;

					if (!$captcha_attempted)
						$tpl->message = 'Too many failed login attempts. Please solve a CAPTCHA.';
				}
			}
			break;
	}
}

$tpl->logged = $logged = isset($sess->username);
$tpl->username = $sess->username;

if (isset($sess->username, $sess->password))
	$userdata = webcp_db_fetchall("SELECT * FROM accounts WHERE username = ? AND password = ? LIMIT 1", $sess->username, $sess->password);
else
	$userdata = array();

if ($logged && empty($userdata))
{
	if (isset($sess->password))
		$tpl->message = "Your account has been deleted or changed password, logging out...";
	else
		$tpl->message = "Session expired, logging out...";

	$tpl->logged = $logged = false;
}

$tpl->GUIDE = $GUIDE = false;
$tpl->GUARDIAN = $GUARDIAN = false;
$tpl->GM = $GM = false;
$tpl->HGM = $HGM = false;

$chardata_guilds = array();
if (isset($userdata[0]))
{
	$userdata = $userdata[0];
	$chardata = webcp_db_fetchall("SELECT * FROM characters WHERE account = ?", $sess->username);
	foreach ($chardata as $cd)
	{
		if ($cd['admin'] >= ADMIN_GUIDE)
		{
			$tpl->GUIDE = $GUIDE = true;
		}

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
			if ($cd['guild_rank'] <= 1)
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
	$buffer = preg_replace('/<form(.*?)method="POST">/i', '<form\1method="POST"><input type="hidden" name="csrf" value="'.$csrf.'">', $buffer);
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
	
	while (count($items) < 15)
	{
		$items[] = 0;
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
	$ranks = explode(',', $str);
	array_pop($ranks);
	
	while (count($ranks) < 9)
	{
		$ranks[] = "?";
	}

	return $ranks;
}

function unserialize_spells($str)
{
global $eoserv_spells;
	$spells = explode(';', $str);
	array_pop($spells);

	foreach ($spells as &$spell)
	{
		$xspell = explode(',', $spell);
		$spell = array(
			'id' => (int)$xspell[0],
			'name' => $eoserv_spells->Get($xspell[0])->name,
			'level' => $xspell[1]
		);
	}
	unset($spell);
	
	return $spells;
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
	if ($rank == 0) $rank = 1;
	return isset($ranks[$rank-1])?$ranks[$rank-1]:'Unknown';
}
