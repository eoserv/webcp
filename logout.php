<?php

$pagetitle = 'Logout';
$checkcsrf = true;

require 'common.php';

$tpl->message = 'Logged out.';

$tpl->Execute('header');

unset($sess->username);

$tpl->Execute('footer');
