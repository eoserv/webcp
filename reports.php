<?php

$pagetitle = 'Report Control';

require 'common.php';

if (!$logged)
{
	$tpl->message = 'You must be logged in to view this page.';
	$tpl->Execute(null);
	exit;
}

if (!$GUIDE)
{
	$tpl->message = 'You must be a Light Guide to view this page.';
	$tpl->Execute(null);
	exit;
}

$count = webcp_db_fetchall('SELECT COUNT(1) as count FROM reports');
$count = $count[0]['count'];

if ($count == 0)
{
	$tpl->message = 'No reports have been made yet.';
	$tpl->Execute(null);
	return;
}

$page = isset($_GET['page']) ? $_GET['page'] : 1;
$pages = ceil($count / $perpage);

if ($page < 1 || $page > $pages)
{
	$page = max(min($page, $pages), 1);
}

$start = ($page-1) * $perpage;

$reports = webcp_db_fetchall("SELECT reporter, reported, reason, time FROM reports ORDER BY time DESC LIMIT ?,?", $start, $perpage);

foreach ($reports as &$report)
{
	$report['reporter'] = ucfirst($report['reporter']);
	$report['reported'] = ucfirst($report['reported']);
	$report['time_ago'] = timeago_full($report['time'], time());
}

$pagination = generate_pagination($pages, $page);

$tpl->page = $page;
$tpl->pages = $pages;
$tpl->pagination = $pagination;
$tpl->perpage = $perpage;
$tpl->showing = count($reports);
$tpl->start = $start+1;
$tpl->end = min($start+$perpage, $count);
$tpl->count = $count;

$tpl->reports = $reports;

$tpl->Execute('reports');
