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

$accounts = $db->SQL("SELECT * FROM accounts LIMIT 0,100");

foreach ($accounts as &$account)
{
	$account['hdid_str'] = sprintf("%08x", (double)$account['hdid']);
	$account['hdid_str'] = strtoupper(substr($account['hdid_str'],0,4).'-'.substr($account['hdid_str'],4,4));
	$account['characters'] = count($db->SQL("SELECT 1 FROM `characters` WHERE account = '$'", $account['username']));
}
unset($account);

$tpl->accounts = $accounts;

$tpl->Execute('allaccounts');

$tpl->Execute('footer');
