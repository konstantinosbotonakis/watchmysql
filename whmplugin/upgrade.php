<?PHP 
$nav = 'index';
include_once('header.php');
?>
			<div class="page-header">
				<h1>WatchMySQL Software Upgrade</h1>
			</div>

			<pre><?php passthru("/var/cpanel/addons/watchmysql/bin/upgrade"); ?></pre>

<?php include_once('footer.php'); ?>
