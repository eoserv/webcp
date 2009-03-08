<?php

$pagetitle = 'Top Players';

require 'common.php';

$tpl->Execute('header');

$tpl->limitcharacters = 100;
$characters = $db->SQL("SELECT name, title, level, exp, gender FROM characters WHERE admin = 0 ORDER BY exp DESC LIMIT {$tpl->limitcharacters}");

foreach ($characters as &$character)
{
	$character['name'] = ucfirst($character['name']);
	$character['gender'] = $character['gender']?'Male':'Female';
	$character['title'] = empty($character['title'])?'-':ucfirst($character['title']);
	$character['exp'] = number_format($character['exp']);
}
unset($character);

$tpl->characters = $characters;

$tpl->Execute('players');

$tpl->Execute('footer');
