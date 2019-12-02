<?PHP 
$nav='packagelimits';
include_once('header.php');

// cPanel Package List
$packages = $watchmysql->get_package_list();

// WatchMySQL global config
$global_config = $watchmysql->get_global_config();

if(isset($_POST['action']) && $_POST['action'] == 'save') {
	$result = $watchmysql->add_package_limit($_POST['package'],$_POST['limit']);
	if($result === true) {
		$success = 'Successfully added package ' . $_POST['package'] . ' with a limit of ' . $_POST['limit'];
	} else {
		$error = 'Error adding package ' . $_POST['package'] . ' with a limit of ' . $_POST['limit'];
	}
}
?>

			<div class="page-header">
				<h1>Package Limits <small>Add new package limit</small>
			</div>
			<p>Adding a package limit for a user will override the existing global limits that are set.  This means users that use this package will be bound by the limits that you set here!</p>
			
			<?php include('alerts.php') ?>

			<form action="<?php echo $baseurl; ?>/addpackagelimit.php" method="post" class="well well-sm form-horizontal">
				<input type="hidden" name="action" value="save">
				<div class="row">
					<div class="col-md-5">
						<label class="control-label col-md-5" id="user">Package</label>
						<div class="col-md-7">
							<select name="package" class="form-control">
								<option value="">Select Package</option>
								<?php foreach($packages as $package => $details) { ?>
								<option value="<?php echo $package;?>"><?php echo $package;?></option>
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
