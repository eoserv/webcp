<?php

$pagetitle = 'Top Guilds';

require 'common.php';

$tpl->limit = $topguilds;
$guilds = webcp_db_fetchall("SELECT tag, name, (SELECT COUNT(1) FROM characters c WHERE c.guild = g.tag) AS members, (SELECT SUM(`exp`) FROM characters c WHERE c.guild = g.tag) AS `exp` FROM guilds g ORDER BY `exp` DESC LIMIT ?", $topguilds);

if (empty($guilds))
{
	$tpl->message = "No guilds have been created yet.";
	$tpl->Execute(null);
	exit;
}

foreach ($guilds as &$guild)
{
	$guild['exp'] = number_format($guild['exp']);
	$guild['members'] = number_format($guild['members']);
}
unset($guild);

$tpl->guilds = $guilds;

$tpl->Execute('guilds');
