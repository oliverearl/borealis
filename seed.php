<?php PHP_SAPI === 'cli' or die('This file must be run at the command line.');
require 'vendor/autoload.php';

error_reporting(E_ALL);
date_default_timezone_set('Europe/London');

use Icewind\SMB\Server;
use ole4\Magneto\Models\Magnetometer;

$username = 'imaps\yourusernamehere';
$password = 'yourpasswordhere';

$server = new Server('imapspc0017.imaps.aber.ac.uk', $username, $password);
$share = $server->getShare('magdata');
$tempFile = __DIR__ . '/storage/csv/temp.csv';

$years = [2014, 2015, 2016, 2017, 2018];
foreach ($years as $year) {
    $dir = $share->dir($year);
    foreach ($dir as $file) {
        $share->get($file->getPath(), $tempFile);

        $date = null;
        $values = [];
        $temps = [];

        $entries = array_map('str_getcsv', file($tempFile));
        foreach ($entries as $entry) {
            $date = $entry[0];
            array_push($values, $entry[1]);
            array_push($temps, $entry[2]);
        }

        // Correctly convert and format the timestamp from labView to UNIX and into a date
        $formattedDate = date('Y-m-d H:i:s', Magnetometer::convertToUnix($date));

        // Remove any blanks so that averages aren't poisoned, and then find the average in the arrays
        // https://stackoverflow.com/questions/33461430/how-to-find-average-from-array-in-php
        $values =       array_filter($values);
        $averageValue = array_sum($values) / count($values);

        $temps =        array_filter($temps);
        $averageTemp =  array_sum($temps) / count($temps);

        // Write to database
        $magnetometer = new Magnetometer(
            null,
            $formattedDate,
            $averageValue,
            $averageTemp,
            null
        );
        $magnetometer->saveMagnetometer();
        echo "Magnetometry entry {$formattedDate} entered.\n\n";
    }
}
echo 'Done';
