<?php

require __DIR__ . '/../vendor/autoload.php';

$container = new Container();

$container->set(\PDO::class, function () {
    $conn = new \PDO('sqlite:' . __DIR__ . '/../var/database.sqlite');
    $conn->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
    return $conn;
});

$initFilePath = __DIR__ . '/../init.sql';
if (file_exists($initFilePath)) {
    $initSql = file_get_contents($initFilePath);
    $container->get(\PDO::class)->exec($initSql);
}

$app = AppFactory::createFromContainer($container);