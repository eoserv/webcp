<?php

$pagetitle = 'Logout';
$checkcsrf = true;

require 'common.php';

$tpl->message = 'Logged out.';
$tpl->logged = $logged = false;
unset($sess->username);

$tpl->Execute('header');

$tpl->Execute('footer');
