<?php
$nav = 'packagelimits';
include_once('header.php');

$package_limits = $watchmysql->get_package_limits();

$package = $_GET['package'] ?? ($_POST['package'] ?? '');
$limit = $_POST['limit'] ?? ($package_limits[$package] ?? '');

if (isset($_POST['action']) && $_POST['action'] == 'save') {
    if (is_numeric($_POST['limit'])) {
        $package_limits[$_POST['package']] = $_POST['limit'];
        $result = $watchmysql->save_package_limits($package_limits);
        if ($result === true) {
            $success = 'Updated limit for package ' . htmlspecialchars($_POST['package']) . ' to ' . intval($_POST['limit']);
        } else {
            $error = 'Failed to update limit for package ' . htmlspecialchars($_POST['package']);
        }
    } else {
        $error = 'Limit value must be numeric';
    }
}
?>
        <h4 class="mb-3 mt-2">Modify Package Limit</h4>
        <p class="text-muted">Change the connection limit for this package.</p>
        <?php include('alerts.php'); ?>

        <div class="card">
            <div class="card-body">
                <form action="<?php echo $baseurl; ?>/modifypackagelimit.php" method="post">
                    <input type="hidden" name="action" value="save">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-5">
                            <label class="form-label fw-bold">Package</label>
                            <input type="text" name="package" value="<?php echo htmlspecialchars($package); ?>" class="form-control" readonly>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Connection Limit</label>
                            <input type="number" name="limit" value="<?php echo intval($limit); ?>" class="form-control" min="0" required>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary w-100"><i class="bi bi-check-lg"></i> Save Limit</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

<?php include_once('footer.php'); ?>
