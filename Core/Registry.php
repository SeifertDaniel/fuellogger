<?php

/**
 * This Software is the property of Data Development and is protected
 * by copyright law - it is NOT Freeware.
 * Any unauthorized use of this software without a valid license
 * is a violation of the license agreement and will be prosecuted by
 * civil and criminal law.
 * http://www.shopmodule.com
 *
 * @copyright (C) D3 Data Development (Inh. Thomas Dartsch)
 * @author        D3 Data Development - Daniel Seifert <support@shopmodule.com>
 * @link          http://www.oxidmodule.com
 */

namespace Daniels\Benzinlogger\Core;

use Daniels\Benzinlogger\Application\Model\Logger;

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
    public static function getRequest()
    {
        return self::getObject(Request::class);
    }

    /**
     * @return \Monolog\Logger
     */
    public static function getLogger()
    {
        if (!self::instanceExists('logger')) {
            self::set('logger', (new Logger())->getLogger());
        }
        return self::get('logger');
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