<?php

$pagetitle = 'Character';

require 'common.php';

if (!$logged)
{
	$tpl->message = 'You must be logged in to view this page.';
	$tpl->Execute('header');
	$tpl->Execute('footer');
	exit;
}

if (empty($_GET['name']))
{
	$tpl->message = 'No character name specified.';
	$tpl->Execute('header');
	$tpl->Execute('footer');
	exit;
}

$character = $db->SQL("SELECT * FROM characters WHERE name = '$' AND account = '$' LIMIT 1", $_GET['name'], $sess->username);

if (empty($character))
{
	$tpl->message = 'Character does not exist or is not yours.';
	$tpl->Execute('header');
	$tpl->Execute('footer');
	exit;
}

$character = $character[0];

$character['name'] = ucfirst($character['name']);
$character['gender'] = $character['gender']?'Male':'Female';
$character['title'] = empty($character['title'])?'-':ucfirst($character['title']);
$character['home'] = empty($character['home'])?'-':ucfirst($character['home']);
$character['usage_str'] = floor($character['usage']/60).' hour(s)';
switch ($character['class'])
{
	case 0: $character['class'] = 'Peasant'; break;
	default: $character['class'] = 'Unknown'; break;
}
switch ($character['race'])
{
	case 0: $character['race'] = 'Human (White)'; break;
	case 1: $character['race'] = 'Human (Yellow)'; break;
	case 2: $character['race'] = 'Human (Tan)'; break;
	case 3: $character['race'] = 'Panda'; break;
	case 4: $character['race'] = 'Skeleton'; break;
	case 5: $character['race'] = 'Fish'; break;
	default: $character['race'] = 'Unknown'; break;
}
$character['partner'] = empty($character['partner'])?'-':ucfirst($character['partner']);
$character['exp'] = number_format($character['exp']);
switch ($character['admin'])
{
	case 0: $character['admin_str'] = 'Player'; break;
	case 1: $character['admin_str'] = 'Light Guide'; break;
	case 2: $character['admin_str'] = 'Guardian'; break;
	case 3: $character['admin_str'] = 'Game Master'; break;
	case 4: $character['admin_str'] = 'Heavy Game Master'; break;
	default: $character['admin_str'] = 'Unknown'; break;
}

$pagetitle .= ': '.$character['name'];
$tpl->pagetitle = $pagetitle;

$tpl->character = $character;

$tpl->Execute('header');

$tpl->Execute('character');

$tpl->Execute('footer');
