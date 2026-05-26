<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

/**
 * Phase 7 — Deployment readiness tests.
 *
 * Verifies that all configuration, documentation, and tooling
 * required for Supabase + Vercel deployment are present and correct.
 */
class DeploymentReadinessTest extends TestCase
{
    /**
     * 1. .env.example contains all required keys.
     */
    public function test_env_example_contains_required_keys(): void
    {
        $envPath = base_path('.env.example');
        $this->assertFileExists($envPath, '.env.example file must exist');

        $content = file_get_contents($envPath);

        $requiredKeys = [
            'APP_NAME',
            'APP_ENV',
            'APP_KEY',
            'APP_DEBUG',
            'APP_URL',
            'DB_CONNECTION',
            'DB_HOST',
            'DB_PORT',
            'DB_DATABASE',
            'DB_USERNAME',
            'DB_PASSWORD',
            'DB_SSLMODE',
            'SESSION_DRIVER',
            'CACHE_STORE',
            'QUEUE_CONNECTION',
            'MIDTRANS_MERCHANT_ID',
            'MIDTRANS_CLIENT_KEY',
            'MIDTRANS_SERVER_KEY',
            'MIDTRANS_IS_PRODUCTION',
            'ADMIN_SEED_EMAIL',
            'ADMIN_SEED_PASSWORD',
            'VITE_APP_NAME',
        ];

        foreach ($requiredKeys as $key) {
            $this->assertStringContainsString(
                $key,
                $content,
                ".env.example must contain the key: {$key}"
            );
        }
    }

    /**
     * 2. config/services.php has Midtrans config keys.
     */
    public function test_services_config_has_midtrans_keys(): void
    {
        $midtrans = config('services.midtrans');

        $this->assertIsArray($midtrans, 'services.midtrans must be an array');

        $requiredKeys = [
            'merchant_id',
            'client_key',
            'server_key',
            'is_production',
            'is_sanitized',
            'is_3ds',
        ];

        foreach ($requiredKeys as $key) {
            $this->assertArrayHasKey(
                $key,
                $midtrans,
                "config('services.midtrans') must have key: {$key}"
            );
        }
    }

    /**
     * 3. manake:health command can run in test environment.
     */
    public function test_manake_health_command_runs(): void
    {
        // The command should exit without throwing an exception.
        // In test env it may report FAIL/WARN for missing env vars — that is expected.
        $exitCode = Artisan::call('manake:health');

        // Exit code 0 = SUCCESS, 1 = FAILURE — both are valid runtime responses
        $this->assertContains(
            $exitCode,
            [0, 1],
            'manake:health command must exit with code 0 or 1'
        );
    }

    /**
     * 4. DEPLOYMENT.md exists and warns against migrate:fresh.
     */
    public function test_deployment_docs_exist_and_warn_against_migrate_fresh(): void
    {
        $deployPath = base_path('DEPLOYMENT.md');
        $this->assertFileExists($deployPath, 'DEPLOYMENT.md must exist');

        $content = file_get_contents($deployPath);

        $this->assertStringContainsString(
            'migrate:fresh',
            $content,
            'DEPLOYMENT.md must mention migrate:fresh as a warning'
        );

        $this->assertStringContainsString(
            'NEVER',
            $content,
            'DEPLOYMENT.md must contain NEVER warning about destructive commands'
        );

        $this->assertStringContainsString(
            'Supabase',
            $content,
            'DEPLOYMENT.md must reference Supabase'
        );

        $this->assertStringContainsString(
            'midtrans/callback',
            $content,
            'DEPLOYMENT.md must mention the Midtrans callback URL'
        );
    }

    /**
     * 5. vercel.json exists.
     */
    public function test_vercel_json_exists(): void
    {
        $vercelPath = base_path('vercel.json');
        $this->assertFileExists($vercelPath, 'vercel.json must exist');

        $json = json_decode(file_get_contents($vercelPath), true);
        $this->assertNotNull($json, 'vercel.json must be valid JSON');

        $this->assertArrayHasKey('routes', $json, 'vercel.json must define routes');
        $this->assertArrayHasKey('functions', $json, 'vercel.json must define functions');
    }

    /**
     * 6. api/index.php entry point exists.
     */
    public function test_vercel_api_entry_point_exists(): void
    {
        $apiPath = base_path('api/index.php');
        $this->assertFileExists($apiPath, 'api/index.php must exist for Vercel PHP runtime');

        $content = file_get_contents($apiPath);
        $this->assertStringContainsString(
            'public/index.php',
            $content,
            'api/index.php must delegate to public/index.php'
        );
    }

    /**
     * 7. DB_SSLMODE is configured in pgsql connection.
     */
    public function test_pgsql_sslmode_is_configured(): void
    {
        $sslmode = config('database.connections.pgsql.sslmode');
        $this->assertNotNull($sslmode, 'pgsql connection must have sslmode configured');
    }
}
