<?php

$pagetitle = 'Redirect';
require 'common.php';

header('Location: ./search' . $phpext . '?searchtype=guild');
exit;

