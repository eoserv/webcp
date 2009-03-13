<?php

$pagetitle = 'Account Search';

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

if (isset($_POST['username'],$_POST['computer'],$_POST['hdid']))
{
	$hdid = explode('-', $_POST['hdid']);
	if (isset($hdid[1]))
	{
		$hdid = hexdec($hdid[0]) * 0x10000 + hexdec($hdid[1]);
		$hdidq = " AND hdid = '".$hdid."'";
	}
	else
	{
		$hdidq = "";
	}
	
	$username = strtolower($_POST['username']);
	$computer = strtoupper($_POST['computer']);
	
	$accounts = $db->SQL("SELECT * FROM accounts WHERE username LIKE '$' AND computer LIKE '$'$hdidq LIMIT 0,100", $username, $computer);

	foreach ($accounts as &$account)
	{
		$account['hdid_str'] = sprintf("%08x", (double)$account['hdid']);
		$account['hdid_str'] = strtoupper(substr($account['hdid_str'],0,4).'-'.substr($account['hdid_str'],4,4));
		$account['characters'] = count($db->SQL("SELECT 1 FROM `characters` WHERE account = '$'", $account['username']));
	}
	unset($account);

	$tpl->accounts = $accounts;

	$tpl->Execute('accsearch_results');
}
else
{
	$tpl->Execute('accsearch');
}

$tpl->Execute('footer');
