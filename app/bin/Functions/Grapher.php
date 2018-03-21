<?php
$key = null;
$entries = null;
$graphOn = null;

if (isset($_GET['graph']) && is_numeric($_GET['graph'])) {
    $key = sanitiseInteger($_GET['graph']);
    if (is_null($key)) {
        header('Location: index.php');
    }
    $graphOn = true;
}

/*if (isset($_POST['id']) && is_numeric($_POST['id'])) {
    var_dump($_POST);
    die();
    $key = sanitiseInteger($_POST['id']);
    if (is_null($key)) {
        header('Location: index.php');
    }
} */

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
}

function sanitiseInteger($input)
{
    $input = strip_tags(filter_var($input, FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE));
    return $input;
}
