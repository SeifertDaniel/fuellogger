<?php

use Dotenv\Dotenv;

error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
ini_set('display_errors', 0);
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
    'DEBUGMODE',
    'TKAPIKEY',
    'LOCATIONLAT',
    'LOCATIONLNG',
    'COMMODITIESAPIKEY'
])->notEmpty();

if ($_ENV['DEBUGMODE']) {
    ini_set('display_errors', 1);
    error_reporting(E_ALL & ~E_DEPRECATED);
}

require_once BASE_PATH . 'functions.php';