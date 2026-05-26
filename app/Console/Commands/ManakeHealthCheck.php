<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ManakeHealthCheck extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'manake:health';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run a pre-deployment health check for Manake V2';

    /**
     * Required database tables that must exist.
     */
    private array $requiredTables = [
        'users',
        'categories',
        'equipments',
        'cart_items',
        'orders',
        'order_items',
        'order_status_logs',
        'payments',
        'sessions',
        'cache',
        'jobs',
    ];

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->newLine();
        $this->line('╔══════════════════════════════════════════════╗');
        $this->line('║      Manake V2 — Deployment Health Check     ║');
        $this->line('╚══════════════════════════════════════════════╝');
        $this->newLine();

        $allPassed = true;

        // 1. APP_KEY
        $allPassed = $this->check(
            'APP_KEY is set',
            fn () => ! empty(config('app.key')),
        ) && $allPassed;

        // 2. APP_URL
        $allPassed = $this->check(
            'APP_URL is set',
            fn () => ! empty(config('app.url')) && config('app.url') !== 'http://localhost',
        ) && $allPassed;

        // 3. APP_DEBUG is false in production
        $isProduction = config('app.env') === 'production';
        $allPassed = $this->check(
            'APP_DEBUG is false (production safe)',
            fn () => ! config('app.debug'),
            warn: ! $isProduction, // only warn in non-production
        ) && $allPassed;

        // 4. Database connection
        $allPassed = $this->check(
            'Database connection works',
            function () {
                DB::connection()->getPdo();
                return true;
            },
        ) && $allPassed;

        // 5. Required tables exist
        foreach ($this->requiredTables as $table) {
            $allPassed = $this->check(
                "Table [{$table}] exists",
                fn () => Schema::hasTable($table),
            ) && $allPassed;
        }

        // 6. Categories count
        $allPassed = $this->check(
            'At least 1 category seeded',
            fn () => DB::table('categories')->count() > 0,
            warn: true, // warning, not error — may be empty on fresh deploy
        ) && $allPassed;

        // 7. Equipments count
        $allPassed = $this->check(
            'At least 1 equipment seeded',
            fn () => DB::table('equipments')->count() > 0,
            warn: true,
        ) && $allPassed;

        // 8. Admin user exists
        $allPassed = $this->check(
            'Admin user exists (role = admin or super_admin)',
            fn () => DB::table('users')
                ->whereIn('role', ['admin', 'super_admin'])
                ->exists(),
            warn: true,
        ) && $allPassed;

        // 9. Midtrans config (only presence check — never log values)
        $allPassed = $this->check(
            'MIDTRANS_SERVER_KEY is set',
            fn () => ! empty(config('services.midtrans.server_key')),
        ) && $allPassed;

        $allPassed = $this->check(
            'MIDTRANS_CLIENT_KEY is set',
            fn () => ! empty(config('services.midtrans.client_key')),
        ) && $allPassed;

        // 10. Storage/logs writable
        $allPassed = $this->check(
            'storage/logs directory is writable',
            fn () => is_writable(storage_path('logs')),
            warn: true, // Vercel has ephemeral FS — this may warn
        ) && $allPassed;

        // 11. DB_SSLMODE for Supabase
        $this->check(
            'DB_SSLMODE is "require" (Supabase safe)',
            fn () => config('database.connections.pgsql.sslmode') === 'require',
            warn: true,
        );

        // 12. DB_SCHEMA is set (schema isolation check)
        $configuredSchema = config('database.connections.pgsql.search_path', 'public');
        $allPassed = $this->check(
            'DB_SCHEMA is set (not using default)',
            fn () => env('DB_SCHEMA') !== null && env('DB_SCHEMA') !== '',
            warn: true,
        ) && $allPassed;

        // 13. Warn if DB_SCHEMA=public in production (collision risk with old Manake data)
        if ($isProduction && $configuredSchema === 'public') {
            $this->line('  <fg=yellow>  ⚠ WARN</>  DB_SCHEMA=public in production — risk of collision with old Manake tables.');
            $this->line('         <fg=yellow>Recommendation: Set DB_SCHEMA=manake_v2 and run CREATE SCHEMA IF NOT EXISTS manake_v2;</>');
        } else {
            $schemaLabel = $configuredSchema;
            $this->line("  <fg=green>  ✓ PASS</>  Active PostgreSQL search_path: [{$schemaLabel}]");
        }

        // 14. Verify current PostgreSQL search_path is active on live connection
        $this->check(
            'PostgreSQL search_path is readable from connection',
            function () use ($configuredSchema) {
                $result = DB::selectOne('SHOW search_path');
                return $result !== null;
            },
            warn: true, // non-fatal — only works when DB is connected
        );

        // Summary
        $this->newLine();
        if ($allPassed) {
            $this->line('  <fg=green;options=bold>✓ All critical checks passed. Safe to deploy.</>');
        } else {
            $this->line('  <fg=red;options=bold>✗ Some checks failed. Review above before deploying.</>');
        }
        $this->newLine();

        return $allPassed ? self::SUCCESS : self::FAILURE;
    }

    /**
     * Run a single check and display result.
     *
     * @param  string    $label    Human-readable check label
     * @param  callable  $test     Returns true = pass, false/exception = fail
     * @param  bool      $warn     If true, a failure is a WARNING not an ERROR
     * @return bool                Whether the check passed
     */
    private function check(string $label, callable $test, bool $warn = false): bool
    {
        try {
            $passed = $test();
        } catch (\Throwable $e) {
            $passed = false;
        }

        $status  = $passed
            ? '<fg=green>  ✓ PASS</>'
            : ($warn ? '<fg=yellow>  ⚠ WARN</>' : '<fg=red>  ✗ FAIL</>');

        $this->line("  {$status}  {$label}");

        // Only count as "failed" if it's not a warning
        return $passed || $warn;
    }
}
