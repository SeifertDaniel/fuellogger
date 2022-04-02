<?php

namespace Daniels\FuelLogger\Core;

class Debug
{
    const MODE_LOGGING = 1;
    const MODE_DISPLAYERRORS = 2;
    const MODE_NOTWIGCACHE = 4;
    const MODE_SHOWPROFILING = 8;

    /**
     * @param $dTotalTime
     * @return string
     */
    public function formatExecutionTime($dTotalTime): string
    {
        $log = 'Execution time:' . round($dTotalTime, 4) . '<br />';
        global $aProfileTimes;
        global $executionCounts;
        if (is_array($aProfileTimes)) {
            $log .= "----------------------------------------------------------<br>" . PHP_EOL;
            arsort($aProfileTimes);
            $log .= "<table cellspacing='10px' style='border: 1px solid #000' class='debugTable'>";
            foreach ($aProfileTimes as $key => $val) {
                $log .= "<tr><td style='border-bottom: 1px dotted #000;min-width:300px;'>Profile $key: </td><td style='border-bottom: 1px dotted #000;min-width:100px;'>" . round($val, 5) . "s</td>";
                if ($dTotalTime) {
                    $log .= "<td style='border-bottom: 1px dotted #000;min-width:100px;'>" . round($val * 100 / $dTotalTime, 2) . "%</td>";
                }
                if ($executionCounts[$key]) {
                    $log .= " <td style='border-bottom: 1px dotted #000;min-width:50px;padding-right:30px;' align='right'>" . $executionCounts[$key] . "</td>"
                        . "<td style='border-bottom: 1px dotted #000;min-width:15px; '>*</td>"
                        . "<td style='border-bottom: 1px dotted #000;min-width:100px;'>" . round($val / $executionCounts[$key], 5) . "s</td>" . PHP_EOL;
                } else {
                    $log .= " <td colspan=3 style='border-bottom: 1px dotted #000;min-width:100px;'> not stopped correctly! </td>" . PHP_EOL;
                }
                $log .= '</tr>';
            }
            $log .= "</table>";
        }

        return $log;
    }

    /**
     * @return bool
     */
    public static function useTwigCaching(): bool
    {
        return false === (($_ENV['DEBUGMODES'] & self::MODE_NOTWIGCACHE) === self::MODE_NOTWIGCACHE);
    }

    /**
     * @return bool
     */
    public static function displayErrors(): bool
    {
        return ($_ENV['DEBUGMODES'] & self::MODE_DISPLAYERRORS) === self::MODE_DISPLAYERRORS;
    }

    /**
     * @return bool
     */
    public static function logDebugMessages(): bool
    {
        return ($_ENV['DEBUGMODES'] & self::MODE_LOGGING) === self::MODE_LOGGING;
    }

    /**
     * @return bool
     */
    public static function showProfiling(): bool
    {
        return ($_ENV['DEBUGMODES'] & self::MODE_SHOWPROFILING) === self::MODE_SHOWPROFILING;
    }
}