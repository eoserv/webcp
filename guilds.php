<?php

function sort_exp($a, $b)
{
	return $a['exp'] < $b['exp'];
}

$pagetitle = 'Top Guilds';

require 'common.php';

$tpl->Execute('header');

$tpl->limitguilds = 100;
$guilds = $db->SQL("SELECT tag, name FROM guilds");

$i = 0;
foreach ($guilds as &$guild)
{
	$members = $db->SQL("SELECT 1 FROM characters WHERE guild = '$'", $guild['tag']);
	$totalexp = $db->SQL("SELECT SUM(exp) as totalexp FROM characters WHERE guild = '$'", $guild['tag']);
	if (empty($members)) continue;
	$guild['members'] = count($members);
	$guild['exp'] = $totalexp[0]['totalexp'];
	$guild['tag'] = trim(strtoupper($guild['tag']));
	$guild['name'] = ucfirst($guild['name']);
}
unset($guild);

usort($guilds, 'sort_exp');

$tpl->guilds = $guilds;

$tpl->Execute('guilds');

$tpl->Execute('footer');
