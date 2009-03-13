<?php

$pagetitle = 'Account';

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

$account = $db->SQL("SELECT * FROM accounts WHERE username = '$'", strtolower($_GET['name']));
if (empty($account[0]))
{
	$tpl->message = 'Account does not exist.';
	$tpl->Execute('header');
	$tpl->Execute('footer');
	exit;
}
$account = $account[0];


$account['hdid_str'] = sprintf("%08x", (double)$account['hdid']);
$account['hdid_str'] = strtoupper(substr($account['hdid_str'],0,4).'-'.substr($account['hdid_str'],4,4));
$account['created_str'] = date('r', $account['created']);
$account['lastused_str'] = date('r', $account['lastused']);

$tpl->account = $account;

$characters = $db->SQL("SELECT * FROM characters WHERE account = '$' ORDER BY exp DESC", strtolower($_GET['name']));

foreach ($characters as &$character)
{
	$character['name'] = ucfirst($character['name']);
	$character['gender'] = $character['gender']?'Male':'Female';
	$character['title'] = empty($character['title'])?'-':ucfirst($character['title']);
	$character['exp'] = number_format($character['exp']);
	switch ($character['admin'])
	{
		case ADMIN_PLAYER: $character['admin_str'] = 'Player'; break;
		case ADMIN_GUIDE: $character['admin_str'] = 'Light Guide'; $character['gm'] = true; break;
		case ADMIN_GUARDIAN: $character['admin_str'] = 'Guardian'; $character['gm'] = true; break;
		case ADMIN_GM: $character['admin_str'] = 'Game Master'; $character['gm'] = true; break;
		case ADMIN_HGM: $character['admin_str'] = 'High Game Master'; $character['gm'] = true; break;
		default: $character['admin_str'] = 'Unknown'; break;
	}
}
unset($character);

$tpl->characters = $characters;

$pagetitle .= ': '.htmlentities($_GET['name']);
$tpl->pagetitle = $pagetitle;

$tpl->Execute('header');

$tpl->Execute('gmaccount');

$tpl->Execute('footer');
