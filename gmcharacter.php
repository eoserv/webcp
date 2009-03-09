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

if (!$GM)
{
	$tpl->message = 'You must be a Game Master to view this page.';
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

$character = $db->SQL("SELECT * FROM characters WHERE name = '$' LIMIT 1", $_GET['name']);

if (empty($character))
{
	$tpl->message = 'Character does not exist.';
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
	case RACE_WHITE: $character['race'] = 'Human (White)'; break;
	case RACE_YELLOW: $character['race'] = 'Human (Yellow)'; break;
	case RACE_TAN: $character['race'] = 'Human (Tan)'; break;
	case RACE_PANDA: $character['race'] = 'Panda'; break;
	case RACE_SKELETON: $character['race'] = 'Skeleton'; break;
	case RACE_FISH: $character['race'] = 'Fish'; break;
	default: $character['race'] = 'Unknown'; break;
}
$character['partner'] = empty($character['partner'])?'-':ucfirst($character['partner']);
$character['exp'] = number_format($character['exp']);
switch ($character['admin'])
{
	case ADMIN_PLAYER: $character['admin_str'] = 'Player'; break;
	case ADMIN_GUIDE: $character['admin_str'] = 'Light Guide'; break;
	case ADMIN_GUARDIAN: $character['admin_str'] = 'Guardian'; break;
	case ADMIN_GM: $character['admin_str'] = 'Game Master'; break;
	case ADMIN_HGM: $character['admin_str'] = 'High Game Master'; break;
	default: $character['admin_str'] = 'Unknown'; break;
}

$pagetitle .= ': '.$character['name'];
$tpl->pagetitle = $pagetitle;

$tpl->character = $character;

$tpl->Execute('header');

$tpl->Execute('character');

$tpl->Execute('footer');
