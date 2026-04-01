<?php
$nav = 'index';
include_once('header.php');
$process_list = $watchmysql->mysql_process_list();

// Kill MySQL Process
if (isset($_GET['kill']) && is_numeric($_GET['kill'])) {
    if (is_array($process_list) && isset($process_list[$_GET['kill']])) {
        $killed_user = $process_list[$_GET['kill']]['User'] ?? 'unknown';
        $result = $watchmysql->mysql_kill_id($_GET['kill']);
        if ($result) {
            $success = 'Killed MySQL connection #' . intval($_GET['kill']);
            $watchmysql->log_event($killed_user, 1, '-', 'manual_kill');
        } else {
            $error = 'Failed to kill MySQL connection #' . intval($_GET['kill']);
        }
        $process_list = $watchmysql->mysql_process_list();
    }
}

// Kill all connections for a user
if (isset($_POST['kill_user']) && $_POST['kill_user']) {
    $kill_user = $_POST['kill_user'];
    $killed = 0;
    if (is_array($process_list)) {
        $global_config = $watchmysql->get_global_config();
        foreach ($process_list as $proc) {
            $mysql_user = $proc['User'];
            if (($global_config['cpanel'] ?? '0') == '1') {
                $parts = explode('_', $mysql_user, 2);
                $cpuser = $parts[0];
            } else {
                $cpuser = $mysql_user;
            }
            if ($cpuser === $kill_user) {
                $watchmysql->mysql_kill_id($proc['Id']);
                $killed++;
            }
        }
    }
    if ($killed > 0) {
        $success = "Killed $killed connection(s) for user " . htmlspecialchars($kill_user);
        $watchmysql->log_event($kill_user, $killed, '-', 'manual_kill_all');
    }
    $process_list = $watchmysql->mysql_process_list();
}

// Dashboard stats
$status_vars = $watchmysql->mysql_global_status(['Threads_connected', 'Threads_running', 'Max_used_connections', 'Slow_queries', 'Uptime']);
$server_vars = $watchmysql->mysql_global_variables(['max_connections']);
$daemon_pid = $watchmysql->get_watchmysql_pid();
$total_connections = is_array($process_list) ? count($process_list) : 0;
$max_connections = intval($server_vars['max_connections'] ?? 151);
$conn_ratio = $max_connections > 0 ? round(($total_connections / $max_connections) * 100) : 0;

// Slow queries from process list
$slow_count = 0;
$global_config_dash = $watchmysql->get_global_config();
$slow_threshold = intval($global_config_dash['slow_query_threshold'] ?? 30);
if (is_array($process_list)) {
    foreach ($process_list as $proc) {
        if (intval($proc['Time'] ?? 0) > $slow_threshold && ($proc['Command'] ?? '') !== 'Sleep') {
            $slow_count++;
        }
    }
}

// Connection summary
$user_summary = $watchmysql->get_user_connection_summary($process_list);

