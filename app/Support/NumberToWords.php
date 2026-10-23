<?php

namespace App\Support;

/**
 * Spells out a whole-rupee amount for a tax invoice's "Amount in words"
 * line (e.g. 9700 -> "Nine Thousand Seven Hundred"), using the plain
 * English thousand/hundred grouping a GST tax invoice conventionally uses
 * — not the Indian lakh/crore grouping used elsewhere in this app's UI.
 */
class NumberToWords
{
    private const ONES = [
        '', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine',
        'Ten', 'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen',
        'Seventeen', 'Eighteen', 'Nineteen',
    ];

    private const TENS = [
        '', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety',
    ];

    public static function convert(int $number): string
    {
        if ($number === 0) {
            return 'Zero';
        }

        if ($number < 0) {
            return 'Minus '.self::convert(-$number);
        }

        $parts = [];

        if ($number >= 1_000_000_000) {
            $parts[] = self::convertBelowThousand(intdiv($number, 1_000_000_000)).' Billion';
            $number %= 1_000_000_000;
        }
        if ($number >= 1_000_000) {
            $parts[] = self::convertBelowThousand(intdiv($number, 1_000_000)).' Million';
            $number %= 1_000_000;
        }
        if ($number >= 1_000) {
            $parts[] = self::convertBelowThousand(intdiv($number, 1_000)).' Thousand';
            $number %= 1_000;
        }
        if ($number > 0) {
            $parts[] = self::convertBelowThousand($number);
        }

        return implode(' ', $parts);
    }

    private static function convertBelowThousand(int $number): string
    {
        $words = [];

        if ($number >= 100) {
            $words[] = self::ONES[intdiv($number, 100)].' Hundred';
            $number %= 100;
        }

        if ($number >= 20) {
            $tens = self::TENS[intdiv($number, 10)];
            $ones = $number % 10;
            $words[] = $ones > 0 ? $tens.'-'.self::ONES[$ones] : $tens;
        } elseif ($number > 0) {
            $words[] = self::ONES[$number];
        }

        return implode(' ', $words);
    }
}
