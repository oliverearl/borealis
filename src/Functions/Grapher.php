<?php
// $key = null;
$entriesToRetrieve = null;
$numberOfEntries = null;
$validInput = false;

/*if (isset($_GET['graph']) && is_numeric($_GET['graph'])) {
    $key = sanitiseInteger($_GET['graph']);
    if (is_null($key)) {
        header('Location: index.php');
    }
    $graphOn = true;
}

$db = $app::getDbInstance(null);

if (isset($_GET['graph']) && strtolower($_GET['graph']) === 'latest') {
    $stmt = $db->prepare('SELECT id FROM magneto_meter ORDER BY id DESC LIMIT 1');
    $stmt->execute();
    $key = $stmt->fetch(PDO::FETCH_ASSOC)['id'];
}

if (isset($_GET['graph']) && strtolower($_GET['graph']) === 'all') {
    $stmt = $db->prepare('SELECT * FROM magneto_meter ORDER BY id DESC');
    $stmt->execute();
    $entries = $stmt->fetchAll();
    $graphOn = true;
}

if (isset($key) && !is_null($key)) {
    $stmt = $db->prepare('SELECT * FROM magneto_meter WHERE id = :key ORDER BY id DESC');
    $stmt->bindParam(':key', $key);
    $stmt->execute();
    $entries = $stmt->fetchAll();
}*/

// Check whether input comes from POST or GET and process/sanitise accordingly
// POST always takes priority and GET data is disregarded if it's present
if (isset($_POST['graph']) && is_numeric($_POST['graph'])) {
    $entriesToRetrieve = parseInput($_POST, $_POST['graph']);
} elseif (isset($_GET['graph']) && is_numeric($_GET['graph'])) {
    $entriesToRetrieve = parseInput($_GET, $_GET['graph']);
} elseif (isset($_GET['graph']) && $_GET['graph'] === 'latest') {
    require_once __DIR__ . '/../Models/Magnetometer.php';
    array_push($entriesToRetrieve, ole4\Magneto\Models\Magnetometer::getLatestEntry($app::getDbInstance()));
}

// Get the new number of entries - but since count() is so fickle, check if there's even any input first
if (isset($entriesToRetrieve)) {
    $numberOfEntries = count($entriesToRetrieve);
} else {
    // Otherwise redirect back to the homepage
    header('Location: index.php');
}

// If there's only one entry, retrieve just one lot of data from the database
if ($numberOfEntries === 0) {
    //$entries = oneEntry($entriesToRetrieve[0]);
}

function parseInput($array, $count)
{
    $entries[] = null;
    for ($i = 0; $i > $count; $i++) {
        // Only push value to array if clean
        if (!is_null(sanitiseInteger($array["graph_$i"]))) {
            $entries[$i] = $array["graph_$i"];
        }
    }
    return $entries;
}

function sanitiseInteger($input)
{
    $input = strip_tags(filter_var($input, FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE));
    return $input;
}

function oneEntry($key)
{
return null;
}
