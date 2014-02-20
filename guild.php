<?php

$pagetitle = 'Guild';

require 'common.php';

if (empty($_GET['tag']))
{
	$tpl->message = 'No guild tag specified.';
	$tpl->Execute(null);
	exit;
}

$guild = $db->SQL("SELECT * FROM guilds WHERE tag = '$'", strtoupper($_GET['tag']));
if (empty($guild[0]))
{
	$tpl->message = 'Guild does not exist.';
	$tpl->Execute(null);
	exit;
}
$guild = $guild[0];

$members = $db->SQL("SELECT COUNT(1) as count FROM characters WHERE guild = '$'", strtoupper($_GET['tag']));

$guild['created_str'] = date('r', $guild['created']);
$guild['name'] = ucfirst($guild['name']);
$guild['bank'] = number_format($guild['bank']);
$guild['members'] = number_format($members[0]['count']);
$guild['ranks'] = array_slice(explode(',', $guild['ranks']), 0, 9);

foreach ($guild['ranks'] as $k => $rank)
{
	$guild['ranks'][$k] = array($k+1, $rank);
}

$totalexp = $db->SQL("SELECT SUM(exp) as totalexp FROM characters WHERE guild = '$' AND admin = 0", $guild['tag']);
$guild['exp'] = number_format($totalexp[0]['totalexp']);

$tpl->guild = $guild;

$leaders = $db->SQL("SELECT * FROM characters WHERE guild = '$' AND guild_rank <= 2 ORDER BY guild_rank ASC, name ASC", strtoupper($_GET['tag']));
$recruiters = array();
$num_leaders = 0;
$num_recruiters = 0;

foreach ($leaders as $k => &$leader)
{
	$leader['name'] = ucfirst($leader['name']);
	$leader['gender'] = $leader['gender']?'Male':'Female';
	$leader['title'] = empty($leader['title'])?'-':ucfirst($leader['title']);
	$leader['exp'] = number_format($leader['exp']);
	$leader['gm'] = $leader['admin'] > 0;
	$leader['admin_str'] = adminrank_str($leader['admin']);

	if ($leader['guild_rank'] == 2)
	{
		$recruiters[] = $leader;
		
		unset($leaders[$k]);
	}
}
unset($leader);

$tpl->leaders = $leaders;
$tpl->recruiters = $recruiters;
$tpl->num_leaders = count($leaders);
$tpl->num_recruiters = count($recruiters);

$tpl->can_edit = (isset($chardata_guilds[$_GET['tag']]) && $chardata_guilds[$_GET['tag']]['leader']) || $GM;

$pagetitle .= ': '.strtoupper(htmlentities($_GET['tag']));
$tpl->pagetitle = $pagetitle;

$tpl->Execute('guild');
