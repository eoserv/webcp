<?php

$pagetitle = 'Online Characters';

require 'common.php';

$tpl->limit = $topplayers;

if ($online)
{
	if (empty($onlinelist))
	{
		$tpl->message = "No characters are currently online.";
		$tpl->Execute(null);
		exit;
	}

	foreach ($onlinelist as &$character)
	{
		$character['name'] = ucfirst($character['name']);
		$character['title'] = empty($character['title'])?'-':ucfirst($character['title']);
		$character['gm'] = $character['admin'] == 4 || $character['admin'] == 5 || $character['admin'] == 9 || $character['admin'] == 10;
	}
	unset($character);

	$tpl->characters = $onlinelist;

	$tpl->Execute('online');
}
else
{
	$tpl->message = "Server is offline";
	$tpl->Execute(null);
	exit;
}