// Sorting
$sort_field_map = [
    'id' => 'Id', 'user' => 'User', 'db' => 'db',
    'command' => 'Command', 'time' => 'Time', 'info' => 'Info'
];
if (isset($_GET['sort']) && isset($sort_field_map[$_GET['sort']]) && is_array($process_list)) {
    $field = $sort_field_map[$_GET['sort']];
    $numeric = in_array($_GET['sort'], ['id', 'time']);
    usort($process_list, function($a, $b) use ($field, $numeric) {
        if ($numeric) return intval($a[$field] ?? 0) - intval($b[$field] ?? 0);
        return strcmp($a[$field] ?? '', $b[$field] ?? '');
    });
}
?>
        <h4 class="mb-3 mt-2">Dashboard</h4>
        <?php include('alerts.php'); ?>

        <!-- Dashboard Cards -->
        <div class="row g-3 mb-4">
            <div class="col-sm-6 col-lg-3">
                <div class="card text-white <?php echo $daemon_pid ? 'bg-success' : 'bg-danger'; ?>">
                    <div class="card-body py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-uppercase small opacity-75">Daemon</div>
                                <div class="fs-5 fw-bold"><?php echo $daemon_pid ? 'Running' : 'Stopped'; ?></div>
                            </div>
                            <i class="bi bi-<?php echo $daemon_pid ? 'play-circle' : 'stop-circle'; ?> fs-2 opacity-50"></i>
                        </div>
                        <?php if ($daemon_pid) { ?>
                        <small class="opacity-75">PID <?php echo $daemon_pid; ?></small>
                        <?php } ?>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card text-white <?php echo $conn_ratio > 80 ? 'bg-danger' : ($conn_ratio > 50 ? 'bg-warning' : 'bg-primary'); ?>">
                    <div class="card-body py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-uppercase small opacity-75">Connections</div>
                                <div class="fs-5 fw-bold"><?php echo $total_connections; ?> / <?php echo $max_connections; ?></div>
                            </div>
                            <i class="bi bi-diagram-3 fs-2 opacity-50"></i>
                        </div>
                        <div class="progress mt-1" style="height:4px;">
                            <div class="progress-bar bg-light" style="width:<?php echo min($conn_ratio, 100); ?>%"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card text-white bg-info">
                    <div class="card-body py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-uppercase small opacity-75">Threads Running</div>
                                <div class="fs-5 fw-bold"><?php echo $status_vars['Threads_running'] ?? '0'; ?></div>
                            </div>
                            <i class="bi bi-cpu fs-2 opacity-50"></i>
                        </div>
                        <small class="opacity-75">Peak: <?php echo $status_vars['Max_used_connections'] ?? '-'; ?></small>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-lg-3">
                <div class="card text-white <?php echo $slow_count > 0 ? 'bg-warning' : 'bg-secondary'; ?>">
                    <div class="card-body py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-uppercase small opacity-75">Slow Queries</div>
                                <div class="fs-5 fw-bold"><?php echo $slow_count; ?></div>
                            </div>
                            <i class="bi bi-hourglass-split fs-2 opacity-50"></i>
                        </div>
                        <small class="opacity-75">Threshold: <?php echo $slow_threshold; ?>s</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Per-User Connection Summary -->
        <?php if (!empty($user_summary)) { ?>
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-people"></i> Connections by User</span>
                <span class="badge bg-secondary"><?php echo count($user_summary); ?> users</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover table-sm mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>User</th>
                            <th>Package</th>
                            <th>Connections</th>
                            <th>Limit</th>
                            <th>Source</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($user_summary as $user => $info) { ?>
                        <tr>
                            <td><code><?php echo htmlspecialchars($user); ?></code></td>
                            <td><?php echo htmlspecialchars($info['package']); ?></td>
                            <td><strong><?php echo $info['connections']; ?></strong></td>
                            <td><?php echo $info['limit']; ?></td>
                            <td><span class="badge bg-<?php echo $info['source'] === 'User' ? 'primary' : ($info['source'] === 'Package' ? 'info' : 'secondary'); ?>"><?php echo $info['source']; ?></span></td>
                            <td>
                                <?php if ($info['status'] === 'danger') { ?>
                                    <span class="badge bg-danger">Over Limit</span>
                                <?php } elseif ($info['status'] === 'warning') { ?>
                                    <span class="badge bg-warning text-dark">High</span>
                                <?php } else { ?>
                                    <span class="badge bg-success">OK</span>
                                <?php } ?>
                            </td>
                            <td>
                                <form method="post" style="display:inline" onsubmit="return confirm('Kill all connections for <?php echo htmlspecialchars($user); ?>?')">
                                    <input type="hidden" name="kill_user" value="<?php echo htmlspecialchars($user); ?>">
                                    <button type="submit" class="btn btn-outline-danger btn-sm py-0 px-1" title="Kill all connections for this user">
                                        <i class="bi bi-x-lg"></i> Kill All
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php } ?>

        <!-- Process List -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-list-task"></i> MySQL Process List</span>
                <span class="badge bg-secondary"><?php echo $total_connections; ?> connections</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover table-sm mb-0">
                    <thead class="table-light">
                        <tr>
                            <th><a href="<?php echo $baseurl; ?>/index.php?sort=id" class="text-decoration-none">Id</a></th>
                            <th><a href="<?php echo $baseurl; ?>/index.php?sort=user" class="text-decoration-none">User</a></th>
                            <th><a href="<?php echo $baseurl; ?>/index.php?sort=db" class="text-decoration-none">Database</a></th>
                            <th><a href="<?php echo $baseurl; ?>/index.php?sort=command" class="text-decoration-none">Command</a></th>
                            <th><a href="<?php echo $baseurl; ?>/index.php?sort=time" class="text-decoration-none">Time</a></th>
                            <th><a href="<?php echo $baseurl; ?>/index.php?sort=info" class="text-decoration-none">Info</a></th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (is_array($process_list)) { foreach ($process_list as $process_line) { ?>
                        <tr<?php if (intval($process_line['Time'] ?? 0) > $slow_threshold && ($process_line['Command'] ?? '') !== 'Sleep') echo ' class="table-warning"'; ?>>
                            <td><?php echo intval($process_line['Id']); ?></td>
                            <td><code><?php echo htmlspecialchars($process_line['User'] ?? ''); ?></code></td>
                            <td><?php echo htmlspecialchars($process_line['db'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($process_line['Command'] ?? ''); ?></td>
                            <td><?php echo intval($process_line['Time'] ?? 0); ?>s</td>
                            <td><small><?php echo htmlspecialchars(substr($process_line['Info'] ?? '', 0, 120)); ?></small></td>
                            <td>
                                <a href="<?php echo $baseurl; ?>/index.php?kill=<?php echo intval($process_line['Id']); ?>"
                                   class="btn btn-danger btn-sm py-0 px-1"
                                   onclick="return confirm('Kill process #<?php echo intval($process_line['Id']); ?>?')"
                                   title="Kill this process"><i class="bi bi-x-lg"></i></a>
                            </td>
                        </tr>
                        <?php } } ?>
                    </tbody>
                </table>
            </div>
        </div>

<?php include_once('footer.php'); ?>
