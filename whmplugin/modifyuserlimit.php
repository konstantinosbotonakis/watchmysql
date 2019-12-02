<?PHP 
$nav='userlimits';
include_once('header.php');

// User Limits
$user_limits = $watchmysql->get_user_limits();

// Default set limit.
if(isset($_REQUEST['user']) && !isset($_REQUEST['limit'])) $_REQUEST['limit'] = $user_limits[$_REQUEST['user']];

// Save
if(isset($_POST['action']) && $_POST['action'] == 'save') {
	if(is_numeric($_REQUEST['limit'])) {
		$user_limits[$_REQUEST['user']] = $_REQUEST['limit'];
		$result = $watchmysql->save_user_limits($user_limits);
		if($result === true) {
			$success = 'Successfully modified ' . $_REQUEST['user'] . ' with a limit of ' . $_REQUEST['limit'];
		} else {
			$error = 'Error modifying ' . $_POST['user'] . ' with a limit of ' . $_POST['limit'];
		}
	} else {
		$error = 'Limit must be a numeric value';
	}
}
?>

			<div class="page-header">
				<h1>User Limits <small>Modify User Limit</small>
			</div>
			<p>Setting a user limit for a user will bypass the existing package and global limits that are already set in place.  This means that this user will be bound by the limits that you set here!</p>
			
			<?php include('alerts.php') ?>

			<form action="<?php echo $baseurl; ?>/modifyuserlimit.php" method="post" class="well well-sm form-horizontal">
				<input type="hidden" name="action" value="save">
				<div class="row">
					<div class="col-md-5">
						<label class="control-label col-md-5" id="user">Username</label>
						<div class="col-md-7">
							<input type="text" name="user" value="<?php echo $_REQUEST['user'];?>" class="form-control readonly" readonly="readonly">
						</div>
					</div>
					<div class="col-md-5">
						<label class="control-label col-md-5" id="limit">Connection Limit</label>
						<div class="col-md-7">
							<input type="text" name="limit" value="<?php echo $_REQUEST['limit'];?>" class="form-control">
						</div>
					</div>
					<div class="col-md-2">
						<button type="submit" class="btn btn-primary">Save Limit</button>
					</div>
				</div>
			</form>

<?php include_once('footer.php'); ?>
