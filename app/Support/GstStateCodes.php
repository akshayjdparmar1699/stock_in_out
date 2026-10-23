<?php

namespace App\Support;

/**
 * India's official GST state/UT codes (the 2-digit prefix that also forms
 * the first two digits of every GSTIN) — a fixed government table, not
 * something that changes, so a static lookup is safe rather than editable
 * data. Used to print "State (code)" on a GST tax invoice and to decide
 * CGST+SGST vs IGST by comparing codes rather than state-name spelling.
 */
class GstStateCodes
{
    private const CODES = [
        'Jammu and Kashmir' => '01',
        'Himachal Pradesh' => '02',
        'Punjab' => '03',
        'Chandigarh' => '04',
        'Uttarakhand' => '05',
        'Haryana' => '06',
        'Delhi' => '07',
        'Rajasthan' => '08',
        'Uttar Pradesh' => '09',
        'Bihar' => '10',
        'Sikkim' => '11',
        'Arunachal Pradesh' => '12',
        'Nagaland' => '13',
        'Manipur' => '14',
        'Mizoram' => '15',
        'Tripura' => '16',
        'Meghalaya' => '17',
        'Assam' => '18',
        'West Bengal' => '19',
        'Jharkhand' => '20',
        'Odisha' => '21',
        'Chhattisgarh' => '22',
        'Madhya Pradesh' => '23',
        'Gujarat' => '24',
        'Daman and Diu' => '25',
        'Dadra and Nagar Haveli and Daman and Diu' => '26',
        'Maharashtra' => '27',
        'Andhra Pradesh (Old)' => '28',
        'Karnataka' => '29',
        'Goa' => '30',
        'Lakshadweep' => '31',
        'Kerala' => '32',
        'Tamil Nadu' => '33',
        'Puducherry' => '34',
        'Andaman and Nicobar Islands' => '35',
        'Telangana' => '36',
        'Andhra Pradesh' => '37',
        'Ladakh' => '38',
    ];

    /**
     * Case/whitespace-insensitive lookup — null when the name isn't a
     * recognised Indian state/UT, so callers can decide how to handle an
     * unset or free-typed value rather than silently showing a wrong code.
     */
    public static function code(?string $state): ?string
    {
        if (! $state) {
            return null;
        }

        $needle = strtolower(trim($state));

        foreach (self::CODES as $name => $code) {
            if (strtolower($name) === $needle) {
                return $code;
            }
        }

        return null;
    }

    /**
     * @return array<string, string> state name => code, for a <select>.
     */
    public static function all(): array
    {
        return self::CODES;
    }
}
