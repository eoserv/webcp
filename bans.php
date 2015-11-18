<?php

$pagetitle = 'Ban Control';

require 'common.php';

if (!$logged)
{
	$tpl->message = 'You must be logged in to view this page.';
	$tpl->Execute(null);
	exit;
}

if (!$GUARDIAN)
{
	$tpl->message = 'You must be a Guardian to view this page.';
	$tpl->Execute(null);
	exit;
}

$count = webcp_db_fetchall('SELECT COUNT(1) as count FROM bans');
$count = $count[0]['count'];

if ($count == 0)
{
	$tpl->message = 'No bans have been set yet.';
	$tpl->Execute(null);
	return;
}

if (isset($_POST['action']))
{
	switch($_POST['action'])
	{
		case 'cleanup':
			$rows = webcp_db_execute("DELETE FROM bans WHERE expires < ? AND expires != 0", time());
			$tpl->message = $rows." ban(s) removed.";
			break;

		case 'unban':
			if (isset($_POST['input'], $_POST['unban-username']))
			{
				$col = 'username';
				$val = $_POST['input'];
			}
			elseif (isset($_POST['input'], $_POST['unban-ip']))
			{
				$col = 'ip';
				$val = ip2long(webcp_decrypt_ip($_POST['input']));
				if (!$val)
				{
					$tpl->error = 'Malformed IP address.';
					$tpl->Execute('error');
					exit;
				}
			}
			elseif (isset($_POST['input'], $_POST['unban-hdid']))
			{
				$col = 'hdid';
				$hdid = explode('-', webcp_decrypt_hdid($_POST['input']));
				if (isset($hdid[1]))
				{
					$val = hexdec($hdid[0]) * 0x10000 + hexdec($hdid[1]);
				}
				else
				{
					$val = hexdec($hdid[0]);
				}
			}
			else
			{
				$tpl->error = 'Invalid arguments to unban action.';
				$tpl->Execute('error');
				exit;
			}
			$rows = webcp_db_execute("DELETE FROM bans WHERE $col = ?", $val);
			$tpl->message = $rows ." ban(s) removed.";
			break;
	}
}

$page = isset($_GET['page']) ? $_GET['page'] : 1;
$pages = ceil($count / $perpage);

if ($page < 1 || $page > $pages)
{
	$page = max(min($page, $pages), 1);
}

$start = ($page-1) * $perpage;

$bans = webcp_db_fetchall("SELECT * FROM bans ORDER BY expires DESC LIMIT ?,?", $start, $perpage);

foreach ($bans as &$ban)
{
	$ban['username'] = $ban['username']===null?'-':$ban['username'];
	$ban['nouser'] = $ban['username']===null;
	$ban['noip'] = $ban['ip']===null;
	$ban['nohdid'] = $ban['hdid']===null;
	$ban['ip_str'] = $ban['ip']===null?'-':webcp_encrypt_ip(long2ip($ban['ip']));
	$ban['setter'] = ucfirst($ban['setter']);

	if (!is_null($ban['hdid']))
	{
		$ban['hdid_str'] = sprintf("%08x", (double)$ban['hdid']);
		$ban['hdid_str'] = strtoupper(substr($ban['hdid_str'],0,4).'-'.substr($ban['hdid_str'],4,4));
		$ban['hdid_str'] = webcp_encrypt_hdid($ban['hdid_str']);
	}
	else
	{
		$ban['hdid_str'] = '-';
	}

	if ($ban['expires'] <= 0)
	{
		$ban['remaining'] = '<b>Permanent</b>';
	}
	elseif ($ban['expires'] <= time())
	{
		$ban['remaining'] = '<i>Expired</i>';
	}
	else
	{
		$ban['remaining'] = timeago_full($ban['expires'], time(), false);
	}
}

$pagination = generate_pagination($pages, $page);

$tpl->page = $page;
$tpl->pages = $pages;
$tpl->pagination = $pagination;
$tpl->perpage = $perpage;
$tpl->showing = count($bans);
$tpl->start = $start+1;
$tpl->end = min($start+$perpage, $count);
$tpl->count = $count;

$tpl->bans = $bans;

$tpl->Execute('bans');
