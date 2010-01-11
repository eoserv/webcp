<?php

$pagetitle = 'Search';

if (!empty($_GET['searchtype']))
{
	$checkcsrf = true;
}
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
			if (isset($_GET['username'],$_GET['computer'],$_GET['hdid']))
			{
				$hdid = explode('-', $_GET['hdid']);
				if (isset($hdid[1]))
				{
					$hdid = hexdec($hdid[0]) * 0x10000 + hexdec($hdid[1]);
					$hdidq = " AND hdid = '".$hdid."'";
				}
				else
				{
					$hdidq = "";
				}

				$username = strtolower($_GET['username']);
				$computer = strtoupper($_GET['computer']);

				$count = $db->SQL("SELECT COUNT(1) as count FROM accounts WHERE username LIKE '$' AND computer LIKE '$'$hdidq", $username, $computer);
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
				
				$accounts = $db->SQL("SELECT * FROM accounts WHERE username LIKE '$' AND computer LIKE '$'$hdidq LIMIT #,#", $username, $computer, $start, $perpage);

				$acclistq = '';

				foreach ($accounts as &$account)
				{
					$account['hdid_str'] = sprintf("%08x", (double)$account['hdid']);
					$account['hdid_str'] = strtoupper(substr($account['hdid_str'],0,4).'-'.substr($account['hdid_str'],4,4));
					$acclistq .= "account = '".$db->Escape($account['username'])."' OR ";
				}
				unset($account);

				$acclistq = substr($acclistq, 0, -4);

				if (!$acclistq)
				{
					trigger_error("No accounts were selected");
				}

				$charcounts = $db->SQL("SELECT account FROM characters WHERE $acclistq");

				foreach ($accounts as $i => &$account)
				{
					$charcount = 0;
					foreach ($charcounts as $character)
					{
						if ($character['account'] == $account['username'])
						{
							++$charcount;
						}
					}
					$account['characters'] = $charcount;
				}
				unset($account);

				$pagination = generate_pagination($pages, $page, '?searchtype=account&username='.urlencode($_GET['username']).'&computer='.urlencode($_GET['computer']).'&hdid='.urlencode($_GET['hdid']).'&csrf='.$csrf);

				$tpl->page = $page;
				$tpl->pages = $pages;
				$tpl->pagination = $pagination;
				$tpl->perpage = $perpage;
				$tpl->showing = count($accounts);
				$tpl->start = $start+1;
				$tpl->end = min($start+$perpage, $count);
				$tpl->count = $count;

				$tpl->accounts = $accounts;

				$tpl->Execute('accsearch_results');
			}
			else
			{
				$tpl->message = 'Invalid search parameters.';
				$tpl->Execute(null);
			}
			break;
		
		case 'character':
			if (isset($_GET['name']))
			{
				$name = strtolower($_GET['name']);
				
				$count = $db->SQL("SELECT COUNT(1) as count FROM characters WHERE name LIKE '$'", $name);
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
				
				$characters = $db->SQL("SELECT * FROM characters WHERE name LIKE '$' LIMIT #,#", $name, $start, $perpage);

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

				$pagination = generate_pagination($pages, $page, '?searchtype=character&name='.urlencode($_GET['name']).'&csrf='.$csrf);

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
			if (isset($_GET['tag'], $_GET['name']))
			{
				$tag = strtoupper($_GET['tag']);
				$name = strtolower($_GET['name']);

				$count = $db->SQL("SELECT COUNT(1) as count FROM guilds WHERE tag LIKE '$' AND name LIKE '$'", $tag, $name);
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
				
				$guilds = $db->SQL("SELECT * FROM guilds WHERE tag LIKE '$' AND name LIKE '$' LIMIT #,#", $tag, $name, $start, $perpage);

				foreach ($guilds as &$guild)
				{
					$membercount = $db->SQL("SELECT COUNT(1) as count FROM characters WHERE guild = '$'", $guild['tag']);
					$totalexp = $db->SQL("SELECT SUM(exp) as totalexp FROM characters WHERE guild = '$' AND admin = 0", $guild['tag']);
					$guild['tag'] = trim(strtoupper($guild['tag']));
					$guild['name'] = ucfirst($guild['name']);
					$guild['members'] = number_format($membercount[0]['count']);
					$guild['exp'] = number_format($totalexp[0]['totalexp']);
				}
				unset($guild);

				$pagination = generate_pagination($pages, $page, '?searchtype=guild&tag='.urlencode($_GET['tag']).'&name='.urlencode($_GET['name']).'&csrf='.$csrf);

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
