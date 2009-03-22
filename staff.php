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
	$character['admin_str'] = adminrank_str($character['admin']);
}
unset($character);

$tpl->characters = $characters;

$tpl->Execute('staff');

$tpl->Execute('footer');
