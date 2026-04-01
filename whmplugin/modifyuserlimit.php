<?php
$nav = 'userlimits';
include_once('header.php');

$user_limits = $watchmysql->get_user_limits();

$user = $_GET['user'] ?? ($_POST['user'] ?? '');
$limit = $_POST['limit'] ?? ($user_limits[$user] ?? '');

if (isset($_POST['action']) && $_POST['action'] == 'save') {
    if (is_numeric($_POST['limit'])) {
        $user_limits[$_POST['user']] = $_POST['limit'];
        $result = $watchmysql->save_user_limits($user_limits);
        if ($result === true) {
            $success = 'Updated limit for ' . htmlspecialchars($_POST['user']) . ' to ' . intval($_POST['limit']);
        } else {
            $error = 'Failed to update limit for ' . htmlspecialchars($_POST['user']);
        }
    } else {
        $error = 'Limit must be a numeric value';
    }
}
?>
        <h4 class="mb-3 mt-2">Modify User Limit</h4>
        <p class="text-muted">Change the per-user connection limit. This overrides package and global limits.</p>
        <?php include('alerts.php'); ?>

        <div class="card">
            <div class="card-body">
                <form action="<?php echo $baseurl; ?>/modifyuserlimit.php" method="post">
                    <input type="hidden" name="action" value="save">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-5">
                            <label class="form-label fw-bold">Username</label>
                            <input type="text" name="user" value="<?php echo htmlspecialchars($user); ?>" class="form-control" readonly>
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
