<?php

$pagetitle = 'Report Chat Log';

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

if (!isset($_GET['reporter'], $_GET['reported'], $_GET['time']))
{
	$tpl->message = 'No report specified.';
	$tpl->Execute(null);
	exit;
}

$report = $db->SQL("SELECT * FROM reports WHERE reporter = '$' AND reported = '$' AND time = #", strtolower($_GET['reporter']), strtolower($_GET['reported']), $_GET['time']);

if (empty($report[0]))
{
	$tpl->message = 'Report not found.';
	$tpl->Execute(null);
	exit;
}

$report = $report[0];

function chat_log_format($log)
{
global $phpext;

	static $chat_channels = array(
		'' => 'Public',
		'!' => 'Private',
		'\'' => 'Party',
		'&amp;' => 'Guild',
		'~' => 'Global',
		'@' => 'Announcement',
		'+' => 'Admin'
	);

	$lines = explode("\r\n", htmlentities($log));
	
	$result = "<table class=\"chat_log\">";
	
	foreach ($lines as $line)
	{
		if (trim($line) == '')
			continue;
		
		if ($line[0] == '!')
		{
			$line = explode(' ', $line, 4);
			$line[1] = $line[1] . ' <a href="character' . $phpext . '?name=' . rtrim(ucfirst($line[2]), ':') . '">' . ucfirst($line[2]) . '</a>';
			$line[2] = $line[3];
			unset($line[3]);
		}
		else
		{
			$line = explode(' ', $line, 3);
			$line[1] = '<a href="character' . $phpext . '?name=' . rtrim(ucfirst($line[1]), ':') . '">' . ucfirst($line[1]) . '</a>';
		}
		
		if (count($line) < 3)
			continue;
		
		$chat_channel = '';
		
		if (isset($chat_channels[$line[0]]))
			$chat_channel = $chat_channels[$line[0]];
		
		$result .= '<tr><th title="' . $chat_channel . '">' . implode('<td>', $line);
	}
	
	$result .= "</table>";
	return $result;
}

$report['reporter'] = ucfirst($report['reporter']);
$report['reported'] = ucfirst($report['reported']);
$report['time_ago'] = timeago_full($report['time'], time());
$report['chat_log_html'] = chat_log_format($report['chat_log']);

$tpl->report = $report;

$tpl->pagetitle = $pagetitle;

$tpl->Execute('report_log');
