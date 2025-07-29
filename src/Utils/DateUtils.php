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

    /**
     * Check if two dates are in the same month
     * @param string $date1
     * @param string $date2
     * @return bool
     */
    public static function areInSameMonth(string $date1, string $date2): bool
    {
        $dateTime1 = \DateTime::createFromFormat('Y-m-d', $date1);
        $dateTime2 = \DateTime::createFromFormat('Y-m-d', $date2);
        
        if (!$dateTime1 || !$dateTime2) {
            return false;
        }
        
        return $dateTime1->format('Y-m') === $dateTime2->format('Y-m');
    }
}