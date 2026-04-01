<?php
$nav = 'index';
$no_upgrade_check = true;
include_once('header.php');
?>
        <h4 class="mb-3 mt-2">Software Upgrade</h4>

        <div class="card">
            <div class="card-body">
                <pre class="mb-0"><?php
                if (defined('WATCHMYSQL_DEV') && WATCHMYSQL_DEV) {
                    echo "Upgrade not available in dev mode.\n";
                } elseif (is_file('/var/cpanel/addons/watchmysql/bin/upgrade')) {
                    passthru("/var/cpanel/addons/watchmysql/bin/upgrade");
                } else {
                    echo "Upgrade script not found.\n";
                }
                ?></pre>
            </div>
        </div>

        <a href="<?php echo $baseurl; ?>/index.php" class="btn btn-primary mt-3"><i class="bi bi-arrow-left"></i> Back to Dashboard</a>

<?php include_once('footer.php'); ?>
