<?php

namespace Daniels\FuelLogger\Core;

class Stage
{
    const DEVELOPMENT = 'dev';
    const PRODUCTION = 'production';

    /**
     * @return string[]
     */
    public static function getList(): array
    {
        return [self::DEVELOPMENT, self::PRODUCTION];
    }
}