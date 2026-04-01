<?php if (!$watchmysql->is_latest() && !isset($no_upgrade_check)) { ?>
<div class="alert alert-info d-flex align-items-start gap-3">
    <i class="bi bi-arrow-repeat fs-4"></i>
    <div>
        <strong>New Version Available</strong><br>
        A new version is available. <a href="<?php echo $baseurl; ?>/upgrade.php" class="alert-link">Upgrade now</a> or wait for the automatic nightly update.
    </div>
</div>
<?php } ?>

<?php if (!$watchmysql->get_watchmysql_pid()) { ?>
<div class="alert alert-warning d-flex align-items-start gap-3">
    <i class="bi bi-exclamation-triangle-fill fs-4"></i>
    <div>
        <strong>Daemon Not Running</strong><br>
        The WatchMySQL daemon is not running. Limits will not be enforced. <a href="<?php echo $baseurl; ?>/startdaemon.php" class="alert-link">Start daemon</a>
    </div>
</div>
<?php } ?>

<?php if (isset($error)) { ?>
<div class="alert alert-danger d-flex align-items-start gap-3">
    <i class="bi bi-x-circle-fill fs-4"></i>
    <div>
        <strong>Error</strong><br>
        <?php echo htmlspecialchars($error); ?>
    </div>
</div>
<?php } ?>

<?php if (isset($success)) { ?>
<div class="alert alert-success d-flex align-items-start gap-3">
    <i class="bi bi-check-circle-fill fs-4"></i>
    <div>
        <strong>Success</strong><br>
        <?php echo htmlspecialchars($success); ?>
    </div>
</div>
<?php } ?>
