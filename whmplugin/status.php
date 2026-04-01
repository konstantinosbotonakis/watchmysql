<?php
$nav = 'status';
include_once('header.php');

$status = $watchmysql->mysql_global_status([
    'Threads_connected', 'Threads_running', 'Max_used_connections',
    'Connections', 'Queries', 'Slow_queries', 'Uptime',
    'Questions', 'Bytes_sent', 'Bytes_received',
    'Com_select', 'Com_insert', 'Com_update', 'Com_delete',
    'Table_locks_waited', 'Table_locks_immediate'
]);

$variables = $watchmysql->mysql_global_variables([
    'max_connections', 'wait_timeout', 'interactive_timeout',
    'slow_query_log', 'long_query_time', 'innodb_buffer_pool_size',
    'version'
]);

$max_conn = intval($variables['max_connections'] ?? 151);
$max_used = intval($status['Max_used_connections'] ?? 0);
$headroom = $max_conn > 0 ? round(($max_used / $max_conn) * 100) : 0;
$uptime = intval($status['Uptime'] ?? 0);
?>
        <h4 class="mb-3 mt-2">MySQL Server Status</h4>
        <?php include('alerts.php'); ?>

        <!-- Server Info -->
        <div class="row g-3 mb-4">
            <div class="col-md-6 col-lg-3">
                <div class="card">
                    <div class="card-body text-center py-3">
                        <div class="text-muted small text-uppercase">MySQL Version</div>
                        <div class="fs-5 fw-bold"><?php echo htmlspecialchars($variables['version'] ?? '-'); ?></div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="card">
                    <div class="card-body text-center py-3">
                        <div class="text-muted small text-uppercase">Uptime</div>
                        <div class="fs-5 fw-bold"><?php echo $watchmysql->format_uptime($uptime); ?></div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="card">
                    <div class="card-body text-center py-3">
                        <div class="text-muted small text-uppercase">Total Queries</div>
                        <div class="fs-5 fw-bold"><?php echo number_format(intval($status['Queries'] ?? 0)); ?></div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="card">
                    <div class="card-body text-center py-3">
                        <div class="text-muted small text-uppercase">Slow Queries</div>
                        <div class="fs-5 fw-bold <?php echo intval($status['Slow_queries'] ?? 0) > 0 ? 'text-warning' : ''; ?>">
                            <?php echo number_format(intval($status['Slow_queries'] ?? 0)); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Connection Headroom -->
        <div class="card mb-4">
            <div class="card-header"><i class="bi bi-bar-chart-line"></i> Connection Headroom</div>
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <div class="d-flex justify-content-between mb-1">
                            <span>Peak Used: <strong><?php echo $max_used; ?></strong></span>
                            <span>Max Allowed: <strong><?php echo $max_conn; ?></strong></span>
                        </div>
                        <div class="progress" style="height: 24px;">
                            <div class="progress-bar <?php echo $headroom > 80 ? 'bg-danger' : ($headroom > 50 ? 'bg-warning' : 'bg-success'); ?>"
                                 style="width: <?php echo min($headroom, 100); ?>%">
                                <?php echo $headroom; ?>%
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 text-center">
                        <span class="fs-4 fw-bold <?php echo $headroom > 80 ? 'text-danger' : ($headroom > 50 ? 'text-warning' : 'text-success'); ?>">
                            <?php echo $max_conn - $max_used; ?>
                        </span>
                        <br><small class="text-muted">connections available</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detailed Stats -->
        <div class="row g-3">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header"><i class="bi bi-diagram-3"></i> Connection Stats</div>
                    <table class="table table-sm mb-0">
                        <tr><td>Current Connections</td><td class="text-end fw-bold"><?php echo $status['Threads_connected'] ?? '-'; ?></td></tr>
                        <tr><td>Active Threads</td><td class="text-end fw-bold"><?php echo $status['Threads_running'] ?? '-'; ?></td></tr>
                        <tr><td>Peak Connections</td><td class="text-end fw-bold"><?php echo $status['Max_used_connections'] ?? '-'; ?></td></tr>
                        <tr><td>Total Connections (lifetime)</td><td class="text-end fw-bold"><?php echo number_format(intval($status['Connections'] ?? 0)); ?></td></tr>
                        <tr><td>max_connections setting</td><td class="text-end fw-bold"><?php echo $variables['max_connections'] ?? '-'; ?></td></tr>
                        <tr><td>wait_timeout</td><td class="text-end"><?php echo $variables['wait_timeout'] ?? '-'; ?>s</td></tr>
                        <tr><td>interactive_timeout</td><td class="text-end"><?php echo $variables['interactive_timeout'] ?? '-'; ?>s</td></tr>
                    </table>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header"><i class="bi bi-activity"></i> Query Stats</div>
                    <table class="table table-sm mb-0">
                        <tr><td>Questions</td><td class="text-end fw-bold"><?php echo number_format(intval($status['Questions'] ?? 0)); ?></td></tr>
                        <tr><td>SELECT</td><td class="text-end"><?php echo number_format(intval($status['Com_select'] ?? 0)); ?></td></tr>
                        <tr><td>INSERT</td><td class="text-end"><?php echo number_format(intval($status['Com_insert'] ?? 0)); ?></td></tr>
                        <tr><td>UPDATE</td><td class="text-end"><?php echo number_format(intval($status['Com_update'] ?? 0)); ?></td></tr>
                        <tr><td>DELETE</td><td class="text-end"><?php echo number_format(intval($status['Com_delete'] ?? 0)); ?></td></tr>
                        <tr><td>Slow Query Log</td><td class="text-end"><?php echo ($variables['slow_query_log'] ?? 'OFF') === 'ON' ? '<span class="badge bg-success">ON</span>' : '<span class="badge bg-secondary">OFF</span>'; ?></td></tr>
                        <tr><td>long_query_time</td><td class="text-end"><?php echo $variables['long_query_time'] ?? '-'; ?>s</td></tr>
                    </table>
                </div>
            </div>
        </div>

<?php include_once('footer.php'); ?>
