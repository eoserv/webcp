<?php

$pagetitle = 'Search';

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

if (!empty($_GET['searchtype']))
{
	switch ($_GET['searchtype'])
	{
		case 'account':
			if (!isset($_GET['username'])) $_GET['username'] = '%';
			if (!isset($_GET['computer'])) $_GET['computer'] = '%';
			if (!isset($_GET['hdid']))     $_GET['hdid'] = '';
			if (!isset($_GET['ip']))       $_GET['ip'] = '';

			if (isset($_GET['username'],$_GET['computer'],$_GET['hdid'],$_GET['ip']))
			{
				$original_ip = $_GET['ip'];
				$original_computer = $_GET['computer'];
				$original_hdid = $_GET['hdid'];

				if ($_GET['computer'] != '%')
					$_GET['computer'] = webcp_decrypt_computer($_GET['computer']);

				if ($_GET['hdid'] != '')
					$_GET['hdid'] = webcp_decrypt_hdid($_GET['hdid']);

				if ($_GET['ip'] != '')
					$_GET['ip'] = webcp_decrypt_ip($_GET['ip']);

				$hdid = explode('-', $_GET['hdid']);
				if (isset($hdid[1]))
				{
					$hdid = intval(hexdec($hdid[0]) * 0x10000 + hexdec($hdid[1]));
					if ($hdid > 0x7FFFFFFF)
						$hdid = -0x100000000 + $hdid;
					$hdidq = " AND hdid = '".$hdid."'";
				}
				else
				{
					$hdidq = "";
				}

				$ip = intval(ip2long($_GET['ip']));

				if ($ip != 0)
				{
					$ipq = " AND (regip = '".long2ip($ip)."' OR lastip='".long2ip($ip)."')";
				}
				else
				{
					$ipq = "";
				}

				$username = strtolower($_GET['username']);
				$computer = strtoupper($_GET['computer']);

				$count = webcp_db_fetchall("SELECT COUNT(1) as count FROM accounts WHERE username LIKE ? AND computer LIKE ?$hdidq$ipq", $username, $computer);
				$count = $count[0]['count'];

				$page = isset($_GET['page']) ? $_GET['page'] : 1;
				$pages = ceil($count / $perpage);

				if ($page < 1 || $page > $pages)
				{
					$page = max(min($page, $pages), 1);
				}

				$start = ($page-1) * $perpage;
				
				if ($count == 0)
				{
					$tpl->message = 'No results found for your search.';
					$tpl->Execute(null);
					return;
				}
				
				$accounts = webcp_db_fetchall("SELECT * FROM accounts WHERE username LIKE ? AND computer LIKE ?$hdidq$ipq LIMIT ?,?", $username, $computer, $start, $perpage);

				$acclistq = '';
				$acclistqa = array();

				foreach ($accounts as &$account)
				{
					$account['computer'] = webcp_encrypt_computer($account['computer']);
					$account['computer_str'] = webcp_trunc($account['computer'], 15);
					$account['hdid_str'] = sprintf("%08x", (double)$account['hdid']);
					$account['hdid_str'] = strtoupper(substr($account['hdid_str'],0,4).'-'.substr($account['hdid_str'],4,4));
					$account['hdid_str'] = webcp_encrypt_hdid($account['hdid_str']);
					$acclistq .= "account = ? OR ";
					$acclistqa[] = $account['username'];
				}
				unset($account);

				$acclistq = substr($acclistq, 0, -4);

				if (!$acclistq)
				{
					trigger_error("No accounts were selected");
				}

				$charcounts = webcp_db_fetchall_array("SELECT name,admin,account FROM characters WHERE $acclistq", $acclistqa);

				foreach ($accounts as $i => &$account)
				{
				    $charcount = 0;
				    $charlist = array();
				    foreach ($charcounts as $character)
				    {
				        if ($character['account'] == $account['username'])
				        {
				            ++$charcount;
				            $charlist[] = array(
				                'name' => ucfirst($character['name']),
				                'admin' => $character['admin'],
				                'gm' => $character['admin'] > 0
				            );
				        }
				    }
				    $account['characters'] = $charcount;
				    $account['character_list'] = $charlist;
				}

				unset($account);

				$pagination = generate_pagination($pages, $page, '?searchtype=account&username='.urlencode($_GET['username']).'&computer='.urlencode($original_computer).'&hdid='.urlencode($original_hdid).'&ip='.urlencode($original_ip));

				$tpl->page = $page;
				$tpl->pages = $pages;
				$tpl->pagination = $pagination;
				$tpl->perpage = $perpage;
				$tpl->showing = count($accounts);
				$tpl->start = $start+1;
				$tpl->end = min($start+$perpage, $count);
				$tpl->count = $count;

				$tpl->accounts = $accounts;

				$tpl->original_ip = $original_ip;
				$tpl->original_computer = $original_computer;
				$tpl->original_hdid = $original_hdid;

				$tpl->Execute('accsearch_results');
			}
			else
			{
				$tpl->message = 'Invalid search parameters.';
				$tpl->Execute(null);
			}
			break;
		
		case 'character':
			if (!isset($_GET['name'])) $_GET['name'] = '%';

			if (isset($_GET['name']))
			{
				$name = strtolower($_GET['name']);
				
				$count = webcp_db_fetchall("SELECT COUNT(1) as count FROM characters WHERE name LIKE ?", $name);
				$count = $count[0]['count'];

				$page = isset($_GET['page']) ? $_GET['page'] : 1;
				$pages = ceil($count / $perpage);

				if ($page < 1 || $page > $pages)
				{
					$page = max(min($page, $pages), 1);
				}

				$start = ($page-1) * $perpage;
				
				if ($count == 0)
				{
					$tpl->message = 'No results found for your search.';
					$tpl->Execute(null);
					return;
				}
				
				$characters = webcp_db_fetchall("SELECT * FROM characters WHERE name LIKE ? LIMIT ?,?", $name, $start, $perpage);

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

				$pagination = generate_pagination($pages, $page, '?searchtype=character&name='.urlencode($_GET['name']));

				$tpl->page = $page;
				$tpl->pages = $pages;
				$tpl->pagination = $pagination;
				$tpl->perpage = $perpage;
				$tpl->showing = count($characters);
				$tpl->start = $start+1;
				$tpl->end = min($start+$perpage, $count);
				$tpl->count = $count;

				$tpl->characters = $characters;
				$tpl->Execute('charsearch_results');
			}
			else
			{
				$tpl->message = 'Invalid search parameters.';
				$tpl->Execute(null);
			}
			break;

		case 'guild':
			if (!isset($_GET['tag']))  $_GET['tag'] = '%';
			if (!isset($_GET['name'])) $_GET['name'] = '%';

			if (isset($_GET['tag'], $_GET['name']))
			{
				$tag = strtoupper($_GET['tag']);
				$name = strtolower($_GET['name']);

				$count = webcp_db_fetchall("SELECT COUNT(1) as count FROM guilds WHERE tag LIKE ? AND name LIKE ?", $tag, $name);
				$count = $count[0]['count'];

				$page = isset($_GET['page']) ? $_GET['page'] : 1;
				$pages = ceil($count / $perpage);

				if ($page < 1 || $page > $pages)
				{
					$page = max(min($page, $pages), 1);
				}

				$start = ($page-1) * $perpage;
				
				if ($count == 0)
				{
					$tpl->message = 'No results found for your search.';
					$tpl->Execute(null);
					return;
				}
				
				$guilds = webcp_db_fetchall("SELECT * FROM guilds WHERE tag LIKE ? AND name LIKE ? LIMIT ?,?", $tag, $name, $start, $perpage);

				foreach ($guilds as &$guild)
				{
					$membercount = webcp_db_fetchall("SELECT COUNT(1) as count FROM characters WHERE guild = ?", $guild['tag']);
					$totalexp = webcp_db_fetchall("SELECT SUM(exp) as totalexp FROM characters WHERE guild = ? AND admin = 0", $guild['tag']);
					$guild['tag'] = trim(strtoupper($guild['tag']));
					$guild['name'] = ucfirst($guild['name']);
					$guild['members'] = number_format($membercount[0]['count']);
					$guild['exp'] = number_format($totalexp[0]['totalexp']);
				}
				unset($guild);

				$pagination = generate_pagination($pages, $page, '?searchtype=guild&tag='.urlencode($_GET['tag']).'&name='.urlencode($_GET['name']));

				$tpl->page = $page;
				$tpl->pages = $pages;
				$tpl->pagination = $pagination;
				$tpl->perpage = $perpage;
				$tpl->showing = count($guilds);
				$tpl->start = $start+1;
				$tpl->end = min($start+$perpage, $count);
				$tpl->count = $count;

				$tpl->guilds = $guilds;

				$tpl->Execute('guildsearch_results');
			}
			else
			{
				$tpl->message = 'Invalid search parameters.';
				$tpl->Execute(null);
			}
			break;
		
		default:
			$tpl->message = 'Invalid search type.';
			$tpl->Execute(null);
			exit;

	}
}
else 
{
	$tpl->Execute('search');
}
