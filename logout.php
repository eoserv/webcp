<?php

$pagetitle = 'Logout';
$checkcsrf = true;

require 'common.php';

$tpl->message = 'Logged out.';
$tpl->logged = $logged = false;
$tpl->GM = $GM = false;
$tpl->HGM = $HGM = false;
unset($sess->username);

$tpl->Execute('header');

$tpl->Execute('footer');
