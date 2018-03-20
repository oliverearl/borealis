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
} ?>
<!DOCTYPE HTML>
<html lang="<?= $app->getLanguage(); ?>">
<head>
    <?php require_once __DIR__ . '/partial/Includes.phtml'; ?>
    <title><?= $app->getAppName(); ?></title>
</head>
<body>
    <?php require_once __DIR__ . '/partial/Header.phtml'; ?>
    <div class="container-fluid">
        <?php require_once __DIR__ . '/partial/Navigation.phtml'; ?>
        <?php if (isset($_GET['p'])) {
            switch(strtolower($_GET['p'])) {
                case 'settings':
                    require_once __DIR__ . '/Settings.phtml';
                    break;
                case 'graph':
                    require_once __DIR__ . '/../bin/Functions/Grapher.php';
                    require_once __DIR__ . '/Graph.phtml';
                    break;
                default:
                    require_once __DIR__ . '/Home.phtml';
            }
        } else if (isset($_GET['graph']) && is_numeric($_GET['graph'])) {
            require_once __DIR__ . '/../bin/Functions/Grapher.php';
            require_once __DIR__ . '/Graph.phtml';
        } else {
            require_once __DIR__ . '/Home.phtml';
        } ?>
        <?php require_once __DIR__ . '/partial/Scripts.phtml'; ?>
    </div>
</body>
</html>
