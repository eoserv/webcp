<?php

$pagetitle = 'All Guilds';

require 'common.php';

if (!$logged)
{
	$tpl->message = 'You must be logged in to view this page.';
	$tpl->Execute(null);
	exit;
}

if (!$GM)
{
	$tpl->message = 'You must be a Game Master to view this page.';
	$tpl->Execute(null);
	exit;
}

$count = $db->SQL('SELECT COUNT(1) as count FROM guilds');
$count = $count[0]['count'];

if ($count == 0)
{
	$tpl->message = "No guilds have been created yet.";
	$tpl->Execute(null);
    exit;
}			

$page = isset($_GET['page']) ? $_GET['page'] : 1;
$pages = ceil($count / $perpage);

if ($page < 1 || $page > $pages)
{
	$page = max(min($page, $pages), 1);
}

$start = ($page-1) * $perpage;

$guilds = $db->SQL("SELECT * FROM guilds LIMIT #,#", $start, $perpage);

if (empty($guilds))
{
	$tpl->messages = "No guilds have been created yet.";
	$tpl->Execute(null);
	exit;
}

$guildlistq = '';

foreach ($guilds as &$guild)
{
	$guild['tag'] = trim(strtoupper($guild['tag']));
	$guild['name'] = ucfirst($guild['name']);
	$guildlistq .= "guild = '".$db->Escape($guild['tag'])."' OR ";
}
unset($guild);

$guildlistq = substr($guildlistq, 0, -4);

if (!$guildlistq)
{
	trigger_error("No guilds were selected");
}

$members = $db->SQL("SELECT guild FROM characters WHERE $guildlistq");
$totalexp = $db->SQL("SELECT guild, exp FROM characters WHERE ($guildlistq) AND admin = 0");

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

foreach ($guilds as &$guild)
{
	$guild['exp'] = number_format($guild['exp']);
	$guild['members'] = number_format($guild['members']);
}
unset($guild);

$tpl->guilds = $guilds;

$pagination = generate_pagination($pages, $page);

$tpl->page = $page;
$tpl->pages = $pages;
$tpl->pagination = $pagination;
$tpl->perpage = $perpage;
$tpl->showing = count($guilds);
$tpl->start = $start+1;
$tpl->end = min($start+$perpage, $count);
$tpl->count = $count;

$tpl->guilds = $guilds;

$tpl->Execute('allguilds');
