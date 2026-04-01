<?php
$nav = 'slowqueries';
include_once('header.php');

$global_config = $watchmysql->get_global_config();
$threshold = intval($global_config['slow_query_threshold'] ?? 30);

// Kill slow query
if (isset($_GET['kill']) && is_numeric($_GET['kill'])) {
    $result = $watchmysql->mysql_kill_id($_GET['kill']);
    if ($result) {
        $success = 'Killed slow query #' . intval($_GET['kill']);
        $watchmysql->log_event('slow_query', 1, '-', 'manual_kill');
    } else {
        $error = 'Failed to kill query #' . intval($_GET['kill']);
    }
}

$slow_processes = $watchmysql->mysql_slow_processes($threshold);
?>
        <h4 class="mb-3 mt-2">Slow Queries <small class="text-muted fs-6">(running &gt; <?php echo $threshold; ?>s)</small></h4>
        <?php include('alerts.php'); ?>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-hourglass-split"></i> Active Slow Queries</span>
                <span class="badge bg-<?php echo count($slow_processes) > 0 ? 'warning text-dark' : 'success'; ?>">
                    <?php echo count($slow_processes); ?> queries
                </span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover table-sm mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Id</th>
                            <th>User</th>
                            <th>Database</th>
                            <th>Time</th>
                            <th>State</th>
                            <th>Query</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (is_array($slow_processes) && count($slow_processes) > 0) {
                            foreach ($slow_processes as $proc) { ?>
                        <tr class="<?php echo intval($proc['TIME'] ?? 0) > ($threshold * 3) ? 'table-danger' : 'table-warning'; ?>">
                            <td><?php echo intval($proc['ID'] ?? 0); ?></td>
                            <td><code><?php echo htmlspecialchars($proc['USER'] ?? ''); ?></code></td>
                            <td><?php echo htmlspecialchars($proc['DB'] ?? ''); ?></td>
                            <td><strong><?php echo intval($proc['TIME'] ?? 0); ?>s</strong></td>
                            <td><small><?php echo htmlspecialchars($proc['STATE'] ?? ''); ?></small></td>
                            <td><small class="text-break"><?php echo htmlspecialchars(substr($proc['INFO'] ?? '', 0, 200)); ?></small></td>
                            <td>
                                <a href="<?php echo $baseurl; ?>/slowqueries.php?kill=<?php echo intval($proc['ID']); ?>"
                                   class="btn btn-danger btn-sm py-0 px-1"
                                   onclick="return confirm('Kill slow query #<?php echo intval($proc['ID']); ?>?')">
                                    <i class="bi bi-x-lg"></i>
                                </a>
                            </td>
                        </tr>
                        <?php } } else { ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                <i class="bi bi-check-circle fs-4"></i><br>
                                No slow queries running
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>

        <p class="text-muted mt-2">
            <small>Threshold is configurable in <a href="<?php echo $baseurl; ?>/globalconfig.php">Global Config</a>. Currently set to <?php echo $threshold; ?> seconds.</small>
        </p>

<?php include_once('footer.php'); ?>
