<?php
// Dev mode detection: set WATCHMYSQL_DEV=1 env var or create a DEV_MODE file
$is_dev = getenv('WATCHMYSQL_DEV') === '1' || file_exists(__DIR__ . '/DEV_MODE');

if ($is_dev) {
    define('WATCHMYSQL_DEV', true);
    $_ENV['REMOTE_USER'] = 'root';
    $_ENV['cp_security_token'] = '';
} else {
    define('WATCHMYSQL_DEV', false);
}
