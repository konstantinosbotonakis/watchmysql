<?php
$nav = 'whitelist';
include_once('header.php');

$users = $watchmysql->get_user_list();
$whitelist = $watchmysql->get_whitelist();

// Add to whitelist
if (isset($_POST['action']) && $_POST['action'] == 'add' && isset($_POST['user'])) {
    $result = $watchmysql->add_to_whitelist($_POST['user']);
    if ($result) {
        $success = 'Added ' . htmlspecialchars($_POST['user']) . ' to whitelist';
        $whitelist = $watchmysql->get_whitelist();
    } else {
        $error = $watchmysql->get_errors_string();
    }
}

// Remove from whitelist
if (isset($_GET['remove'])) {
    $result = $watchmysql->remove_from_whitelist($_GET['remove']);
    if ($result) {
        $success = 'Removed ' . htmlspecialchars($_GET['remove']) . ' from whitelist';
        $whitelist = $watchmysql->get_whitelist();
    } else {
        $error = $watchmysql->get_errors_string();
    }
}
?>
        <h4 class="mb-3 mt-2">Whitelisted Users</h4>
        <p class="text-muted">Whitelisted users are exempt from connection limit enforcement. Their connections will never be killed and no alerts will be sent for them. Useful for backup processes, monitoring tools, and system accounts.</p>
        <?php include('alerts.php'); ?>

        <!-- Add User Form -->
        <div class="card mb-4">
            <div class="card-header"><i class="bi bi-plus-circle"></i> Add User to Whitelist</div>
            <div class="card-body">
                <form method="post" class="row g-2 align-items-end">
                    <input type="hidden" name="action" value="add">
                    <div class="col-auto">
                        <label class="form-label">Username</label>
                        <select name="user" class="form-select" required>
                            <option value="">Select User</option>
                            <?php foreach ($users as $user => $details) {
                                if (!in_array($user, $whitelist)) { ?>
                            <option value="<?php echo htmlspecialchars($user); ?>"><?php echo htmlspecialchars($user); ?></option>
                            <?php } } ?>
                        </select>
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Add</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Whitelist Table -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-shield-check"></i> Current Whitelist</span>
                <span class="badge bg-secondary"><?php echo count($whitelist); ?> users</span>
            </div>
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Username</th>
                        <th>Package</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($whitelist) > 0) {
                        foreach ($whitelist as $wl_user) { ?>
                    <tr>
                        <td><code><?php echo htmlspecialchars($wl_user); ?></code></td>
                        <td><?php echo htmlspecialchars($users[$wl_user]['PLAN'] ?? '-'); ?></td>
                        <td>
                            <a href="<?php echo $baseurl; ?>/whitelist.php?remove=<?php echo urlencode($wl_user); ?>"
                               class="btn btn-outline-danger btn-sm"
                               onclick="return confirm('Remove <?php echo htmlspecialchars($wl_user); ?> from whitelist?')">
                                <i class="bi bi-trash"></i> Remove
                            </a>
                        </td>
                    </tr>
                    <?php } } else { ?>
                    <tr>
                        <td colspan="3" class="text-center text-muted py-3">No users whitelisted</td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>

<?php include_once('footer.php'); ?>
