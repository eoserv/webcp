<?php

$pagetitle = 'All Accounts';

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

$count = $db->SQL('SELECT COUNT(1) as count FROM accounts');
$count = $count[0]['count'];

if ($count == 0)
{
	$tpl->message = 'No accounts have been created yet.';
	$tpl->Execute(null);
	return;
}

$page = isset($_GET['page']) ? $_GET['page'] : 1;
$pages = ceil($count / $perpage);

if ($page < 1 || $page > $pages)
{
	$page = max(min($page, $pages), 1);
}

$start = ($page-1) * $perpage;

$accounts = $db->SQL("SELECT * FROM accounts LIMIT #,#", $start, $perpage);

$acclistq = '';

foreach ($accounts as &$account)
{
	$account['hdid_str'] = sprintf("%08x", (double)$account['hdid']);
	$account['hdid_str'] = strtoupper(substr($account['hdid_str'],0,4).'-'.substr($account['hdid_str'],4,4));
	$acclistq .= "account = '".$db->Escape($account['username'])."' OR ";
}
unset($account);

$acclistq = substr($acclistq, 0, -4);

if (!$acclistq)
{
	trigger_error("No accounts were selected");
}

$charcounts = $db->SQL("SELECT account FROM characters WHERE $acclistq");

foreach ($accounts as $i => &$account)
{
	$charcount = 0;
	foreach ($charcounts as $character)
	{
		if ($character['account'] == $account['username'])
		{
			++$charcount;
		}
	}
	$account['characters'] = $charcount;
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
