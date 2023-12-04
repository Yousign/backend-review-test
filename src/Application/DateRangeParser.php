<?php

namespace App\Application;

final class DateRangeParser
{
    /**
     * @throws \InvalidArgumentException
     *
     * @param string $date
     * @return array|null
     *
     */
    public function parse(string $date): ?array
    {
        $matches = [];

        if (0 === preg_match(
                '/^(?P<year>20[1-9]{2})-(?P<month>\d{2})-((?P<day>\d{2})|\{(?P<days>(?P<day_start>\d{2})\.\.(?P<day_end>\d{2}))\})((?>-)((?P<hour>\d{1,2})|\{(?P<hours>(?P<hour_start>\d{1,2})\.\.(?P<hour_end>\d{2}))\}))?/',
                $date,
                $matches,
                PREG_UNMATCHED_AS_NULL)) {
            throw new \InvalidArgumentException(sprintf('Invalid date %s', $date));
        }
        try {
            $this->checkValues($matches);
        } catch (\InvalidArgumentException $e) {
            throw new \InvalidArgumentException(sprintf('Invalid date %s (%s)', $date, $e->getMessage()));
        }
        return $this->getDateRanges(array_filter($matches,));
    }

    private function checkValues($values): void
    {
        $result = match (true) {
            !empty($values['day_start']) && $values['day_start'] >= $values['day_end'] => 'day start is greater than or equal to day end',
            !empty($values['hour_start']) && $values['hour_start'] >= $values['hour_end'] => 'hour start is greater than or equal to hour end',
            !empty($values['day']) && ($values['day'] > 31),
                !empty($values['hour'][0]) && ($values['hour'][0] > 23),
                !empty($values['day_start']) && ($values['day_start'] > 31 || $values['day_end'] > 31),
                !empty($values['hour_start']) && ($values['hour_start'] > 23 || $values['hour_end'] > 23),
                => 'days or hours out of range',
            default => null,
        };
        if (null !== $result) {
            throw new \InvalidArgumentException($result);
        }
    }

    private function getDateRanges(array $spec)
    {
        $ranges = [];
        $year = $spec['year'];
        $month = $spec['month'];
        $days =  !empty($spec['day_start']) ? range($spec['day_start'], $spec['day_end']) : [$spec['day']];
        if (!empty($spec['hour_start'])) {
            $hours = range($spec['hour_start'], $spec['hour_end']);
        } elseif (!empty($spec['hour'])) {
            $hours = [$spec['hour']];
        } else {
            $hours = range(0, 23);
        }

        foreach ($days as $day) {
            if (checkdate($month, $day, $year)) {
                foreach ($hours as $hour) {
                    $ranges[] = sprintf('%s-%s-%s-%s', $year, $month, $day, $hour);
                }
            }
        }

        return $ranges;
    }
}
