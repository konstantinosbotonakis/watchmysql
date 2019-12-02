<?PHP 
$nav = 'userlimits';
include_once('header.php');

$users = $watchmysql->get_user_list();
$userlimits = $watchmysql->get_user_limits();

// Remove
if(isset($_GET['removeuser'])) {
	if(isset($userlimits[$_GET['removeuser']])) {
		$result = $watchmysql->remove_user_limit($_GET['removeuser']);
		if($result) {
			$success = 'Successfully removed user limit for username ' . $_GET['removeuser'];
		} else {
			$error = 'Failed to remove user limit for username ' . $_GET['removeuser'];
		}
		// refresh limits
		$userlimits = $watchmysql->get_user_limits();
	} else {
		$error = 'Username ' . $_GET['removeuser'] . ' does not have a limit set';
	}
}


?>
			<div class="page-header">
				<h1>User Limits <small>Set per user limits</small></h1>
			</div>
			<p>Here you will find a list of custom limits set per user.  These limits superseed the set <a href="<?php echo $baseurl;?>/packagelimits.php" title="view package limits">package</a> and <a href="<?php echo $baseurl;?>/globalconfig.php" title="global config">global limits</a>.  A user with no limit listed here will then be bound by the package limit, if one exits, and then by the global limit.</p>
			<?php include('alerts.php'); ?>
			<p class="text-right">
				<a href="<?php echo $baseurl;?>/adduserlimit.php" title="Add user limit">Add New User Limit</a>
			</p>
			<div class="panel panel-default">
				<table class="table table-striped">
					<tr>
						<th>Username</th>
						<th>Package</th>
						<th>Connection Limit</th>
						<th>Actions</th>
					</tr>
					<?php foreach($userlimits as $user => $limit) { ?>
					<tr>
						<td><?php echo $user;?></td>
						<td><?php echo $users[$user]['PLAN'];?></td>
						<td><?php echo $limit;?></td>
						<td>
							<a href="<?php echo $baseurl;?>/userlimits.php?removeuser=<?php echo $user;?>" title="remove user limit">Remove</a>
							<span class="text-muted">&nbsp;|&nbsp;</span>
							<a href="<?php echo $baseurl;?>/modifyuserlimit.php?user=<?php echo $user;?>" title="modify user limit">Modify</a>
						</td>
					</tr>
					<?php } ?>
				</table>
			</div>

<?php include_once('footer.php'); ?>
