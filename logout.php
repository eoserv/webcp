<?php

$pagetitle = 'Logout';
$checkcsrf = true;

require 'common.php';

$tpl->loggingout = true;
$tpl->message = 'Logged out.';
$tpl->logged = $logged = false;
$tpl->GUIDE = $GUIDE = false;
$tpl->GUARDIAN = $GUARDIAN = false;
$tpl->GM = $GM = false;
$tpl->HGM = $HGM = false;
$tpl->chardata_guilds = $chardata_guilds = array();
unset($sess->username);

$tpl->Execute('logout');
