<?php

namespace App\Utils;

class UrlUtils
{
    private const GH_ARCHIVE_BASE_URL = 'https://data.gharchive.org';

    /**
     * Generate GitHub Archive URL based on year, month, day, and hour parameters
     * 
     * @param int $year The year (e.g., 2015)
     * @param int|null $month The month (1-12), null for entire year
     * @param int|null $day The day (1-31), null for entire month
     * @param int|null $hour The hour (0-23), null for entire day
     * @return string Returns URL or URL pattern with bash expansion syntax
     */
    public static function buildGithubArchiveUrl(int $year, ?int $month = null, ?int $day = null, ?int $hour = null): string
    {
        // Format year as 4 digits
        $yearStr = sprintf('%04d', $year);
        
        if ($month !== null && $day !== null && $hour !== null) {
            // Specific hour: 2015-01-01-15.json.gz
            $monthStr = sprintf('%02d', $month);
            $dayStr = sprintf('%02d', $day);
            $hourStr = sprintf('%d', $hour);
            return sprintf('%s/%s-%s-%s-%s.json.gz', self::GH_ARCHIVE_BASE_URL, $yearStr, $monthStr, $dayStr, $hourStr);
            
        } elseif ($month !== null && $day !== null) {
            // Entire day (all hours): 2015-01-01-{0..23}.json.gz
            $monthStr = sprintf('%02d', $month);
            $dayStr = sprintf('%02d', $day);
            return sprintf('%s/%s-%s-%s-{0..23}.json.gz', self::GH_ARCHIVE_BASE_URL, $yearStr, $monthStr, $dayStr);
            
        } elseif ($month !== null) {
            // Entire month: 2015-01-{01..31}-{0..23}.json.gz
            $monthStr = sprintf('%02d', $month);
            $daysInMonth = date('t', mktime(0, 0, 0, $month, 1, $year));
            $dayRange = sprintf('%02d..%02d', 1, $daysInMonth);
            return sprintf('%s/%s-%s-{%s}-{0..23}.json.gz', self::GH_ARCHIVE_BASE_URL, $yearStr, $monthStr, $dayRange);
            
        } else {
            // Entire year: 2015-{01..12}-{01..31}-{0..23}.json.gz
            return sprintf('%s/%s-{01..12}-{01..31}-{0..23}.json.gz', self::GH_ARCHIVE_BASE_URL, $yearStr);
        }
    }


}