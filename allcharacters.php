<?php

$pagetitle = 'All Characters';

require 'common.php';

if (!$logged)
{
	$tpl->message = 'You must be logged in to view this page.';
	$tpl->Execute('header');
	$tpl->Execute('footer');
	exit;
}

if (!$GM)
{
	$tpl->message = 'You must be a Game Master to view this page.';
	$tpl->Execute('header');
	$tpl->Execute('footer');
	exit;
}

$tpl->Execute('header');
$count = $db->SQL('SELECT COUNT(1) as count FROM characters');
$count = $count[0]['count'];

$page = isset($_GET['page']) ? $_GET['page'] : 1;
$pages = ceil($count / $perpage);

if ($page < 1 || $page > $pages)
{
	$page = max(min($page, $pages), 1);
}

$start = ($page-1) * $perpage;

$characters = $db->SQL("SELECT * FROM characters LIMIT #,#", $start, $perpage);

foreach ($characters as &$character)
{
	$character['name'] = ucfirst($character['name']);
	$character['gender'] = $character['gender']?'Male':'Female';
	$character['title'] = empty($character['title'])?'-':ucfirst($character['title']);
	$character['exp'] = number_format($character['exp']);
	$character['gm'] = $character['admin'] > 0;
	$character['admin_str'] = adminrank_str($character['admin']);
}
unset($character);

$pagination = generate_pagination($pages, $page);

$tpl->page = $page;
$tpl->pages = $pages;
$tpl->pagination = $pagination;
$tpl->perpage = $perpage;
$tpl->showing = count($characters);
$tpl->start = $start+1;
$tpl->end = min($start+$perpage, $count);
$tpl->count = $count;

$tpl->characters = $characters;

$tpl->Execute('allcharacters');

$tpl->Execute('footer');
