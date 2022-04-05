<?php

if (!function_exists('startProfile')) {
    /**
     * @param string $sProfileName name of profile
     */
    function startProfile(string $sProfileName)
    {
        global $aStartTimes;
        global $executionCounts;
        if (!isset($executionCounts[$sProfileName])) {
            $executionCounts[$sProfileName] = 0;
        }
        if (!isset($aStartTimes[$sProfileName])) {
            $aStartTimes[$sProfileName] = 0;
        }
        $executionCounts[$sProfileName]++;
        $aStartTimes[$sProfileName] = microtime(true);
    }
}

if (!function_exists('stopProfile')) {
    /**
     * @param string $sProfileName name of profile
     */
    function stopProfile(string $sProfileName)
    {
        global $aProfileTimes;
        global $aStartTimes;
        if (!isset($aProfileTimes[$sProfileName])) {
            $aProfileTimes[$sProfileName] = 0;
        }
        $aProfileTimes[$sProfileName] += microtime(true) - $aStartTimes[$sProfileName];
    }
}

if (!function_exists('dumpVar')) {
    function dumpVar($mVar, $blToFile = false)
    {
        if ($blToFile) {
            $out = var_export($mVar, true);
            $f = fopen($_ENV['ROOTDIR'] . "tmp/vardump.txt", "a");
            fwrite($f, $out);
            fclose($f);
        } else {
            echo '<pre>';
            var_export($mVar);
            echo '</pre>';
        }
    }
}