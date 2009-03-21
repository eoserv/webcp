<?php

$pagetitle = 'All Accounts';

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

$count = $db->SQL('SELECT COUNT(1) as count FROM accounts');
$count = $count[0]['count'];

$page = isset($_GET['page']) ? $_GET['page'] : 1;
$pages = ceil($count / $perpage);

if ($page < 1 || $page > $pages)
{
	$page = max(min($page, $pages), 1);
}

$start = ($page-1) * $perpage;

$accounts = $db->SQL("SELECT * FROM accounts LIMIT #,#", $start, $perpage);

foreach ($accounts as &$account)
{
	$account['hdid_str'] = sprintf("%08x", (double)$account['hdid']);
	$account['hdid_str'] = strtoupper(substr($account['hdid_str'],0,4).'-'.substr($account['hdid_str'],4,4));
	$account['characters'] = count($db->SQL("SELECT 1 FROM `characters` WHERE account = '$'", $account['username']));
}
unset($account);

$pagination = generate_pagination($pages, $page);

$tpl->page = $page;
$tpl->pages = $pages;
$tpl->pagination = $pagination;
$tpl->perpage = $perpage;
$tpl->showing = count($accounts);
$tpl->start = $start+1;
$tpl->end = min($start+$perpage, $count);
$tpl->count = $count;

$tpl->accounts = $accounts;

$tpl->Execute('allaccounts');

$tpl->Execute('footer');
