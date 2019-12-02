<?PHP 
$nav='globalconfig';
include_once('header.php');

if(isset($_POST['action']) && $_POST['action'] == 'save') {
	$result = $watchmysql->save_global_config($_POST);
	if($result) {
		$success = 'Successfully saved configuration';
	} else {
		$error = 'Failed to save configuration';
	}
}

$global_config = $watchmysql->get_global_config();
?>

			<div class="page-header">
				<h1>Global Config</h1>
			</div>

			<?php include('alerts.php'); ?>

			<form action="<?php echo $baseurl; ?>/globalconfig.php" method="post">
				<input type="hidden" name="action" value="save">
				<div class="panel panel-default">
					<table class="table table-striped">
						<tr>
							<th nowrap>Automatic Updates</th>
							<td><p>Keep this software up to date by checking for updates nightly and automatically upgrading if needed.</p></td>
							<td>
								<select name="automatic_updates" class="form-control">
									<option value="1" <?=$global_config['automatic_updates'] == '1' ? 'selected' : '';?>>Enabled</option>
									<option value="0" <?=$global_config['automatic_updates'] == '0' ? 'selected' : '';?>>Disabled</option>
								</select>
							</td>
						</tr>
						<tr>
							<td nowrap><strong>Admin Notifications</strong></td>
							<td><p>Notify the admin when a user exceeds their connection limit.</p></td>
							<td>
								<select name="notify_admin" class="form-control">
									<option value="1" <?=$global_config['notify_admin'] == '1' ? 'selected' : '';?>>Enabled</option>
									<option value="0" <?=$global_config['notify_admin'] == '0' ? 'selected' : '';?>>Disabled</option>
								</select>
							</td>
						</tr>
						<tr>
							<td nowrap><strong>User Notifications</strong></td>
							<td><p>Notify the user when they exceed their connection limit.</p></td>
							<td>
								<select name="notify_user" class="form-control">
									<option value="1" <?=$global_config['notify_user'] == '1' ? 'selected' : '';?>>Enabled</option>
									<option value="0" <?=$global_config['notify_user'] == '0' ? 'selected' : '';?>>Disabled</option>
								</select>
							</td>
						</tr>
						<tr>
							<td nowrap><strong>Kill Connections</strong></td>
							<td><p>Kill all the users MySQL connections when they exceed their limit.</p>
							<td>
								<select name="kill_connections" class="form-control">
									<option value="1" <?=$global_config['kill_connections'] == '1' ? 'selected' : '';?>>Enabled</option>
									<option value="0" <?=$global_config['kill_connections'] == '0' ? 'selected' : '';?>>Disabled</option>
								</select>
							</td>
						</tr>
						<tr>
							<td nowrap><strong>Check Interval</strong></td>
							<td><p>How often the watchmysql daemon should check for users over their limits.  This value is in seconds, default value is 900 (15 minutes)</p></td>
							<td><input type="textbox" name="check_interval" value="<?=$global_config['check_interval'];?>" class="form-control"></td>
						</tr>
						<tr>
							<td nowrap><strong>Default Limit</strong></td>
							<td><p>Set a default limit for user connections.  Setting this value to 0 will disable the global limit.</p></td>
							<td><input type="textbox" name="connection_limit" value="<?=$global_config['connection_limit'];?>" class="form-control"></td>
						</tr>
					</table>
				</div>
				<div class="form-group text-right">
					<button type="submit" class="btn btn-primary">Save Config</button>
				</div>
			</form>
<?php include_once('footer.php'); ?>
