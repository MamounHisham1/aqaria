<?php

/**
 * Test bootstrap.
 *
 * PHPUnit injects <env> directives (phpunit.xml) via the process environment.
 * On hosts where `variables_order` omits `E` (the default `GPCS`), `$_ENV` is
 * never auto-populated, so those directives never reach Laravel's Dotenv
 * loader through the EnvConstAdapter. Worse, an `APP_ENV` exported in the
 * parent shell lands in `$_SERVER`/`getenv()` (e.g. "local"), and Dotenv's
 * default reader checks `$_SERVER` first — so it wins, making
 * `Application::runningUnitTests()` return false. That disables the unit-test
 * short-circuit in `PreventRequestForgery`, so CSRF/auth are enforced against
 * the in-memory database and the suite fails with ~30 spurious 419s.
 *
 * Setting these via putenv() AND directly in $_SERVER/$_ENV here — before the
 * framework boots — makes the immutable Dotenv repository treat them as
 * externally defined and honour the test values regardless of host config.
 */

foreach ([
    'APP_ENV' => 'testing',
    'SESSION_DRIVER' => 'array',
    'CACHE_STORE' => 'array',
    'QUEUE_CONNECTION' => 'sync',
    'MAIL_MAILER' => 'array',
    'BCRYPT_ROUNDS' => '4',
] as $key => $value) {
    \putenv("{$key}={$value}");
    $_SERVER[$key] = $value;
    $_ENV[$key] = $value;
}

require __DIR__.'/../vendor/autoload.php';
