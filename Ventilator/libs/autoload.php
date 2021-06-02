<?php
declare(strict_types=1);

<?php
//include_once __DIR__ . '/traits.php';

foreach (glob(__DIR__ . '/*.php') as $filename) {
    if (basename($filename) != 'autoload.php') {
        include_once $filename;
    }
}