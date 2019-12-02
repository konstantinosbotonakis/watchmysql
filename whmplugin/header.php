<?php
// Set Base Url
$baseurl = $_ENV['cp_security_token'] . '/cgi/addons/watchmysql';
include_once(__DIR__ . '/watchmysql.class.php');
$watchmysql = new watchmysql();
?>
<!DOCTYPE html>
<html>
	<head>
		<title>Watch MySQL</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<link href="<?php echo $baseurl; ?>/assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
		<link href="<?php echo $baseurl; ?>/assets/watchmysql/css/watchmysql.css" rel="stylesheet">
	</head>
	<body>
		<div class="navbar navbar-default navbar-fixed-top" role="navigation">
			<div class="container">
				<div class="navbar-header">
					<a class="navbar-brand" href="<?php echo $baseurl; ?>/index.php">WatchMySQL</a>
				</div>
				<ul class="nav navbar-nav">
					<li<?php if($nav == 'userlimits') echo ' class="active"'; ?>><a href="<?php echo $baseurl; ?>/userlimits.php" title="User Limits">User Limits</a></li>
					<li<?php if($nav == 'packagelimits') echo ' class="active"'; ?>><a href="<?php echo $baseurl; ?>/packagelimits.php" title="Package Limits">Package Limits</a></li>
					<li<?php if($nav == 'globalconfig') echo ' class="active"'; ?>><a href="<?php echo $baseurl; ?>/globalconfig.php" title="Global Config">Global Config</a></li>
				</ul>
				<p class="navbar-text navbar-right">Version <?php echo $watchmysql->get_version(); ?></p>
			</div>
		</div>
		<div class="container">
