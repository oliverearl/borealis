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
    <?php require_once __DIR__ . '/partial/includes.phtml'; ?>
    <title><?= $app->getAppName(); ?></title>
</head>
<body>
    <?php require_once __DIR__ . '/partial/header.phtml'; ?>
    <div class="container-fluid">
        <?php require_once __DIR__ . '/partial/navigation.phtml'; ?>
        <?php require_once __DIR__ . '/Sample.phtml'; ?>
        <?php require_once __DIR__ . '/partial/scripts.phtml'; ?>
        <script>
            var ctx = document.getElementById("myChart");
            var myChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"],
                    datasets: [{
                        data: [15339, 21345, 18483, 24003, 23489, 24092, 12034],
                        lineTension: 0,
                        backgroundColor: 'transparent',
                        borderColor: '#007bff',
                        borderWidth: 4,
                        pointBackgroundColor: '#007bff'
                    }]
                },
                options: {
                    scales: {
                        yAxes: [{
                            ticks: {
                                beginAtZero: false
                            }
                        }]
                    },
                    legend: {
                        display: false,
                    }
                }
            });
        </script>
    </div>
</body>
</html>
