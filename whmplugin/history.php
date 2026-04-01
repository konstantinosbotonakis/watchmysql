<?php
$nav = 'history';
include_once('header.php');

$history = $watchmysql->get_history(500);
$filter_user = $_GET['user'] ?? '';
if ($filter_user) {
    $history = array_filter($history, function($entry) use ($filter_user) {
        return $entry['user'] === $filter_user;
    });
}
?>
        <h4 class="mb-3 mt-2">Connection History</h4>
        <p class="text-muted">Log of manual kills and daemon enforcement actions.</p>
        <?php include('alerts.php'); ?>

        <?php if ($filter_user) { ?>
        <div class="alert alert-info py-2">
            Filtering by user: <strong><?php echo htmlspecialchars($filter_user); ?></strong>
            <a href="<?php echo $baseurl; ?>/history.php" class="alert-link ms-2">Clear filter</a>
        </div>
        <?php } ?>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-clock-history"></i> Event Log</span>
                <span class="badge bg-secondary"><?php echo count($history); ?> events</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover table-sm mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Time</th>
                            <th>User</th>
                            <th>Connections</th>
                            <th>Limit</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($history) > 0) {
                            foreach ($history as $entry) { ?>
                        <tr>
                            <td><small><?php echo htmlspecialchars($entry['timestamp']); ?></small></td>
                            <td>
                                <a href="<?php echo $baseurl; ?>/history.php?user=<?php echo urlencode($entry['user']); ?>" class="text-decoration-none">
                                    <code><?php echo htmlspecialchars($entry['user']); ?></code>
                                </a>
                            </td>
                            <td><?php echo htmlspecialchars($entry['connections']); ?></td>
                            <td><?php echo htmlspecialchars($entry['limit']); ?></td>
                            <td>
                                <?php
                                $action = $entry['action'];
                                $badge_class = 'bg-secondary';
                                if (strpos($action, 'kill') !== false) $badge_class = 'bg-danger';
                                if (strpos($action, 'notify') !== false) $badge_class = 'bg-info';
                                ?>
                                <span class="badge <?php echo $badge_class; ?>"><?php echo htmlspecialchars($action); ?></span>
                            </td>
                        </tr>
                        <?php } } else { ?>
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">
                                <i class="bi bi-clock-history fs-4"></i><br>
                                No history events recorded yet
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>

<?php include_once('footer.php'); ?>
