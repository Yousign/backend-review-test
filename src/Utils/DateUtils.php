<?php

namespace App\Utils;

class DateUtils
{
   /**
     * Validate date format
     * @param string $date
     * @return bool
     */
    public static function validateDate(string $date): bool
    {
        return preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) === 1;
    }

    /**
     * Validate hour range
     * @param int $hour
     * @return bool
     */
    public static function validateHour(int $hour): bool
    {
        return $hour >= 0 && $hour <= 23;
    }
}