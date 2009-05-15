<?php

$pagetitle = 'Top Players';

require 'common.php';

$tpl->limit = $topplayers;
$characters = $db->SQL("SELECT name, title, level, exp, gender FROM characters WHERE admin = 0 ORDER BY exp DESC LIMIT #", $topplayers);

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
