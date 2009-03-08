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
		case 0: $character['admin'] = 'Player'; break;
		case 1: $character['admin'] = 'Light Guide'; break;
		case 2: $character['admin'] = 'Guardian'; break;
		case 3: $character['admin'] = 'Game Master'; break;
		case 4: $character['admin'] = 'Heavy Game Master'; break;
	}
}
unset($character);

$tpl->characters = $characters;

$tpl->Execute('staff');

$tpl->Execute('footer');
