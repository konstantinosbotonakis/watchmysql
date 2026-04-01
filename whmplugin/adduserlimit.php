<?php
$nav = 'userlimits';
include_once('header.php');

$users = $watchmysql->get_user_list();
$global_config = $watchmysql->get_global_config();

if (isset($_POST['action']) && $_POST['action'] == 'save') {
    $result = $watchmysql->add_user_limit($_POST['user'], $_POST['limit']);
    if ($result === true) {
        $success = 'Added limit for ' . htmlspecialchars($_POST['user']) . ': ' . intval($_POST['limit']);
    } else {
        $error = $watchmysql->get_errors_string() ?: 'Failed to add user limit';
    }
}

$preselected = $_GET['user'] ?? '';
?>
        <h4 class="mb-3 mt-2">Add User Limit</h4>
        <p class="text-muted">Setting a per-user limit overrides the package and global limits for this user.</p>
        <?php include('alerts.php'); ?>

        <div class="card">
            <div class="card-body">
                <form action="<?php echo $baseurl; ?>/adduserlimit.php" method="post">
                    <input type="hidden" name="action" value="save">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-5">
                            <label class="form-label fw-bold">Username</label>
                            <select name="user" class="form-select" required>
                                <option value="">Select Username</option>
                                <?php foreach ($users as $user => $details) { ?>
                                <option value="<?php echo htmlspecialchars($user); ?>" <?php echo $user === $preselected ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($user); ?>
                                </option>
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
