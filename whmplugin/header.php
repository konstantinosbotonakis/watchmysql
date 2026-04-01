<?php
include_once(__DIR__ . '/env.php');
include_once(__DIR__ . '/watchmysql.class.php');

if (defined('WATCHMYSQL_DEV') && WATCHMYSQL_DEV) {
    $baseurl = '';
    $is_whm = false;
    $watchmysql = new watchmysql([
        'mycnf_file'      => __DIR__ . '/dev/.my.cnf',
        'config_file'     => __DIR__ . '/dev/watchmysql.config',
        'users_dir'       => __DIR__ . '/dev/users',
        'packages_dir'    => __DIR__ . '/dev/packages',
        'userlimits_file' => __DIR__ . '/dev/watchmysql.userlimits',
        'pkg_limits_file' => __DIR__ . '/dev/watchmysql.packagelimits',
        'whitelist_file'  => __DIR__ . '/dev/watchmysql.whitelist',
        'history_file'    => __DIR__ . '/dev/watchmysql.history',
        'daemon_binary'   => null,
        'pidof_binary'    => null,
        'sendmail_binary' => null,
    ]);
} else {
    $baseurl = ($_ENV['cp_security_token'] ?? '') . '/cgi/addons/watchmysql';
    $is_whm = true;
    $watchmysql = new watchmysql();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>WatchMySQL</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="<?php echo $baseurl; ?>/assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?php echo $baseurl; ?>/assets/bootstrap/css/bootstrap-icons.min.css" rel="stylesheet">
    <link href="<?php echo $baseurl; ?>/assets/watchmysql/css/watchmysql.css" rel="stylesheet">
</head>
<body class="<?php echo $is_whm ? 'whm-mode' : 'standalone-mode'; ?>">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark <?php echo $is_whm ? 'mb-3' : 'fixed-top'; ?>">
        <div class="<?php echo $is_whm ? 'container-fluid' : 'container'; ?>">
            <a class="navbar-brand" href="<?php echo $baseurl; ?>/index.php">
                <i class="bi bi-database-fill-gear"></i> WatchMySQL
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarMain">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link<?php if (($nav ?? '') == 'index') echo ' active'; ?>" href="<?php echo $baseurl; ?>/index.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link<?php if (($nav ?? '') == 'slowqueries') echo ' active'; ?>" href="<?php echo $baseurl; ?>/slowqueries.php">Slow Queries</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link<?php if (($nav ?? '') == 'status') echo ' active'; ?>" href="<?php echo $baseurl; ?>/status.php">MySQL Status</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link<?php if (($nav ?? '') == 'userlimits') echo ' active'; ?>" href="<?php echo $baseurl; ?>/userlimits.php">User Limits</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link<?php if (($nav ?? '') == 'packagelimits') echo ' active'; ?>" href="<?php echo $baseurl; ?>/packagelimits.php">Package Limits</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link<?php if (($nav ?? '') == 'whitelist') echo ' active'; ?>" href="<?php echo $baseurl; ?>/whitelist.php">Whitelist</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link<?php if (($nav ?? '') == 'history') echo ' active'; ?>" href="<?php echo $baseurl; ?>/history.php">History</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link<?php if (($nav ?? '') == 'globalconfig') echo ' active'; ?>" href="<?php echo $baseurl; ?>/globalconfig.php">Config</a>
                    </li>
                </ul>
                <span class="navbar-text">
                    <small class="text-secondary">v<?php echo $watchmysql->get_version() ?: '?'; ?></small>
                </span>
            </div>
        </div>
    </nav>
    <div class="<?php echo $is_whm ? 'container-fluid' : 'container'; ?>">
