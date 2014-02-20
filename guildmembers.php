<?php

$pagetitle = 'Guild Members';

require 'common.php';

if (empty($_GET['tag']))
{
	$tpl->message = 'No guild tag specified.';
	$tpl->Execute(null);
	exit;
}

$guild = $db->SQL("SELECT * FROM guilds WHERE tag = '$'", strtoupper($_GET['tag']));
if (empty($guild[0]))
{
	$tpl->message = 'Guild does not exist.';
	$tpl->Execute(null);
	exit;
}
$guild = $guild[0];

$guild['name'] = ucfirst($guild['name']);
$guild['ranks'] = explode(',', $guild['ranks']);

$tpl->guild = $guild;

$count = $db->SQL("SELECT COUNT(1) as count FROM characters WHERE guild = '$'", $guild['tag']);
$count = $count[0]['count'];

$page = isset($_GET['page']) ? $_GET['page'] : 1;
$pages = ceil($count / $perpage);

if ($page < 1 || $page > $pages)
{
	$page = max(min($page, $pages), 1);
}

$start = ($page-1) * $perpage;

$members = $db->SQL("SELECT * FROM characters WHERE guild = '$' ORDER BY guild_rank ASC, name ASC", $guild['tag']);

foreach ($members as &$member)
{
	$member['name'] = ucfirst($member['name']);
	$member['gender'] = $member['gender']?'Male':'Female';
	$member['title'] = empty($member['title'])?'-':ucfirst($member['title']);
	$member['exp'] = number_format($member['exp']);
	$member['gm'] = $member['admin'] > 0;
	$member['rank'] = guildrank_str($guild['ranks'], $member['guild_rank']);
}
unset($member);

$pagination = generate_pagination($pages, $page);

$tpl->page = $page;
$tpl->pages = $pages;
$tpl->pagination = $pagination;
$tpl->perpage = $perpage;
$tpl->showing = count($members);
$tpl->start = $start+1;
$tpl->end = min($start+$perpage, $count);
$tpl->count = $count;

$tpl->members = $members;

$pagetitle .= ': '.strtoupper(htmlentities($_GET['tag']));
$tpl->pagetitle = $pagetitle;

$tpl->Execute('guildmembers');
