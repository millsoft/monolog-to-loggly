<?php

require_once(__DIR__ . "/vendor/autoload.php");
require_once(__DIR__ . "/MonologToLoggly.php");


$L = new MonologToLoggly();
$L->uploadLogFile("logs/2018-10-06.log");
