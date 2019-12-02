<?PHP 
$nav = 'packagelimits';
include_once('header.php');

$packagelimits = $watchmysql->get_package_limits();

// Remove
if(isset($_GET['removepackage'])) {
	if(isset($packagelimits[$_GET['removepackage']])) {
		$result = $watchmysql->remove_package_limit($_GET['removepackage']);
		if($result) {
			$success = 'Successfully removed package limit for ' . $_GET['removepackage'];
		} else {
			$error = 'Failed to remove package limit for ' . $_GET['removepackage'];
		}
		// refresh limits
		$packagelimits = $watchmysql->get_package_limits();
	} else {
		$error = 'Package ' . $_GET['removepackage'] . ' does not have a limit set';
	}
}


?>
			<div class="page-header">
				<h1>Package Limits <small>Set per package limits</small></h1>
			</div>
			<p>Here you will find a list of limits set per package.  These limits superseed the set <a href="<?php echo $baseurl;?>/globalconfig.php" title="global config">global limits</a>.  A package with no limit listed will then be bound by the global limit.</p>
			<?php include('alerts.php'); ?>
			<p class="text-right">
				<a href="<?php echo $baseurl;?>/addpackagelimit.php" title="Add package limit">Add New Package Limit</a>
			</p>
			<div class="panel panel-default">
				<table class="table table-striped">
					<tr>
						<th>Package</th>
						<th>Connection Limit</th>
						<th>Actions</th>
					</tr>
					<?php foreach($packagelimits as $package => $limit) { ?>
					<tr>
						<td><?php echo $package;?></td>
						<td><?php echo $limit;?></td>
						<td>
							<a href="<?php echo $baseurl;?>/packagelimits.php?removepackage=<?php echo $package;?>" title="remove package limit"/>Remove</a>
							<span class="text-muted">&nbsp;|&nbsp;</span>
							<a href="<?php echo $baseurl;?>/modifypackagelimit.php?package=<?=$package;?>" title="modify package limit"/>Modify</a>
						</td>
					</tr>
					<?php } ?>
				</table>
			</div>

<?php include_once('footer.php'); ?>
