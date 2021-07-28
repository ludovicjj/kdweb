<?php


namespace App\Service;


class PasswordGenerator
{
    public function generatePassword(int $length = 12): string
    {
        $uppers = $this->generateCharactersWithCharCodeRange([65, 90]);
        $lowers = $this->generateCharactersWithCharCodeRange([97, 122]);
        $numbers = $this->generateCharactersWithCharCodeRange([48, 57]);
        $specials = $this->generateCharactersWithCharCodeRange([33, 47, 58, 64, 91, 96, 123, 126]);

        $allCharacters = array_merge($uppers, $lowers, $numbers, $specials);

        if (!shuffle($allCharacters)) {
            throw new \LogicException("Generate random password failed !");
        }

        return implode("", array_slice($allCharacters, 0, $length));
    }

    /**
     * @param array<int> $range
     * @return array<string|int>
     */
    private function generateCharactersWithCharCodeRange(array $range): array
    {
        if (count($range) === 2) {
            return $this->transformAsciiCodeToCharactersWithRange($range);
        } else {
            return array_merge(...array_map(function ($range) {
                return $this->transformAsciiCodeToCharactersWithRange($range);
            }, array_chunk($range, 2)));
        }
    }

    /**
     * @param array<int> $range
     * @return array<string|int>
     */
    private function transformAsciiCodeToCharactersWithRange(array $range): array
    {
        return range(chr($range[0]), chr($range[1]));
    }
}