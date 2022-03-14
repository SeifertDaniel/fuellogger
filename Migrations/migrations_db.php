<?php

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__."/../..");
$dotenv->load();
$dotenv->required(['DBHOST', 'DBNAME', 'DBUSER', 'DBPASS', 'DBDRIVER'])->notEmpty();

use Doctrine\DBAL\DriverManager;

return DriverManager::getConnection([
    'dbname' => $_ENV['DBNAME'],
    'user' => $_ENV['DBUSER'],
    'password' => $_ENV['DBPASS'],
    'host' => $_ENV['DBHOST'],
    'driver' => $_ENV['DBDRIVER'],
]);
