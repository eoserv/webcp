<?php

function sort_exp($a, $b)
{
	return $a['exp'] < $b['exp'];
}

$pagetitle = 'Top Guilds';

require 'common.php';

$tpl->Execute('header');

$tpl->limit = $topguilds;
$guilds = $db->SQL("SELECT tag, name FROM guilds");

$i = 0;
foreach ($guilds as &$guild)
{
	$members = $db->SQL("SELECT 1 FROM characters WHERE guild = '$'", $guild['tag']);
	$totalexp = $db->SQL("SELECT SUM(exp) as totalexp FROM characters WHERE guild = '$'", $guild['tag']);
	$guild['exp'] = $totalexp[0]['totalexp'];
	$guild['tag'] = trim(strtoupper($guild['tag']));
	$guild['name'] = ucfirst($guild['name']);
	$guild['members'] = count($members);
}
unset($guild);

usort($guilds, 'sort_exp');

$guilds = array_slice($guilds, 0, $topguilds);

foreach ($guilds as &$guild)
{
	$guild['exp'] = number_format($guild['exp']);
	$guild['members'] = number_format($guild['members']);
}
unset($guild);

$tpl->guilds = $guilds;

$tpl->Execute('guilds');

$tpl->Execute('footer');
