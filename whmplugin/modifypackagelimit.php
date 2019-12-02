<?PHP 
$nav='packagelimits';
include_once('header.php');

// cPanel Package List
$package_limits = $watchmysql->get_package_limits();

// Set default set limit
if(isset($_REQUEST['package']) && !isset($_REQUEST['limit'])) $_REQUEST['limit'] = $package_limits[$_REQUEST['package']];

// Save Modifyed Limits
if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'save') {
	if(is_numeric($_REQUEST['limit'])) {
		$package_limits[$_REQUEST['package']] = $_REQUEST['limit'];
		$result = $watchmysql->save_package_limits($package_limits);
		if($result === true) {
			$success = 'Successfully modified ' . $_REQUEST['package'] . ' with a limit of ' . $_REQUEST['limit'];
		} else {
			$error = 'Error modifying ' . $_REQUEST['package'] . ' with a limit of ' . $_REQUEST['limit'];
		}
	} else {
		$error = 'Limit value must be numeric';
	}
}
?>

			<div class="page-header">
				<h1>Package Limits <small>Modify new package limit</small>
			</div>
			<p>Setting a limit for a package will bypass the existing global limits that are already set in place.  This means that users with this package will be bound by the limits that you set here!</p>
			
			<?php include('alerts.php') ?>

			<form action="<?php echo $baseurl; ?>/modifypackagelimit.php" method="post" class="well well-sm form-horizontal">
				<input type="hidden" name="action" value="save">
				<div class="row">
					<div class="col-md-5">
						<label class="control-label col-md-5" id="user">Package</label>
						<div class="col-md-7">
							<input type="text" name="package" value="<?php echo $_REQUEST['package']; ?>" class="readonly form-control" readonly="readonly">
						</div>
					</div>
					<div class="col-md-5">
						<label class="control-label col-md-5" id="limit">Connection Limit</label>
						<div class="col-md-7">
							<input type="text" name="limit" value="<?php echo $_REQUEST['limit']; ?>" class="form-control">
						</div>
					</div>
					<div class="col-md-2">
						<button type="submit" class="btn btn-primary">Save Limit</button>
					</div>
				</div>
			</form>

<?php include_once('footer.php'); ?>
