<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * One-off: copies every business table from the original Postgres database
 * (connection "pgsql_source") into whatever the app's default connection is
 * now (MySQL, on the Hostinger deployment this was written for). Run once,
 * against a freshly-migrated, otherwise-empty destination database — each
 * destination table is truncated first so the command is safe to re-run if
 * it fails partway through, but it is NOT meant to be run against a
 * database that already has its own real data, since that data would be
 * wiped by the truncate.
 *
 * Tables are listed oldest-ancestor-first so foreign keys would resolve
 * correctly even without the FK-check toggle below, which exists as a
 * belt-and-braces safety net rather than the primary ordering mechanism.
 * Session/cache/queue tables and the per-device push_subscriptions table
 * are deliberately excluded — none of them are real business data worth
 * carrying across.
 */
#[Signature('app:copy-data-to-mysql {--chunk=500}')]
#[Description('Copy all business data from the pgsql_source connection into the default (MySQL) connection')]
class CopyDataToMysql extends Command
{
    private const TABLES = [
        'companies',
        'branches',
        'users',
        'items',
        'item_stocks',
        'customers',
        'branch_customer',
        'suppliers',
        'staff_members',
        'branch_staff_member',
        'invoices',
        'invoice_items',
        'invoice_payments',
        'purchases',
        'purchase_items',
        'purchase_payments',
        'stock_movements',
        'stock_batches',
        'stock_batch_allocations',
        'expenses',
    ];

    public function handle(): int
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            $this->error('Default connection is not MySQL — refusing to run, this command is only meant for the MySQL destination.');

            return self::FAILURE;
        }

        $chunkSize = (int) $this->option('chunk');

        DB::connection()->statement('SET FOREIGN_KEY_CHECKS=0');

        foreach (self::TABLES as $table) {
            $this->copyTable($table, $chunkSize);
        }

        DB::connection()->statement('SET FOREIGN_KEY_CHECKS=1');

        $this->info('Done.');

        return self::SUCCESS;
    }

    private function copyTable(string $table, int $chunkSize): void
    {
        if (! Schema::connection('pgsql_source')->hasTable($table)) {
            $this->warn("Skipping {$table}: not found on the source database.");

            return;
        }

        $total = DB::connection('pgsql_source')->table($table)->count();
        $this->line("{$table}: {$total} row(s) on the source.");

        if ($total === 0) {
            return;
        }

        DB::connection()->table($table)->truncate();

        $bar = $this->output->createProgressBar($total);
        $copied = 0;

        DB::connection('pgsql_source')->table($table)->orderBy('id')
            ->chunk($chunkSize, function ($rows) use ($table, &$copied, $bar) {
                $prepared = $rows->map(fn ($row) => $this->normalizeRow((array) $row))->all();

                DB::connection()->table($table)->insert($prepared);

                $copied += count($prepared);
                $bar->advance(count($prepared));
            });

        $bar->finish();
        $this->newLine();

        $this->syncAutoIncrement($table);

        if ($copied !== $total) {
            $this->warn("{$table}: copied {$copied} of {$total} — counts don't match, worth a look.");
        }
    }

    /**
     * Postgres' PDO driver hands boolean columns back as the single
     * characters 't'/'f' rather than real PHP booleans — inserted as-is,
     * MySQL would coerce both of those non-numeric strings to 0 for a
     * tinyint column, silently turning every true value false. Converts
     * only those exact two values, so real text data ('t' as part of a
     * longer string, numbers, nulls) is left untouched.
     */
    private function normalizeRow(array $row): array
    {
        foreach ($row as $key => $value) {
            if ($value === 't') {
                $row[$key] = 1;
            } elseif ($value === 'f') {
                $row[$key] = 0;
            }
        }

        return $row;
    }

    /**
     * Explicit ids were just inserted (preserving every foreign key
     * relationship as-is), which on MySQL leaves the table's own
     * auto-increment counter still at 1 — the next *new* row created by
     * the app would collide with an id that already exists. Moves it past
     * whatever the highest copied id was.
     */
    private function syncAutoIncrement(string $table): void
    {
        $maxId = DB::connection()->table($table)->max('id');

        if ($maxId) {
            DB::connection()->statement("ALTER TABLE `{$table}` AUTO_INCREMENT = ".((int) $maxId + 1));
        }
    }
}
