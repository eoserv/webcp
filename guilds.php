<?php

function sort_exp($a, $b)
{
	return $a['exp'] < $b['exp'];
}

$pagetitle = 'Top Guilds';

require 'common.php';

$tpl->limit = $topguilds;
$guilds = webcp_db_fetchall("SELECT tag, name FROM guilds");

if (empty($guilds))
{
	$tpl->message = "No guilds have been created yet.";
	$tpl->Execute(null);
	exit;
}

$guildlistq = '';
$guildlistqa = array();

foreach ($guilds as &$guild)
{
	$guild['tag'] = trim(strtoupper($guild['tag']));
	$guild['name'] = ucfirst($guild['name']);
	$guildlistq .= "guild = ? OR ";
	$guildlistqa[] = $guild['tag'];
}
unset($guild);

$guildlistq = substr($guildlistq, 0, -4);

if (!$guildlistq)
{
	trigger_error("No guilds were selected");
}

$members = webcp_db_fetchall_array("SELECT guild FROM characters WHERE $guildlistq", $guildlistqa);
$totalexp = webcp_db_fetchall_array("SELECT guild,exp FROM characters WHERE ($guildlistq) AND admin = 0", $guildlistqa);

foreach ($guilds as &$guild)
{
	$membercount = 0;
	$expcount = 0;
	foreach ($members as $member)
	{
		if ($member['guild'] == $guild['tag'])
		{
			++$membercount;
		}
	}

	foreach ($totalexp as $member)
	{
		if ($member['guild'] == $guild['tag'])
		{
			$expcount += $member['exp'];
		}
	}

	$guild['exp'] = $expcount;
	$guild['members'] = $membercount;
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
