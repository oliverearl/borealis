<?php
if (is_null($app)) {
    die();
}
?>
<!DOCTYPE HTML>
<html lang="<?= $app->getLanguage(); ?>">
<head>
    <?php require_once __DIR__ . '../templates/partial/Includes.phtml'; ?>
    <title><?= $app->getAppName() . ' - ' . $app->getConfigEntry('appVersion');?></title>
</head>
<body>
<?php require_once __DIR__ . '../templates/partial/Header.phtml'; ?>
<div class="container-fluid">
    <?php require_once __DIR__ . '../templates/partial/Navigation.phtml'; ?>
    <?php
    if (isset($_GET['p'])) {
        switch(strtolower($_GET['p'])) {
            case 'settings':
                require_once __DIR__ . '../templates/Settings.phtml';
                break;
            case 'about':
                require_once __DIR__ . '../templates/About.phtml';
                break;
            case 'magnetometer':
                require_once __DIR__ . '../templates/Magnetometer.phtml';
                break;
            case 'graph':
                require_once __DIR__ . '/Functions/Grapher.php';
                require_once __DIR__ . '../templates/Graph.phtml';
                break;
            case 'view':
                require_once __DIR__ . '/Functions/Tabler.php';
                break;
            default:
                require_once __DIR__ . '/../templates/Home.phtml';
        }
    } else {
        require_once __DIR__ . '/../templates/Home.phtml';
    }
    require_once __DIR__ . '/../templates/partial/Scripts.phtml';
    ?>
</div>
</body>
</html>
