<?PHP 
$nav = 'index';
include_once('header.php');

$watchmysqlpid = $watchmysql->get_watchmysql_pid();
if($watchmysqlpid === false) {
	$watchmysqlpid = $watchmysql->start_watchmysql();
	if($watchmysqlpid === false) {
		$error = 'Failed to start WatchMySQL daemon, check /var/log/watchmysql.log for further details';
	} else {
		$success = 'Successfully started WatchMySQL daemon! (PID ' . $watchmysqlpid . ')';
	}
} else {
	$success = 'WatchMySQL daemon is already running on PID ' . $watchmysqlpid;
}

?>
			<div class="page-header">
				<h1>Start WatchMySQL Daemon</h1>
			</div>
			<?php include('alerts.php'); ?>


<?php include_once('footer.php'); ?>
