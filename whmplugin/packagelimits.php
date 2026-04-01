<?php
$nav = 'packagelimits';
include_once('header.php');

$packagelimits = $watchmysql->get_package_limits();

// Remove
if (isset($_GET['removepackage'])) {
    if (isset($packagelimits[$_GET['removepackage']])) {
        $result = $watchmysql->remove_package_limit($_GET['removepackage']);
        if ($result) {
            $success = 'Removed limit for package ' . htmlspecialchars($_GET['removepackage']);
        } else {
            $error = 'Failed to remove limit for package ' . htmlspecialchars($_GET['removepackage']);
        }
        $packagelimits = $watchmysql->get_package_limits();
    } else {
        $error = 'Package ' . htmlspecialchars($_GET['removepackage']) . ' does not have a limit set';
    }
}
?>
        <h4 class="mb-3 mt-2">Package Limits</h4>
        <p class="text-muted">Package limits override the global limit for all users on a package. Per-user limits still take priority over package limits.</p>
        <?php include('alerts.php'); ?>

        <div class="mb-3 text-end">
            <a href="<?php echo $baseurl; ?>/addpackagelimit.php" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-lg"></i> Add Package Limit
            </a>
        </div>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-box"></i> Package Limits</span>
                <span class="badge bg-secondary"><?php echo count($packagelimits); ?> packages</span>
            </div>
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Package</th>
                        <th>Connection Limit</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($packagelimits) > 0) {
                        foreach ($packagelimits as $package => $limit) { ?>
                    <tr>
                        <td><code><?php echo htmlspecialchars($package); ?></code></td>
                        <td><strong><?php echo intval($limit); ?></strong></td>
                        <td>
                            <a href="<?php echo $baseurl; ?>/modifypackagelimit.php?package=<?php echo urlencode($package); ?>"
                               class="btn btn-outline-primary btn-sm py-0 px-1" title="Modify">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <a href="<?php echo $baseurl; ?>/packagelimits.php?removepackage=<?php echo urlencode($package); ?>"
                               class="btn btn-outline-danger btn-sm py-0 px-1"
                               onclick="return confirm('Remove limit for package <?php echo htmlspecialchars($package); ?>?')"
                               title="Remove">
                                <i class="bi bi-trash"></i>
                            </a>
                        </td>
                    </tr>
                    <?php } } else { ?>
                    <tr>
                        <td colspan="3" class="text-center text-muted py-3">No package limits set. All users use the global limit unless they have a per-user limit.</td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>

<?php include_once('footer.php'); ?>
