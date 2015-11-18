<?php

$pagetitle = 'Home';

require 'common.php';

$accounts = webcp_db_fetchall('SELECT COUNT(1) as count FROM accounts');
$accounts = $accounts[0]['count'];

$characters = webcp_db_fetchall('SELECT COUNT(1) as count FROM characters');
$characters = $characters[0]['count'];

$staffcharacters = webcp_db_fetchall('SELECT COUNT(1) as count FROM characters WHERE admin > 0');
$staffcharacters = $staffcharacters[0]['count'];

$guilds = webcp_db_fetchall('SELECT COUNT(1) as count FROM guilds');
$guilds = $guilds[0]['count'];

if ((isset($showbankgold) && $showbankgold)
 || (!isset($showbankgold) && $characters < 10000))
{
	$bank = webcp_db_fetchall('SELECT SUM(goldbank) as gold FROM characters');
	$bank = $bank[0]['gold'];
}

$tpl->accounts = number_format($accounts);
$tpl->characters = number_format($characters);
$tpl->staffcharacters = number_format($staffcharacters);
$tpl->guilds = number_format($guilds);

if (isset($bank))
	$tpl->bank = number_format($bank);

$tpl->Execute('index');
