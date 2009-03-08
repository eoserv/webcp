<?php

$pagetitle = 'Stats';

require 'common.php';

$tpl->Execute('header');

echo "<p>stats etc";

$tpl->Execute('stats');

$tpl->Execute('footer');
