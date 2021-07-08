<?php

namespace App\Utils;

use Symfony\Component\Console\Exception\RuntimeException;
use DateTimeImmutable;
use DateTime;

trait DateTimeImmutableTrait
{
    /**
     * Generate random DateTimeImmutable object and related date string,
     * Between a stat date and an end date.
     *
     * @param string $start
     * @param string $end
     * @return array{dateObject: DateTimeImmutable, dateString: string}
     */
    private function generateRandomDateBetweenRange(string $start, string $end): array
    {
        $startDate = DateTime::createFromFormat('d-m-Y', $start);
        $endDate = DateTime::createFromFormat('d-m-Y', $end);

        if (!$startDate || !$endDate) {
            throw new RuntimeException('Parameters invalid, expected date with format d-m-Y', 400);
        }
        $randomTimestamp = mt_rand($startDate->getTimestamp(), $endDate->getTimestamp());
        $dateTimeImmutable = (new DateTimeImmutable())->setTimestamp($randomTimestamp);
        return [
            'dateObject' => $dateTimeImmutable,
            'dateString' => $dateTimeImmutable->format('d-m-Y')
        ];
    }
}