<?php

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use App\App;

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
date_default_timezone_set('Europe/London');
session_start();

require_once __DIR__ . '/App.php';
require_once __DIR__ . '/../../vendor/autoload.php';

$app = new App();
$log = new Logger('logger');
$log->pushHandler(new StreamHandler(__DIR__ . '/../storage/logs/App.log', Logger::WARNING));
