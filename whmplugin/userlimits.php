<?php
$nav = 'userlimits';
include_once('header.php');

$users = $watchmysql->get_user_list();
$userlimits = $watchmysql->get_user_limits();

// Remove
if (isset($_GET['removeuser'])) {
    if (isset($userlimits[$_GET['removeuser']])) {
        $result = $watchmysql->remove_user_limit($_GET['removeuser']);
        if ($result) {
            $success = 'Removed limit for ' . htmlspecialchars($_GET['removeuser']);
        } else {
            $error = 'Failed to remove limit for ' . htmlspecialchars($_GET['removeuser']);
        }
        $userlimits = $watchmysql->get_user_limits();
    } else {
        $error = htmlspecialchars($_GET['removeuser']) . ' does not have a limit set';
    }
}

// Get effective limits for all users
$effective_limits = $watchmysql->get_all_user_effective_limits();
?>
        <h4 class="mb-3 mt-2">User Limits</h4>
        <p class="text-muted">Per-user limits override package and global limits. A user with no explicit limit is bound by their package limit, then the global limit.</p>
        <?php include('alerts.php'); ?>

        <div class="mb-3 text-end">
            <a href="<?php echo $baseurl; ?>/adduserlimit.php" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-lg"></i> Add User Limit
            </a>
        </div>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-people"></i> All Users &mdash; Effective Limits</span>
                <span class="badge bg-secondary"><?php echo count($effective_limits); ?> users</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover table-sm mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Username</th>
                            <th>Package</th>
                            <th>Connections</th>
                            <th>Effective Limit</th>
                            <th>Source</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($effective_limits as $username => $info) {
                            $has_limit = $info['has_explicit_limit'];
                        ?>
                        <tr class="<?php echo $has_limit ? 'table-light' : ''; ?>">
                            <td>
                                <code><?php echo htmlspecialchars($username); ?></code>
                                <?php if ($info['whitelisted']) { ?>
                                    <span class="badge bg-success ms-1">WL</span>
                                <?php } ?>
                            </td>
                            <td><?php echo htmlspecialchars($info['package']); ?></td>
                            <td><?php echo $info['connections']; ?></td>
                            <td>
                                <strong><?php echo $info['limit']; ?></strong>
                            </td>
                            <td>
                                <span class="badge bg-<?php echo $info['source'] === 'User' ? 'primary' : ($info['source'] === 'Package' ? 'info' : 'secondary'); ?>">
                                    <?php echo $info['source']; ?>
                                </span>
                            </td>
                            <td>
                                <?php
                                if ($info['whitelisted']) {
                                    echo '<span class="badge bg-secondary">Exempt</span>';
                                } elseif ($info['limit'] > 0 && $info['connections'] >= $info['limit']) {
                                    echo '<span class="badge bg-danger">Over</span>';
                                } elseif ($info['limit'] > 0 && $info['connections'] >= $info['limit'] * 0.75) {
                                    echo '<span class="badge bg-warning text-dark">High</span>';
                                } else {
                                    echo '<span class="badge bg-success">OK</span>';
                                }
                                ?>
                            </td>
                            <td>
                                <?php if ($has_limit) { ?>
                                <a href="<?php echo $baseurl; ?>/modifyuserlimit.php?user=<?php echo urlencode($username); ?>"
                                   class="btn btn-outline-primary btn-sm py-0 px-1" title="Modify limit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <a href="<?php echo $baseurl; ?>/userlimits.php?removeuser=<?php echo urlencode($username); ?>"
                                   class="btn btn-outline-danger btn-sm py-0 px-1"
                                   onclick="return confirm('Remove explicit limit for <?php echo htmlspecialchars($username); ?>?')"
                                   title="Remove limit">
                                    <i class="bi bi-trash"></i>
                                </a>
                                <?php } else { ?>
                                <a href="<?php echo $baseurl; ?>/adduserlimit.php?user=<?php echo urlencode($username); ?>"
                                   class="btn btn-outline-secondary btn-sm py-0 px-1" title="Set a custom limit">
                                    <i class="bi bi-plus"></i>
                                </a>
                                <?php } ?>
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>

<?php include_once('footer.php'); ?>
