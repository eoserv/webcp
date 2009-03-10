<?php

$pagetitle = 'Edit Details';

require 'common.php';

if (!$logged)
{
	$tpl->message = 'You must be logged in to view this page.';
	$tpl->Execute('header');
	$tpl->Execute('footer');
	exit;
}

if (!empty($_POST['fullname']) && !empty($_POST['location']) && !empty($_POST['email']))
{
	if ($userdata['fullname'] == $_POST['fullname'] && $userdata['location'] == $_POST['location'] && $userdata['email'] == $_POST['email'])
	{

	}
	else
	{
		$db->SQL("UPDATE accounts SET fullname = '$', location = '$', email = '$' WHERE username = '$'", $_POST['fullname'], $_POST['location'], $_POST['email'], $sess->username);
		if ($db->AffectedRows() != 1)
		{
			$tpl->message = "Failed to update account info.";
		}
		else
		{
			$userdata = $db->SQL("SELECT * FROM accounts WHERE username = '$'", $sess->username);
			$tpl->userdata = $sess->userdata = $userdata[0];
			$tpl->message = "Account details updated.";
		}
	}
}

if (!empty($_POST['currentpassword']) && !empty($_POST['newpassword']) && !empty($_POST['repeatpassword']))
{
	if (!isset($tpl->message))
	{
		$tpl->message = '';
	}
	else
	{
		$tpl->message = $tpl->message . '<br>';
	}
	if ($_POST['newpassword'] != $_POST['repeatpassword'])
	{
		$tpl->message = $tpl->message . "Passwords did not match.";
	}
	else
	{
		$currentpassword = hash('sha256',$salt.($sess->username).substr($_POST['currentpassword'],0,12));
		echo $userdata['password'];
		echo "<br>";
		echo $currentpassword;
		if ($currentpassword != $userdata['password'])
		{
			$tpl->message = $tpl->message . "Current password did not match the one in the database.";
		}
		else
		{
			$newpassword = hash('sha256',$salt.($sess->username).substr($_POST['newpassword'],0,12));
			$db->SQL("UPDATE accounts SET password = '$' WHERE username = '$'", $newpassword, $sess->username);
			if ($db->AffectedRows() != 1)
			{
				$tpl->message = $tpl->message . "Failed to update password.";
			}
			else
			{
				$tpl->message = $tpl->message . "Password updated.";
			}
		}
	}
}

$tpl->Execute('header');

$tpl->Execute('editacc');

$tpl->Execute('footer');
