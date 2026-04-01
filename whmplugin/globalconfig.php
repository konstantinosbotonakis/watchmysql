<?php
$nav = 'globalconfig';
include_once('header.php');

if (isset($_POST['action']) && $_POST['action'] == 'save') {
    $result = $watchmysql->save_global_config($_POST);
    if ($result) {
        $success = 'Configuration saved successfully';
    } else {
        $error = 'Failed to save configuration';
    }
}

if (isset($_POST['action']) && $_POST['action'] == 'test_email') {
    $result = $watchmysql->send_test_email();
    if ($result) {
        $success = 'Test email sent successfully. Check your inbox.';
    } else {
        $error = 'Failed to send test email. ' . ($watchmysql->get_errors_string() ?: '');
    }
}

$global_config = $watchmysql->get_global_config();
?>
        <h4 class="mb-3 mt-2">Global Configuration</h4>
        <?php include('alerts.php'); ?>

        <form action="<?php echo $baseurl; ?>/globalconfig.php" method="post">
            <input type="hidden" name="action" value="save">
            <div class="card mb-4">
                <div class="card-header"><i class="bi bi-gear"></i> Daemon Settings</div>
                <div class="card-body">
                    <div class="row mb-3">
                        <label class="col-sm-3 col-form-label fw-bold">Automatic Updates</label>
                        <div class="col-sm-6">
                            <select name="automatic_updates" class="form-select">
                                <option value="1" <?php echo ($global_config['automatic_updates'] ?? '1') == '1' ? 'selected' : ''; ?>>Enabled</option>
                                <option value="0" <?php echo ($global_config['automatic_updates'] ?? '1') == '0' ? 'selected' : ''; ?>>Disabled</option>
                            </select>
                            <div class="form-text">Check for updates nightly and auto-upgrade if needed.</div>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-sm-3 col-form-label fw-bold">Admin Notifications</label>
                        <div class="col-sm-6">
                            <select name="notify_admin" class="form-select">
                                <option value="1" <?php echo ($global_config['notify_admin'] ?? '1') == '1' ? 'selected' : ''; ?>>Enabled</option>
                                <option value="0" <?php echo ($global_config['notify_admin'] ?? '1') == '0' ? 'selected' : ''; ?>>Disabled</option>
                            </select>
                            <div class="form-text">Email the server admin when a user exceeds their limit.</div>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-sm-3 col-form-label fw-bold">User Notifications</label>
                        <div class="col-sm-6">
                            <select name="notify_user" class="form-select">
                                <option value="1" <?php echo ($global_config['notify_user'] ?? '1') == '1' ? 'selected' : ''; ?>>Enabled</option>
                                <option value="0" <?php echo ($global_config['notify_user'] ?? '1') == '0' ? 'selected' : ''; ?>>Disabled</option>
                            </select>
                            <div class="form-text">Email the user when they exceed their limit.</div>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-sm-3 col-form-label fw-bold">Kill Connections</label>
                        <div class="col-sm-6">
                            <select name="kill_connections" class="form-select">
                                <option value="1" <?php echo ($global_config['kill_connections'] ?? '1') == '1' ? 'selected' : ''; ?>>Enabled</option>
                                <option value="0" <?php echo ($global_config['kill_connections'] ?? '1') == '0' ? 'selected' : ''; ?>>Disabled</option>
                            </select>
                            <div class="form-text">Kill all MySQL connections when a user exceeds their limit.</div>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-sm-3 col-form-label fw-bold">Check Interval</label>
                        <div class="col-sm-6">
                            <div class="input-group">
                                <input type="number" name="check_interval" value="<?php echo intval($global_config['check_interval'] ?? 900); ?>" class="form-control" min="300">
                                <span class="input-group-text">seconds</span>
                            </div>
                            <div class="form-text">How often the daemon checks for violations. Minimum 300s (5 min), default 900s (15 min).</div>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-sm-3 col-form-label fw-bold">Default Connection Limit</label>
                        <div class="col-sm-6">
                            <input type="number" name="connection_limit" value="<?php echo intval($global_config['connection_limit'] ?? 10); ?>" class="form-control" min="0">
                            <div class="form-text">Global limit for all users without a specific user or package limit. Set to 0 to disable.</div>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <label class="col-sm-3 col-form-label fw-bold">Slow Query Threshold</label>
                        <div class="col-sm-6">
                            <div class="input-group">
                                <input type="number" name="slow_query_threshold" value="<?php echo intval($global_config['slow_query_threshold'] ?? 30); ?>" class="form-control" min="1">
                                <span class="input-group-text">seconds</span>
                            </div>
                            <div class="form-text">Queries running longer than this are shown on the Slow Queries page.</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="d-flex gap-2 mb-4">
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> Save Configuration</button>
            </div>
        </form>

        <!-- Test Email -->
        <div class="card">
            <div class="card-header"><i class="bi bi-envelope"></i> Test Notifications</div>
            <div class="card-body">
                <p class="mb-2">Send a test notification email to verify that the mail delivery path is working correctly.</p>
                <form method="post">
                    <input type="hidden" name="action" value="test_email">
                    <button type="submit" class="btn btn-outline-primary"><i class="bi bi-send"></i> Send Test Email</button>
                </form>
            </div>
        </div>

<?php include_once('footer.php'); ?>
