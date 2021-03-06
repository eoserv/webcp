<?php

$pagetitle = 'Account';

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

if (empty($_GET['name']))
{
	$tpl->message = 'No character name specified.';
	$tpl->Execute(null);
	exit;
}

$account = webcp_db_fetchall("SELECT * FROM accounts WHERE username = ?", strtolower($_GET['name']));
if (empty($account[0]))
{
	$tpl->message = 'Account does not exist.';
	$tpl->Execute(null);
	exit;
}
$account = $account[0];

$account['regip'] = webcp_encrypt_ip($account['regip']);
$account['lastip'] = webcp_encrypt_ip($account['lastip']);
$account['hdid_str'] = sprintf("%08x", (double)$account['hdid']);
$account['hdid_str'] = strtoupper(substr($account['hdid_str'],0,4).'-'.substr($account['hdid_str'],4,4));
$account['hdid_str'] = webcp_encrypt_hdid($account['hdid_str']);
$account['computer'] = webcp_encrypt_computer($account['computer']);
$account['computer_str'] = webcp_trunc($account['computer'], 15);
$account['created_str'] = date('r', $account['created']);
$account['lastused_str'] = date('r', $account['lastused']);

$tpl->account = $account;

$characters = webcp_db_fetchall("SELECT * FROM characters WHERE account = ? ORDER BY exp DESC", strtolower($_GET['name']));

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

$tpl->characters = $characters;

$pagetitle .= ': '.htmlentities($_GET['name']);
$tpl->pagetitle = $pagetitle;

$tpl->Execute('account');
