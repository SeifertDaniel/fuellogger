<?php

namespace Daniels\FuelLogger\Core;

use Daniels\FuelLogger\Application\Model\Logger;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\Tools\Setup;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class Registry
{
    protected static $instances = [];

    public static function get($className)
    {
        $key = self::getStorageKey($className);

        return self::getObject($key);
    }

    public static function set($className, $instance)
    {
        $key = self::getStorageKey($className);

        if (is_null($instance)) {
            unset(self::$instances[$key]);

            return;
        }

        self::$instances[$key] = $instance;
    }

    /**
     * @return Request
     */
    public static function getRequest(): Request
    {
        return self::getObject(Request::class);
    }

    /**
     * @return \Monolog\Logger
     */
    public static function getLogger(): \Monolog\Logger
    {
        if (!self::instanceExists('logger')) {
            self::set('logger', (new Logger())->getLogger());
        }
        return self::get('logger');
    }

    /**
     * @return Environment
     */
    public static function getTwig(): Environment
    {
        if (!self::instanceExists('twig')) {
            $loader = new FilesystemLoader($_ENV['ROOTDIR'].'src/Templates');
            $twig = new Environment($loader, [
                'cache' => $_ENV['ROOTDIR'].'tmp/twig',
                'debug' => !Debug::useTwigCaching()
            ]);

            self::set('twig', $twig);
        }
        return self::get('twig');
    }

    /**
     * @return EntityManager
     * @throws ORMException
     */
    public static function getEntityManager(): EntityManager
    {
        if (function_exists('startProfile')) {
            startProfile( __METHOD__ );
        }

        $connectionParams = [
            'dbname' => $_ENV['DBNAME'],
            'user' => $_ENV['DBUSER'],
            'password' => $_ENV['DBPASS'],
            'host' => $_ENV['DBHOST'],
            'driver' => $_ENV['DBDRIVER'],
            'charset' => 'utf8mb4'
        ];

        $config = Setup::createAttributeMetadataConfiguration([__DIR__ . '/../Application/Model/Entities']);
        $em = EntityManager::create($connectionParams, $config);

        if (function_exists('stopProfile')) {
            stopProfile( __METHOD__ );
        }

        return $em;
    }

    public static function getKeys()
    {
        return array_keys(self::$instances);
    }

    public static function instanceExists($className)
    {
        $key = self::getStorageKey($className);

        return isset(self::$instances[$key]);
    }

    public static function getStorageKey($className)
    {
        return strtolower($className);
    }

    protected static function createObject($className)
    {
        return new $className;
    }

    protected static function getObject($className)
    {
        if (!isset(self::$instances[$className])) {
            self::$instances[$className] = self::createObject($className);
        }

        return self::$instances[$className];
    }
}