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