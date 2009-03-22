<?php

$pagetitle = 'Online Characters';

require 'common.php';

$tpl->Execute('header');

$tpl->limit = $topplayers;

foreach ($onlinelist as &$character)
{
	$character['name'] = ucfirst($character['name']);
	$character['title'] = empty($character['title'])?'-':ucfirst($character['title']);
	$character['gm'] = $character['admin'] == 4 || $character['admin'] == 5 || $character['admin'] == 9 || $character['admin'] == 10;
}
unset($character);

$tpl->characters = $onlinelist;

$tpl->Execute('online');

$tpl->Execute('footer');
