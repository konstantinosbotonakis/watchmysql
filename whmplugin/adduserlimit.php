<?PHP 
$nav='userlimits';
include_once('header.php');

// cPanel user list
$users = $watchmysql->get_user_list();

// WatchMySQL global config
$global_config = $watchmysql->get_global_config();

if(isset($_POST['action']) && $_POST['action'] == 'save') {
	$result = $watchmysql->add_user_limit($_POST['user'],$_POST['limit']);
	if($result === true) {
		$success = 'Successfully added username ' . $_POST['user'] . ' with a limit of ' . $_POST['limit'];
	} else {
		$error = 'Error adding username ' . $_POST['user'] . ' with a limit of ' . $_POST['limit'];
	}
}
?>

			<div class="page-header">
				<h1>User Limits <small>Add New User Limit</small>
			</div>
			<p>Adding a user limit for a user will bypass the existing package and global limits that are already set in place.  This means that this user will be bound by the limits that you set here!</p>
			
			<?php include('alerts.php') ?>

			<form action="<?php echo $baseurl; ?>/adduserlimit.php" method="post" class="well well-sm form-horizontal">
				<input type="hidden" name="action" value="save">
				<div class="row">
					<div class="col-md-5">
						<label class="control-label col-md-5" id="user">Username</label>
						<div class="col-md-7">
							<select name="user" class="form-control">
								<option value="">Select Username</option>
								<?php foreach($users as $user => $details) { ?>
								<option value="<?=$user;?>"><?=$user;?></option>
								<?php } ?>
							</select>
						</div>
					</div>
					<div class="col-md-5">
						<label class="control-label col-md-5" id="limit">Connection Limit</label>
						<div class="col-md-7">
							<input type="text" name="limit" value="<?php echo $global_config['connection_limit'];?>" class="form-control">
						</div>
					</div>
					<div class="col-md-2">
						<button type="submit" class="btn btn-primary">Save Limit</button>
					</div>
				</div>
			</form>

<?php include_once('footer.php'); ?>
