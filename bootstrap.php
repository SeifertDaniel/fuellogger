<?php

use Daniels\FuelLogger\Core\Debug;
use Dotenv\Dotenv;

define('INSTALLATION_ROOT_PATH', dirname(__DIR__));
const BASE_PATH = INSTALLATION_ROOT_PATH . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR;
const VENDOR_PATH = INSTALLATION_ROOT_PATH . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR;
const TMP_PATH = INSTALLATION_ROOT_PATH . DIRECTORY_SEPARATOR . 'tmp' . DIRECTORY_SEPARATOR;

require_once VENDOR_PATH.'autoload.php';

$dotenv = Dotenv::createImmutable(__DIR__."/..");
$dotenv->load();
$dotenv->required([
    'DBHOST',
    'DBNAME',
    'DBUSER',
    'DBPASS',
    'DBDRIVER',
    'DEBUGMODES',
    'TKAPIKEY',
    'LOCATIONLAT',
    'LOCATIONLNG',
    'RADIUS',
    'COMMODITIESAPIKEY'
])->notEmpty();

if (Debug::displayErrors()) {
    ini_set('display_errors', 1);
    error_reporting(E_ALL & ~E_DEPRECATED);
} else {
    ini_set('display_errors', 0);
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
}

\Doctrine\DBAL\Types\Type::addType('uuid', 'Ramsey\Uuid\Doctrine\UuidType');

require_once BASE_PATH . 'functions.php';