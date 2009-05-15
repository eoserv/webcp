<?php

$pagetitle = 'Character';

$NEEDPUB = true;
require 'common.php';

if (!$logged)
{
	$tpl->message = 'You must be logged in to view this page.';
	$tpl->Execute(null);
	exit;
}

if (empty($_GET['name']))
{
	$tpl->message = 'No character name specified.';
	$tpl->Execute(null);
	exit;
}

$character = $db->SQL("SELECT * FROM characters WHERE name = '$' AND account = '$' LIMIT 1", strtolower($_GET['name']), $sess->username);

if (empty($character))
{
	$tpl->message = 'Character does not exist or is not yours.';
	$tpl->Execute(null);
	exit;
}

$character = $character[0];

$character['name'] = ucfirst($character['name']);
$character['gender'] = $character['gender']?'Male':'Female';
$character['title'] = empty($character['title'])?'-':ucfirst($character['title']);
$character['home'] = empty($character['home'])?'-':ucfirst($character['home']);
$character['usage_str'] = floor($character['usage']/60).' hour(s)';
$character['karma_str'] = karma_str($character['karma']);
$character['inventory'] = unserialize_inventory($character['inventory']);
$character['bank'] = unserialize_inventory($character['bank']);
$character['paperdoll'] = unserialize_paperdoll($character['paperdoll']);
$character['spells'] = unserialize_spells($character['spells']);
if (!empty($character['guild']))
{
	$guildinfo = $db->SQL("SELECT * FROM guilds WHERE tag = '$'", $character['guild']);
	if (!empty($guildinfo[0]))
	{
		$character['guild_name'] = $guildinfo[0]['name'];
		$character['guild_rank_str'] = guildrank_str(unserialize_guildranks($guildinfo[0]['ranks']), $character['guild_rank']);
	}
}
$character['class_str'] = class_str($character['class']);
$character['haircolor_str'] = haircolor_str($character['haircolor']);
$character['race_str'] = race_str($character['race']);
$character['partner'] = empty($character['partner'])?'-':ucfirst($character['partner']);
$character['exp'] = number_format($character['exp']);
$character['admin_str'] = adminrank_str($character['admin']);

$pagetitle .= ': '.$character['name'];
$tpl->pagetitle = $pagetitle;

$tpl->character = $character;

$tpl->Execute('character');
