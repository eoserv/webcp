<?php

$pagetitle = 'My Characters';

require 'common.php';

if (!$logged)
{
	$tpl->message = 'You must be logged in to view this page.';
	$tpl->Execute('header');
	$tpl->Execute('footer');
	exit;
}

$tpl->Execute('header');

$characters = $db->SQL("SELECT * FROM characters WHERE account = '$' ORDER BY exp DESC", $sess->username);

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

$tpl->Execute('characters');

$tpl->Execute('footer');
