<?php
$nav = 'packagelimits';
include_once('header.php');

$packages = $watchmysql->get_package_list();
$global_config = $watchmysql->get_global_config();

if (isset($_POST['action']) && $_POST['action'] == 'save') {
    $result = $watchmysql->add_package_limit($_POST['package'], $_POST['limit']);
    if ($result === true) {
        $success = 'Added limit for package ' . htmlspecialchars($_POST['package']) . ': ' . intval($_POST['limit']);
    } else {
        $error = $watchmysql->get_errors_string() ?: 'Failed to add package limit';
    }
}
?>
        <h4 class="mb-3 mt-2">Add Package Limit</h4>
        <p class="text-muted">Users on this package will be bound by this limit instead of the global limit.</p>
        <?php include('alerts.php'); ?>

        <div class="card">
            <div class="card-body">
                <form action="<?php echo $baseurl; ?>/addpackagelimit.php" method="post">
                    <input type="hidden" name="action" value="save">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-5">
                            <label class="form-label fw-bold">Package</label>
                            <select name="package" class="form-select" required>
                                <option value="">Select Package</option>
                                <?php foreach ($packages as $package => $details) { ?>
                                <option value="<?php echo htmlspecialchars($package); ?>"><?php echo htmlspecialchars($package); ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Connection Limit</label>
                            <input type="number" name="limit" value="<?php echo intval($global_config['connection_limit'] ?? 10); ?>" class="form-control" min="0" required>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary w-100"><i class="bi bi-check-lg"></i> Save Limit</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

<?php include_once('footer.php'); ?>
