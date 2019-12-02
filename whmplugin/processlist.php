<?PHP 
require_once('watchmysql.class.php');
$watchmysql = new watchmysql();
$process_list = $watchmysql->mysql_process_list();

if(isset($_GET['kill']) && $process_list[$_GET['kill']] && $watchmysql->mysql_kill_id($_GET['kill'])) {
	$killed = $_GET['kill'];
	$process_list = $watchmysql->mysql_process_list(); #refresh process list
}

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

require_once('header.php');
require_once('errors.php');

if(isset($killed)) {
?>
		<div class="ndc_success">Killed MySQL connection id <?=$killed;?></div>
<?
}

?>
		<div class="ndc_table_1">
			<div style="margin: 5px">&raquo; Total MySQL Connections: <?=count($process_list);?></div>
			<table width="100%">
				<tr>
					<th><a href="<?=$_ENV{cp_security_token};?>/watchmysql/processlist.php?sory=id" title="sort by id">Id</a></th>
					<th><a href="<?=$_ENV{cp_security_token};?>/watchmysql/processlist.php?sort=user" title="sort by user">User</a></th>
					<th><a href="<?=$_ENV{cp_security_token};?>/watchmysql/processlist.php?sort=db" title="sort by database">Database</a></th>
					<th><a href="<?=$_ENV{cp_security_token};?>/watchmysql/processlist.php?sort=command" title="sort by command">Command</a></th>
					<th><a href="<?=$_ENV{cp_security_token};?>/watchmysql/processlist.php?sort=time" title="sort by time">Time</a></th>
					<th><a href="<?=$_ENV{cp_security_token};?>/watchmysql/processlist.php?sort=info" title="sort by info">Info</a></th>
					<th>Action</th>
				</tr>
<?PHP
foreach($process_list as $process_line) {
	$alt = $i %2 ? true : false; $i++;
?>
				<tr class="<?=$alt ? 'alt' : '';?>">
					<td><?=$process_line['Id'];?></td>
					<td><?=$process_line['User'];?></td>
					<td><?=$process_line['db'];?></td>
					<td><?=$process_line['Command'];?></td>
					<td><?=$process_line['Time'];?></td>
					<td><?=$process_line['Info'];?></td>
					<td><a href="<?=$_ENV{cp_security_token};?>/watchmysql/processlist.php?kill=<?=$process_line['Id'];?>" title="kill process">kill</a></td>
				</tr>
<?PHP
}
?>
			</table>
		</div>
<?PHP require_once('footer.php'); ?>
