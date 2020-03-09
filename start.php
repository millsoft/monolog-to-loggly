<?php

require_once(__DIR__ . "/vendor/autoload.php");
require_once(__DIR__ . "/MonologToLoggly.php");


$today = date("Y-m-d");
# $today = "2020-03-08";
$logfilename = $today . '.log';

$L = new MonologToLoggly();


$L->uploadLogFile("../logs/" . $logfilename);

