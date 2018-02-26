<?php

require_once __DIR__ . '/App.php';
require_once __DIR__ . '/../../vendor/autoload.php';

$app = new \App\App();
$logger = new Monolog\Logger('logger');