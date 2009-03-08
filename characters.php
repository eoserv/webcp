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
		case 0: $character['admin'] = 'Player'; break;
		case 1: $character['admin'] = 'Light Guide'; $character['gm'] = true; break;
		case 2: $character['admin'] = 'Guardian'; $character['gm'] = true; break;
		case 3: $character['admin'] = 'Game Master'; $character['gm'] = true; break;
		case 4: $character['admin'] = 'Heavy Game Master'; $character['gm'] = true; break;
	}
}
unset($character);

$tpl->characters = $characters;

$tpl->Execute('characters');

$tpl->Execute('footer');
