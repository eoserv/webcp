<?php

$pagetitle = 'Home';

require 'common.php';

$accounts = $db->SQL('SELECT COUNT(1) as count FROM accounts');
$accounts = $accounts[0]['count'];

$characters = $db->SQL('SELECT COUNT(1) as count FROM characters');
$characters = $characters[0]['count'];

$staffcharacters = $db->SQL('SELECT COUNT(1) as count FROM characters WHERE admin > 0');
$staffcharacters = $staffcharacters[0]['count'];

$onlinecharacters = 0;

$guilds = $db->SQL('SELECT COUNT(1) as count FROM guilds');
$guilds = $guilds[0]['count'];

$bank = $db->SQL('SELECT SUM(goldbank) as gold FROM characters');
$bank = $bank[0]['gold'];

$tpl->accounts = number_format($accounts);
$tpl->characters = number_format($characters);
$tpl->staffcharacters = number_format($staffcharacters);
$tpl->guilds = number_format($guilds);
$tpl->bank = number_format($bank);

$tpl->Execute('index');
