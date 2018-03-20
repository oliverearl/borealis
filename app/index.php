<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
require_once __DIR__ . '/bin/Bootstrap.php';

/**
 * This is a temporary workaround until I figure out a more object-oriented way of doing the routing.
 * I mean this is VERY temporary. Because this is ugly. Dogshit ugly.
 * More information inside the /templates/demonstrationrouter.php file.
 */
require_once __DIR__ . '/templates/DemonstrationRouter.php';
