<?php

/**
 * Vercel PHP Runtime Entry Point for Laravel 12
 *
 * This file is the bridge between Vercel's serverless PHP runtime
 * and Laravel's public/index.php entry point.
 *
 * Vercel routes all requests here (see vercel.json routes).
 * We then set up paths so Laravel can find its components correctly.
 */

// Resolve the project root (one level above api/)
$projectRoot = dirname(__DIR__);

// Fix: point PHP's working directory and document root to the project root
// so all Laravel path helpers (base_path, storage_path etc.) resolve correctly
chdir($projectRoot);

// Override _SERVER superglobals that Laravel uses for path resolution
$_SERVER['DOCUMENT_ROOT'] = $projectRoot . '/public';
$_SERVER['SCRIPT_FILENAME'] = $projectRoot . '/public/index.php';
$_SERVER['SCRIPT_NAME'] = '/index.php';

// Fix REQUEST_URI: strip the /api prefix that Vercel adds to function paths
// When vercel routes /some/path → /api/index.php the REQUEST_URI stays /some/path
// No stripping needed here; Vercel preserves the original URI.

// Delegate to Laravel's standard public/index.php
require $projectRoot . '/public/index.php';
