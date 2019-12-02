<?PHP 
$nav = 'index';
include_once('header.php');
$process_list = $watchmysql->mysql_process_list();

# Kill MySQL Process
if(isset($_GET['kill']) && $process_list[$_GET['kill']]) {
	$result = $watchmysql->mysql_kill_id($_GET['kill']);
	if($result) {
		$success = 'Successfully killed MySQL connection id ' . $_GET['kill'];
	} else {
		$error = 'Failed to kill MySQL connection id ' . $_GET['kill'];
	}
	$process_list = $watchmysql->mysql_process_list(); #refresh process list
}

# Sorting Process List
if(isset($_GET['sort'])) {
	if($_GET['sort'] == 'id') {
		function sort_by_id($a,$b) { return ($a['Id'] < $b['Id']) ? -1 : 1; }
		usort($process_list, "sort_by_id");
	}elseif($_GET['sort'] == 'user') {
		function sort_by_user($a,$b) { if($a['User'] == $b['User']) return 0; return strcmp($a['User'], $b['User']); }
		usort($process_list, "sort_by_user");
	}elseif($_GET['sort'] == 'db') {
		function sort_by_db($a,$b) { if($a['db'] == $b['db']) return 0; return strcmp($a['db'], $b['db']); }
		usort($process_list, "sort_by_db");
	}elseif($_GET['sort'] == 'command') {
		function sort_by_command($a,$b) { if($a['Command'] == $b['Command']) return 0; return strcmp($a['Command'], $b['Command']); }
		usort($process_list, "sort_by_command");
	}elseif($_GET['sort'] == 'time') {
		function sort_by_time($a,$b) { if($a['Time'] == $b['Time']) return 0; return ($a['Time'] < $b['Time']) ? -1 : 1; }
		usort($process_list, "sort_by_time");
	}elseif($_GET['sort'] == 'info') {
		function sort_by_info($a,$b) { if($a['Info'] == $b['Info']) return 0; return strcmp($a['Info'], $b['Info']); }
		usort($process_list, "sort_by_info");
	}
}

?>
			<div class="page-header">
				<h1>MySQL Process List <small>There are currently <?=is_array($process_list) ? count($process_list) : 'n/a';?> MySQL connections</small></h1>
			</div>

			<?php include('alerts.php'); ?>
		
			<div class="panel panel-default">
				<table class="table table-striped">
					<tr>
						<th><a href="<?php echo $baseurl; ?>/index.php?sory=id" title="sort by id">Id</a></th>
						<th><a href="<?php echo $baseurl; ?>/index.php?sort=user" title="sort by user">User</a></th>
						<th><a href="<?php echo $baseurl; ?>/index.php?sort=db" title="sort by database">Database</a></th>
						<th><a href="<?php echo $baseurl; ?>/index.php?sort=command" title="sort by command">Command</a></th>
						<th><a href="<?php echo $baseurl; ?>/index.php?sort=time" title="sort by time">Time</a></th>
						<th><a href="<?php echo $baseurl; ?>/index.php?sort=info" title="sort by info">Info</a></th>
						<th>Action</th>
					</tr>
					<?php
					if(is_array($process_list)) {
						foreach($process_list as $process_line) {
					?>
					<tr>
						<td><?php echo $process_line['Id']; ?></td>
						<td><?php echo $process_line['User']; ?></td>
						<td><?php echo $process_line['db']; ?></td>
						<td><?php echo $process_line['Command']; ?></td>
						<td><?php echo $process_line['Time']; ?></td>
						<td><?php echo $process_line['Info']; ?></td>
						<td><a href="<?php echo $baseurl; ?>/index.php?kill=<?=$process_line['Id'];?>" title="kill process">kill</a></td>
					</tr>
					<?php
						}
					}
					?>
				</table>
			</div>

<?php include_once('footer.php'); ?>
