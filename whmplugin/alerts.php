<?php if($watchmysql->license_nag()) { ?>
<div class="alert alert-warning">
	<div class="media">
		<div class="pull-left" style="font-size:34px;padding:0px 10px;">
			<span class="glyphicon glyphicon-paperclip"></span>
		</div>
		<div class="media-body">
			<h4 class="media-heading">cPanel/WHM License Nag</h4>
			We noticed that your cPanel/WHM license was not purchased through <a href="http://www.ndchost.com/" target="_new" class="alert-link">NDCHost</a>.  Although this plugin will continue to work properly we would like to ask for your support by using <a href="http://www.ndchost.com/" target="_new" class="alert-link">NDCHost</a> for your cPanel licensing needs.  We will price and bill date match your existing license provider.  There will also be no downtime during the transfer process.  If you are interesting in supporting the company that brought you this plugin please visit <a href="http://www.ndchost.com/" target="_new" class="alert-link">www.NDCHost.com</a>.  Thanks!
		</div>
	</div>
</div>
<?php } ?>


<?php if(!$watchmysql->is_latest() && !isset($no_upgrade_check)) { ?>
<div class="alert alert-info">
	<div class="media">
		<div class="pull-left" style="font-size:34px;padding:0px 10px;">
			<span class="glyphicon glyphicon-refresh"></span>
		</div>
		<div class="media-body">
			<h4 class="media-heading">New Version Available!</h4>
			There is a new version of this software available.  If you have automatic updates enabled this software should upgrade itself at night.  You can manually upgrade the software by clicking <a href="<?php echo $baseurl; ?>/upgrade.php" class="alert-link" title="Upgrade WatchMySQL Software">here</a>
		</div>
	</div>
</div>
<?php } ?>


<?php if(!$watchmysql->get_watchmysql_pid()) { ?>
<div class="alert alert-warning">
	<div class="media">
		<div class="pull-left" style="font-size:34px;padding:0px 10px;">
			<span class="glyphicon glyphicon-thumbs-down"></span>
		</div>
		<div class="media-body">
			<h4 class="media-heading">WatchMySQL Daemon not running!</h4>
			The WatchMySQL daemon is not running!  With out this process your limits will not be enforced.  If you wish to attempt to start this daemon now <a href="<?php echo $baseurl;?>/startdaemon.php" class="alert-link" title="Start WatchMySQL back end">click here</a>
		</div>
	</div>
</div>
<?php } ?>

<?php if(isset($error)) { ?>
<div class="alert alert-danger">
	<div class="media">
		<div class="pull-left" style="font-size:34px;padding:0px 10px;">
			<span class="glyphicon glyphicon-thumbs-down"></span>
		</div>
		<div class="media-body">
			<h4 class="media-heading">Error!</h4>
			<?php echo $error; ?>
		</div>
	</div>
</div>
<?php } ?>

<?php if(isset($success)) { ?>
<div class="alert alert-success">
	<div class="media">
		<div class="pull-left" style="font-size:34px;padding:0px 10px;">
			<span class="glyphicon glyphicon-thumbs-up"></span>
		</div>
		<div class="media-body">
			<h4 class="media-heading">Success!</h4>
			<?php echo $success; ?>
		</div>
	</div>
</div>
<?php } ?>
