<?php

/**
 * This file is only intended for use for the Demonstration, simply because I haven't got proper routing configured yet.
 *
 * So this is an amalgamation of object-oriented and procedural code just for the purpose of getting this to work.
 * It will do for now.
 *
 * THIS CODE IS
 * C O M P L E T E G A R B A G E
 * O O
 * M   M
 * P     P
 * L       L
 * E         E
 * T           T
 * E             E
 * G               G
 * A                 A
 * R                   R
 * B                     B
 * A                       A
 * G                         G
 * E                           E
 */
if (is_null($app)) {
    die();
}
?>
<!DOCTYPE HTML>
<html lang="<?= $app->getLanguage(); ?>">
<head>
    <?php require_once __DIR__ . '/Deprecated/Includes.phtml'; ?>
    <title><?= $app->getAppName() . ' - ' . $app->getConfigEntry('appVersion'); ?></title>
</head>
<body>
    <?php require_once __DIR__ . '/Deprecated/Header.phtml'; ?>
    <div class="container-fluid">
        <?php require_once __DIR__ . '/Deprecated/Navigation.phtml'; ?>
        <?php if (isset($_GET['p'])) {
            switch(strtolower($_GET['p'])) {
                case 'settings':
                    require_once __DIR__ . '/Settings.phtml';
                    break;
                case 'about':
                    require_once __DIR__ .'/About.phtml';
                    break;
                case 'graph':
                    require_once __DIR__ . '/../bin/Functions/Grapher.php';
                    require_once __DIR__ . '/Graph.phtml';
                    break;
                case 'magnetometer':
                    require_once __DIR__ .'/Magnetometer.phtml';
                    break;
                default:
                    require_once __DIR__ . '/Home.phtml';
            }
        } else if (isset($_GET['graph']) && (is_numeric($_GET['graph']) || $_GET['graph'] === 'latest' || $_GET['graph'] === 'all')) {
            require_once __DIR__ . '/../bin/Functions/Grapher.php';
            require_once __DIR__ . '/Graph.phtml';
        } else {
            require_once __DIR__ . '/Home.phtml';
        } ?>
        <?php require_once __DIR__ . '/Deprecated/Scripts.phtml'; ?>
    </div>
</body>
</html>
