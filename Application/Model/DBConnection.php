<?php

namespace Daniels\FuelLogger\Application\Model;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Exception;

class DBConnection
{
    private static $instance = null;

    /**
     * @return Connection
     * @throws \Doctrine\DBAL\Exception
     */
    public static function getConnection()
    {
        if (static::$instance === null) {
            $connectionParams = [
                'dbname' => $_ENV['DBNAME'],
                'user' => $_ENV['DBUSER'],
                'password' => $_ENV['DBPASS'],
                'host' => $_ENV['DBHOST'],
                'driver' => $_ENV['DBDRIVER'],
                'charset'   => 'utf8mb4'
            ];
            static::$instance = DriverManager::getConnection($connectionParams);
        }

        return static::$instance;
    }

    /**
     * is not allowed to call from outside to prevent from creating multiple instances,
     * to use the singleton, you have to obtain the instance from Singleton::getInstance() instead
     */
    private function __construct()
    {
    }

    /**
     * prevent the instance from being cloned (which would create a second instance of it)
     */
    private function __clone()
    {
    }

    /**
     * prevent from being unserialized (which would create a second instance of it)
     */
    public function __wakeup()
    {
        throw new Exception("Cannot unserialize singleton");
    }

    public static function generateRandomString($length = 32) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
}