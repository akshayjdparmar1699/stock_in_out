<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

/**
 * Postgres and MySQL disagree on how to do a case-insensitive LIKE: Postgres
 * needs the ILIKE operator (plain LIKE is case-sensitive there), while MySQL
 * has no ILIKE at all — sending it as a literal operator string silently
 * breaks every search field across the app on MySQL, since Eloquent treats
 * an operator it doesn't recognize as the comparison value instead, not an
 * error. MySQL's own LIKE is already case-insensitive under its default
 * collation, so the fix is just picking the right word per connection.
 */
class Sql
{
    public static function caseInsensitiveLike(): string
    {
        return DB::connection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';
    }
}
