<?php
$nav = 'index';
include_once('header.php');

$watchmysqlpid = $watchmysql->get_watchmysql_pid();
if ($watchmysqlpid === false) {
    $watchmysqlpid = $watchmysql->start_watchmysql();
    if ($watchmysqlpid === false) {
        $error = 'Failed to start WatchMySQL daemon. Check /var/log/watchmysql.log for details.';
    } else {
        $success = 'WatchMySQL daemon started successfully (PID ' . $watchmysqlpid . ')';
    }
} else {
    $success = 'WatchMySQL daemon is already running (PID ' . $watchmysqlpid . ')';
}
?>
        <h4 class="mb-3 mt-2">Start WatchMySQL Daemon</h4>
        <?php include('alerts.php'); ?>

        <a href="<?php echo $baseurl; ?>/index.php" class="btn btn-primary"><i class="bi bi-arrow-left"></i> Back to Dashboard</a>

<?php include_once('footer.php'); ?>
