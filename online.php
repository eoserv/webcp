<?php

$pagetitle = 'Online Characters';

require 'common.php';

$tpl->Execute('header');

$tpl->limit = $topplayers;

foreach ($onlinelist as &$character)
{
	$character['name'] = ucfirst($character['name']);
}
unset($character);

$tpl->characters = $onlinelist;

$tpl->Execute('online');

$tpl->Execute('footer');
