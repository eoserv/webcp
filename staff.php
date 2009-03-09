<?php

$pagetitle = 'Staff Characters';

require 'common.php';

$tpl->Execute('header');

$characters = $db->SQL("SELECT name, gender, title, admin FROM characters WHERE admin > 0 ORDER BY admin DESC");

foreach ($characters as &$character)
{
	$character['name'] = ucfirst($character['name']);
	$character['gender'] = $character['gender']?'Male':'Female';
	$character['title'] = empty($character['title'])?'-':ucfirst($character['title']);
	switch ($character['admin'])
	{
		case ADMIN_PLAYER: $character['admin_str'] = 'Player'; break;
		case ADMIN_GUIDE: $character['admin_str'] = 'Light Guide'; break;
		case ADMIN_GUARDIAN: $character['admin_str'] = 'Guardian'; break;
		case ADMIN_GM: $character['admin_str'] = 'Game Master'; break;
		case ADMIN_HGM: $character['admin_str'] = 'High Game Master'; break;
		default: $character['admin_str'] = 'Unknown'; break;
	}
}
unset($character);

$tpl->characters = $characters;

$tpl->Execute('staff');

$tpl->Execute('footer');
