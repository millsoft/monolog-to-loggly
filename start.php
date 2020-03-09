<?php

require_once(__DIR__ . "/vendor/autoload.php");
require_once(__DIR__ . "/MonologToLoggly.php");


$today = date("Y-m-d");
//$today = "2018-10-09";
$logfilename = $today . '.log';

$L = new MonologToLoggly();


$L->uploadLogFile("../logs/" . $logfilename);

