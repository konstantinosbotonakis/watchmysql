<?php
// Security Check - Do not allow non-root access
if (!defined('WATCHMYSQL_DEV') || !WATCHMYSQL_DEV) {
    if (($_ENV["REMOTE_USER"] ?? '') !== "root") {
        die('Only root is allowed to access this plug-in');
    }
}

class watchmysql {

    private $errors = array();
    private $global_config = array();
    private $mycnf = array();
    private $dbh = false;

    // Configurable paths (overridable for dev mode)
    private $config_file;
    private $mycnf_file;
    private $users_dir;
    private $packages_dir;
    private $userlimits_file;
    private $pkg_limits_file;
    private $whitelist_file;
    private $history_file;
    private $daemon_binary;
    private $pidof_binary;
    private $sendmail_binary;

    public function __construct(array $options = []) {
        $this->config_file     = $options['config_file']     ?? '/etc/watchmysql.config';
        $this->mycnf_file      = $options['mycnf_file']      ?? '/root/.my.cnf';
        $this->users_dir       = $options['users_dir']        ?? '/var/cpanel/users';
        $this->packages_dir    = $options['packages_dir']     ?? '/var/cpanel/packages';
        $this->userlimits_file = $options['userlimits_file']  ?? '/etc/watchmysql.userlimits';
        $this->pkg_limits_file = $options['pkg_limits_file']  ?? '/etc/watchmysql.packagelimits';
        $this->whitelist_file  = $options['whitelist_file']   ?? '/etc/watchmysql.whitelist';
        $this->history_file    = $options['history_file']     ?? '/var/log/watchmysql.history';
        $this->daemon_binary   = $options['daemon_binary']    ?? '/usr/sbin/watchmysql';
        $this->pidof_binary    = $options['pidof_binary']     ?? '/sbin/pidof';
        $this->sendmail_binary = $options['sendmail_binary']  ?? '/usr/sbin/sendmail';

        $mycnf = $this->get_config_mycnf();
        if ($mycnf === false) {
            return;
        }

        $global_config = $this->get_global_config();
        if ($global_config === false) {
            return;
        }

        // Newer versions of MySQL use 'password' instead of 'pass' in .my.cnf
        if (!isset($mycnf['password']) && isset($mycnf['pass'])) {
            $mycnf['password'] = $mycnf['pass'];
        }

        try {
            $this->dbh = @mysqli_connect(
                $mycnf['host'] ?? 'localhost',
                $mycnf['user'] ?? '',
                $mycnf['password'] ?? '',
                ''
            );
        } catch (\mysqli_sql_exception $e) {
            $this->set_error('MySQL Server Connection Failed: ' . $e->getMessage());
            return;
        }

        if ($this->dbh === false) {
            $this->set_error('MySQL Server Connection Failed');
            return;
        }
    }

    public function set_error($error) {
        $this->errors[] = sprintf('%s', $error);
    }

    public function get_errors() {
        if (count($this->errors) > 0) return $this->errors;
        return false;
    }

    public function clear_errors() {
        $this->errors = array();
    }

    public function get_errors_string() {
        if (count($this->errors) > 0) {
            $errors = '';
            foreach ($this->errors as $error) {
                $errors .= $error . "\n ";
            }
            return $errors;
        }
        return false;
    }

    public function get_version() {
        if ($this->daemon_binary === null) {
            return '11.0-dev';
        }
        if (!is_file($this->daemon_binary)) {
            $this->set_error($this->daemon_binary . ' is missing, unable to determine version');
            return false;
        }

        $output = shell_exec($this->daemon_binary . ' -v');
        if ($output === null) {
            $this->set_error('Failed to execute ' . $this->daemon_binary);
            return false;
        }

        $version = explode(':', $output, 2);
        return isset($version[1]) ? trim($version[1]) : trim($version[0]);
    }

    public function is_latest() {
        if (defined('WATCHMYSQL_DEV') && WATCHMYSQL_DEV) {
            return true;
        }

        $ctx = stream_context_create(['http' => [
            'header' => "User-Agent: WatchMySQL\r\n",
            'timeout' => 5
        ]]);
        $json = @file_get_contents('https://api.github.com/repos/konstantinosbotonakis/watchmysql/releases/latest', false, $ctx);
        if ($json === false) {
            return true; // Can't check, assume up to date
        }

        $release = @json_decode($json, true);
        if (!$release || !isset($release['tag_name'])) {
            return true;
        }

        $latest_version = ltrim($release['tag_name'], 'v');
        $current_version = $this->get_version();
        if ($current_version === false) return false;

        if (version_compare($current_version, $latest_version) == -1) return false;

        return true;
    }


    private function get_config_mycnf() {
        if (!is_file($this->mycnf_file)) {
            $this->set_error($this->mycnf_file . ' does not exist or is not accessible');
            return false;
        }

        $file_contents = @file($this->mycnf_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($file_contents === false) {
            $this->set_error('Failed to read ' . $this->mycnf_file . ' contents');
            return false;
        }

        $return = array();
        foreach ($file_contents as $file_line) {
            if (preg_match("/^#/", $file_line) || preg_match("/^\[/", $file_line) || strlen($file_line) == 0) continue;
            $parts = explode("=", $file_line, 2);
            if (count($parts) < 2) continue;
            $key = trim($parts[0]);
            $value = trim($parts[1]);
            if (!$key) continue;
            $value = preg_replace(array('/^"/', '/"$/'), '', $value);
            $return[$key] = $value;
        }

        if (count($return) > 0) return $return;

        $this->set_error('config file is empty');
        return false;
    }


    public function get_global_config() {
        $defaults = array(
            'automatic_updates' => '1',
            'notify_admin' => '1',
            'notify_user' => '1',
            'kill_connections' => '1',
            'connection_limit' => '10',
            'check_interval' => '900',
            'slow_query_threshold' => '30',
            'cpanel' => '1'
        );

        if (!is_file($this->config_file)) {
            $this->set_error($this->config_file . ' does not exist, setting default values');
            return $defaults;
        }

        $file_contents = @file($this->config_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($file_contents === false) {
            $this->set_error('Failed to read ' . $this->config_file . ', setting default values');
            return $defaults;
        }

        $config = array();
        foreach ($file_contents as $file_content) {
            if (preg_match("/^#/", $file_content) || strlen($file_content) == 0) continue;
            $parts = explode("=", $file_content, 2);
            if (count($parts) < 2) continue;
            $key = trim($parts[0]);
            $value = trim($parts[1]);
            if (!$key) continue;
            $config[$key] = $value;
        }

        // Merge with defaults for any missing keys
        $config = array_merge($defaults, $config);

        if (count($config) > 0) return $config;

        $this->set_error($this->config_file . ' is empty, setting default values');
        return $defaults;
    }

    public function save_global_config($config = array()) {
        $flat_config = '';

        // Automatic Updates
        $flat_config .= "automatic_updates=" . (($config['automatic_updates'] ?? '1') == '1' ? '1' : '0') . "\n";

        // Admin Notify
        $flat_config .= "notify_admin=" . (($config['notify_admin'] ?? '1') == '1' ? '1' : '0') . "\n";

        // User Notify
        $flat_config .= "notify_user=" . (($config['notify_user'] ?? '1') == '1' ? '1' : '0') . "\n";

        // Kill Connections
        $flat_config .= "kill_connections=" . (($config['kill_connections'] ?? '1') == '1' ? '1' : '0') . "\n";

        // Default Limit
        if (isset($config['connection_limit']) && is_numeric($config['connection_limit']) && $config['connection_limit'] >= 0) {
            $flat_config .= "connection_limit=" . $config['connection_limit'] . "\n";
        } else {
            $this->set_error("Invalid connection limit, setting to a default value of 10");
            $flat_config .= "connection_limit=10\n";
        }

        // Check Interval
        if (isset($config['check_interval']) && is_numeric($config['check_interval']) && $config['check_interval'] >= 300) {
            $flat_config .= "check_interval=" . $config['check_interval'] . "\n";
        } else {
            $this->set_error("Invalid check interval, setting to a default value of 900 seconds");
            $flat_config .= "check_interval=900\n";
        }

        // Slow Query Threshold
        if (isset($config['slow_query_threshold']) && is_numeric($config['slow_query_threshold']) && $config['slow_query_threshold'] >= 1) {
            $flat_config .= "slow_query_threshold=" . $config['slow_query_threshold'] . "\n";
        } else {
            $flat_config .= "slow_query_threshold=30\n";
        }

        // cPanel Server
        if (!defined('WATCHMYSQL_DEV') || !WATCHMYSQL_DEV) {
            $flat_config .= "cpanel=1\n";
        }

        $result = @file_put_contents($this->config_file, $flat_config, LOCK_EX);
        if (!$result) {
            $this->set_error('Failed to write to ' . $this->config_file);
            return false;
        }

        $this->reload_watchmysql();

        return true;
    }


    public function mysql_process_list() {
        if (!$this->dbh) return false;

        $query = "SHOW PROCESSLIST";
        $result = mysqli_query($this->dbh, $query);
        if ($result === false) {
            $this->set_error("ERROR MySQL - Process List");
            return false;
        }
        $data = array();
        while ($row = mysqli_fetch_assoc($result)) {
            $data[$row['Id']] = $row;
        }

        return $data;
    }


    public function mysql_kill_id($id) {
        if (!$this->dbh) return false;

        $query = sprintf("KILL %d", intval($id));
        $result = mysqli_query($this->dbh, $query);
        if ($result === false) {
            $this->set_error("ERROR MySQL - Kill ID");
            return false;
        }
        return true;
    }


    public function mysql_global_status($keys = null) {
        if (!$this->dbh) return false;

        if ($keys !== null) {
            $placeholders = implode(',', array_fill(0, count($keys), '?'));
            $query = "SHOW GLOBAL STATUS WHERE Variable_name IN (" . implode(',', array_map(function($k) { return "'" . mysqli_real_escape_string($this->dbh, $k) . "'"; }, $keys)) . ")";
        } else {
            $query = "SHOW GLOBAL STATUS";
        }

        $result = mysqli_query($this->dbh, $query);
        if ($result === false) return false;

        $data = array();
        while ($row = mysqli_fetch_assoc($result)) {
            $data[$row['Variable_name']] = $row['Value'];
        }
        return $data;
    }


    public function mysql_global_variables($keys = null) {
        if (!$this->dbh) return false;

        if ($keys !== null) {
            $query = "SHOW GLOBAL VARIABLES WHERE Variable_name IN (" . implode(',', array_map(function($k) { return "'" . mysqli_real_escape_string($this->dbh, $k) . "'"; }, $keys)) . ")";
        } else {
            $query = "SHOW GLOBAL VARIABLES";
        }

        $result = mysqli_query($this->dbh, $query);
        if ($result === false) return false;

        $data = array();
        while ($row = mysqli_fetch_assoc($result)) {
            $data[$row['Variable_name']] = $row['Value'];
        }
        return $data;
    }


    public function mysql_slow_processes($threshold = 30) {
        if (!$this->dbh) return false;

        $threshold = intval($threshold);
        $query = "SELECT * FROM INFORMATION_SCHEMA.PROCESSLIST WHERE TIME > $threshold AND COMMAND != 'Sleep' ORDER BY TIME DESC";
        $result = mysqli_query($this->dbh, $query);
        if ($result === false) {
            $this->set_error("ERROR MySQL - Slow Processes Query");
            return false;
        }

        $data = array();
        while ($row = mysqli_fetch_assoc($result)) {
            $data[$row['ID']] = $row;
        }
        return $data;
    }


    public function get_user_connection_summary($process_list = null) {
        if ($process_list === null) {
            $process_list = $this->mysql_process_list();
        }
        if (!is_array($process_list)) return array();

        $global_config = $this->get_global_config();
        $user_limits = $this->get_user_limits();
        $package_limits = $this->get_package_limits();
        $users = $this->get_user_list();

        // Count connections per cPanel user
        $counts = array();
        foreach ($process_list as $process) {
            $mysql_user = $process['User'];
            if (($global_config['cpanel'] ?? '0') == '1') {
                $parts = explode('_', $mysql_user, 2);
                $cpuser = $parts[0];
            } else {
                $cpuser = $mysql_user;
            }
            if (!isset($counts[$cpuser])) $counts[$cpuser] = 0;
            $counts[$cpuser]++;
        }

        // Build summary with effective limits
        $summary = array();
        foreach ($counts as $user => $count) {
            $limit = intval($global_config['connection_limit'] ?? 10);
            $source = 'Global';

            // Check package limit
            if (isset($users[$user]['PLAN']) && isset($package_limits[$users[$user]['PLAN']])) {
                $pkg_limit = intval($package_limits[$users[$user]['PLAN']]);
                if ($pkg_limit > 0) {
                    $limit = $pkg_limit;
                    $source = 'Package';
                }
            }

            // Check user limit (highest priority)
            if (isset($user_limits[$user])) {
                $usr_limit = intval($user_limits[$user]);
                if ($usr_limit > 0) {
                    $limit = $usr_limit;
                    $source = 'User';
                }
            }

            $status = 'ok';
            if ($limit > 0) {
                $ratio = $count / $limit;
                if ($ratio >= 1.0) $status = 'danger';
                elseif ($ratio >= 0.75) $status = 'warning';
            }

            $summary[$user] = array(
                'connections' => $count,
                'limit' => $limit,
                'source' => $source,
                'status' => $status,
                'package' => $users[$user]['PLAN'] ?? '-'
            );
        }

        // Sort by connections descending
        uasort($summary, function($a, $b) {
            return $b['connections'] - $a['connections'];
        });

        return $summary;
    }


    public function get_user_list() {
        $users = array();

        if (is_dir($this->users_dir)) {
            if ($dh = opendir($this->users_dir)) {
                while (($file = readdir($dh)) !== false) {
                    if ($file === '.' || $file === '..') continue;
                    if (!is_file($this->users_dir . "/" . $file)) continue;
                    // In dev mode, skip posix check
                    if (!defined('WATCHMYSQL_DEV') || !WATCHMYSQL_DEV) {
                        if (!posix_getpwnam($file)) continue;
                    }
                    $user_file_contents = @file($this->users_dir . '/' . $file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                    if ($user_file_contents === false) continue;
                    foreach ($user_file_contents as $line) {
                        $parts = explode('=', $line, 2);
                        if (count($parts) < 2) continue;
                        $users[$file][trim($parts[0])] = trim($parts[1]);
                    }
                }
                closedir($dh);
            }
        }
        return $users;
    }


    public function get_package_list() {
        $packages = array();

        if (is_dir($this->packages_dir)) {
            if ($dh = opendir($this->packages_dir)) {
                while (($file = readdir($dh)) !== false) {
                    if ($file === '.' || $file === '..') continue;
                    if (!is_file($this->packages_dir . "/" . $file)) continue;
                    $file_contents = @file($this->packages_dir . '/' . $file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                    if ($file_contents === false) continue;
                    foreach ($file_contents as $line) {
                        $parts = explode('=', $line, 2);
                        if (count($parts) < 2) continue;
                        $packages[$file][trim($parts[0])] = trim($parts[1]);
                    }
                }
                closedir($dh);
            }
        }
        return $packages;
    }


    public function get_user_limits() {
        $userlimits = array();

        $contents = @file($this->userlimits_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($contents === false) return $userlimits;

        foreach ($contents as $content) {
            if (preg_match("/^#/", $content) || strlen($content) == 0) continue;
            $parts = explode("=", $content, 2);
            if (count($parts) < 2) continue;
            $key = trim($parts[0]);
            if (!$key) continue;
            $userlimits[$key] = trim($parts[1]);
        }
        return $userlimits;
    }


    public function get_package_limits() {
        $package_limits = array();

        $contents = @file($this->pkg_limits_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($contents === false) return $package_limits;

        foreach ($contents as $content) {
            if (preg_match("/^#/", $content) || strlen($content) == 0) continue;
            $parts = explode("=", $content, 2);
            if (count($parts) < 2) continue;
            $key = trim($parts[0]);
            if (!$key) continue;
            $package_limits[$key] = trim($parts[1]);
        }

        return $package_limits;
    }


    public function remove_user_limit($user = false) {
        if ($user === false) {
            $this->set_error("remove_user_limit function requires a username");
            return false;
        }

        $user_limits = $this->get_user_limits();

        if (!array_key_exists($user, $user_limits)) {
            $this->set_error("Username " . $user . " does not have a limit set, nothing to remove!");
            return false;
        }

        unset($user_limits[$user]);

        $result = $this->save_user_limits($user_limits);
        if ($result === false) return false;

        return true;
    }


    public function remove_package_limit($package = false) {
        if ($package === false) {
            $this->set_error("failed to remove package limit, package name is missing");
            return false;
        }

        $package_limits = $this->get_package_limits();

        if (!array_key_exists($package, $package_limits)) {
            $this->set_error("Package " . $package . " does not have a limit set, nothing to remove");
            return false;
        }

        unset($package_limits[$package]);

        $result = $this->save_package_limits($package_limits);
        if ($result === false) return false;

        return true;
    }


    public function save_user_limits($limits = false) {
        if ($limits === false) {
            $this->set_error("save_user_limits function requires an array of user limits");
            return false;
        }

        $users = $this->get_user_list();

        $flat = '';
        foreach ($limits as $key => $value) {
            if (!array_key_exists($key, $users)) continue;
            if (!is_numeric($value)) continue;
            $flat .= $key . "=" . $value . "\n";
        }

        $result = @file_put_contents($this->userlimits_file, $flat, LOCK_EX);
        if ($result === false) {
            $this->set_error('Failed to write new limits file to ' . $this->userlimits_file);
            return false;
        }

        $this->reload_watchmysql();

        return true;
    }


    public function save_package_limits($limits = false) {
        if ($limits === false) {
            $this->set_error("save_package_limits function requires an array of package limits");
            return false;
        }

        $packages = $this->get_package_list();

        $flat = '';
        foreach ($limits as $key => $value) {
            if (!array_key_exists($key, $packages)) continue;
            if (!is_numeric($value)) continue;
            $flat .= $key . "=" . $value . "\n";
        }

        $result = @file_put_contents($this->pkg_limits_file, $flat, LOCK_EX);
        if ($result === false) {
            $this->set_error('Failed to write new limits file to ' . $this->pkg_limits_file);
            return false;
        }

        $this->reload_watchmysql();

        return true;
    }


    public function add_user_limit($user = false, $limit = false) {
        if ($user === false) {
            $this->set_error("add_user_limit function requires username param");
            return false;
        }

        $user_list = $this->get_user_list();
        if (!array_key_exists($user, $user_list)) {
            $this->set_error("Invalid user, you can only set limits for users who exist");
            return false;
        }

        if ($limit === false) {
            $this->set_error("add_user_limit function requires limit param");
            return false;
        }

        if (!is_numeric($limit)) {
            $this->set_error("Limit must be a numeric value");
            return false;
        }

        $user_limits = $this->get_user_limits();
        $user_limits[$user] = $limit;

        $result = $this->save_user_limits($user_limits);
        if ($result === false) return false;

        return true;
    }


    public function add_package_limit($package = false, $limit = false) {
        if ($package === false) {
            $this->set_error("failed to add package limit, package name missing");
            return false;
        }

        $package_list = $this->get_package_list();
        if (!array_key_exists($package, $package_list)) {
            $this->set_error("package name " . $package . " does not exist");
            return false;
        }

        if ($limit === false) {
            $this->set_error("failed to add package limit, limit missing");
            return false;
        }

        if (!is_numeric($limit)) {
            $this->set_error("failed to add package limit, package limit must be numeric");
            return false;
        }

        $package_limits = $this->get_package_limits();
        $package_limits[$package] = $limit;

        $result = $this->save_package_limits($package_limits);
        if ($result === false) return false;

        return true;
    }


    // --- Whitelist ---

    public function get_whitelist() {
        $whitelist = array();

        $contents = @file($this->whitelist_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($contents === false) return $whitelist;

        foreach ($contents as $line) {
            $line = trim($line);
            if (preg_match("/^#/", $line) || strlen($line) == 0) continue;
            $whitelist[] = $line;
        }
        return $whitelist;
    }

    public function save_whitelist($whitelist = array()) {
        $flat = '';
        foreach ($whitelist as $user) {
            $user = trim($user);
            if ($user) $flat .= $user . "\n";
        }

        $result = @file_put_contents($this->whitelist_file, $flat, LOCK_EX);
        if ($result === false) {
            $this->set_error('Failed to write whitelist to ' . $this->whitelist_file);
            return false;
        }

        $this->reload_watchmysql();
        return true;
    }

    public function add_to_whitelist($user) {
        if (!$user) {
            $this->set_error("add_to_whitelist requires a username");
            return false;
        }

        $user_list = $this->get_user_list();
        if (!array_key_exists($user, $user_list)) {
            $this->set_error("Invalid user, you can only whitelist users who exist");
            return false;
        }

        $whitelist = $this->get_whitelist();
        if (in_array($user, $whitelist)) {
            $this->set_error("User " . $user . " is already whitelisted");
            return false;
        }

        $whitelist[] = $user;
        return $this->save_whitelist($whitelist);
    }

    public function remove_from_whitelist($user) {
        if (!$user) {
            $this->set_error("remove_from_whitelist requires a username");
            return false;
        }

        $whitelist = $this->get_whitelist();
        $new_whitelist = array_filter($whitelist, function($u) use ($user) {
            return $u !== $user;
        });

        if (count($new_whitelist) === count($whitelist)) {
            $this->set_error("User " . $user . " is not in the whitelist");
            return false;
        }

        return $this->save_whitelist(array_values($new_whitelist));
    }


    // --- History Log ---

    public function log_event($user, $connections, $limit, $action) {
        $line = date('Y-m-d H:i:s') . '|' . $user . '|' . $connections . '|' . $limit . '|' . $action . "\n";
        @file_put_contents($this->history_file, $line, FILE_APPEND | LOCK_EX);
    }

    public function get_history($limit = 500) {
        $history = array();

        if (!is_file($this->history_file)) return $history;

        $lines = @file($this->history_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false) return $history;

        $lines = array_reverse($lines);
        $count = 0;

        foreach ($lines as $line) {
            if ($count >= $limit) break;
            $parts = explode('|', $line, 5);
            if (count($parts) < 5) continue;
            $history[] = array(
                'timestamp' => $parts[0],
                'user' => $parts[1],
                'connections' => $parts[2],
                'limit' => $parts[3],
                'action' => $parts[4]
            );
            $count++;
        }

        return $history;
    }


    // --- Effective Limits for All Users ---

    public function get_all_user_effective_limits() {
        $users = $this->get_user_list();
        $user_limits = $this->get_user_limits();
        $package_limits = $this->get_package_limits();
        $global_config = $this->get_global_config();
        $process_list = $this->mysql_process_list();
        $whitelist = $this->get_whitelist();

        // Count connections per cPanel user
        $conn_counts = array();
        if (is_array($process_list)) {
            foreach ($process_list as $process) {
                $mysql_user = $process['User'];
                if (($global_config['cpanel'] ?? '0') == '1') {
                    $parts = explode('_', $mysql_user, 2);
                    $cpuser = $parts[0];
                } else {
                    $cpuser = $mysql_user;
                }
                if (!isset($conn_counts[$cpuser])) $conn_counts[$cpuser] = 0;
                $conn_counts[$cpuser]++;
            }
        }

        $result = array();
        foreach ($users as $username => $details) {
            $limit = intval($global_config['connection_limit'] ?? 10);
            $source = 'Global';

            $plan = $details['PLAN'] ?? '-';

            if (isset($package_limits[$plan])) {
                $pkg_limit = intval($package_limits[$plan]);
                if ($pkg_limit > 0) {
                    $limit = $pkg_limit;
                    $source = 'Package';
                }
            }

            if (isset($user_limits[$username])) {
                $usr_limit = intval($user_limits[$username]);
                if ($usr_limit > 0) {
                    $limit = $usr_limit;
                    $source = 'User';
                }
            }

            $connections = $conn_counts[$username] ?? 0;
            $is_whitelisted = in_array($username, $whitelist);

            $result[$username] = array(
                'package' => $plan,
                'connections' => $connections,
                'limit' => $limit,
                'source' => $source,
                'has_explicit_limit' => isset($user_limits[$username]),
                'whitelisted' => $is_whitelisted
            );
        }

        // Sort: users with connections first, then alphabetically
        uasort($result, function($a, $b) {
            if ($b['connections'] !== $a['connections']) return $b['connections'] - $a['connections'];
            return 0;
        });

        return $result;
    }


    // --- Daemon Control ---

    public function get_watchmysql_pid() {
        if ($this->pidof_binary === null) {
            return '99999'; // Dev mode mock PID
        }
        if (!is_file($this->pidof_binary)) {
            $this->set_error($this->pidof_binary . ' is missing');
            return false;
        }

        $result = shell_exec($this->pidof_binary . ' -s watchmysql');
        if ($result === null) return false;
        $result = trim($result);
        if (is_numeric($result)) return $result;

        return false;
    }


    public function start_watchmysql() {
        if ($this->daemon_binary === null) {
            return '99999'; // Dev mode
        }
        if (!is_file('/etc/init.d/watchmysql')) {
            $this->set_error('/etc/init.d/watchmysql is missing');
            return false;
        }

        shell_exec('/etc/init.d/watchmysql start');

        $watchmysql_pid = $this->get_watchmysql_pid();
        if ($watchmysql_pid) return $watchmysql_pid;

        $this->set_error('Failed to start watchmysql daemon');
        return false;
    }


    public function reload_watchmysql() {
        if ($this->pidof_binary === null) return true; // Dev mode
        $watchmysql_pid = $this->get_watchmysql_pid();
        if (!$watchmysql_pid) return false;

        if (function_exists('posix_kill')) {
            $result = posix_kill(intval($watchmysql_pid), 10);
            if (!$result) return false;
        }

        return true;
    }


    // --- Test Email ---

    public function send_test_email() {
        if (defined('WATCHMYSQL_DEV') && WATCHMYSQL_DEV) {
            @file_put_contents($this->history_file . '.test_email', date('Y-m-d H:i:s') . " Test email sent\n", FILE_APPEND);
            return true;
        }

        if (!is_file($this->sendmail_binary)) {
            $this->set_error($this->sendmail_binary . ' is not available');
            return false;
        }

        $hostname = gethostname() ?: 'unknown';
        $admin_email = 'root@' . $hostname;

        // Try to get admin email from wwwacct.conf
        if (is_file('/etc/wwwacct.conf')) {
            $lines = @file('/etc/wwwacct.conf', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            if ($lines) {
                foreach ($lines as $line) {
                    $parts = preg_split('/\s+/', $line, 2);
                    if (count($parts) >= 2 && $parts[0] === 'CONTACTEMAIL') {
                        $admin_email = trim($parts[1]);
                        break;
                    }
                }
            }
        }

        $message = "To: <$admin_email>\n";
        $message .= "Subject: [WatchMySQL] Test Notification\n\n";
        $message .= "This is a test notification from WatchMySQL on $hostname.\n";
        $message .= "If you received this email, notifications are working correctly.\n";
        $message .= "Sent at: " . date('Y-m-d H:i:s') . "\n";

        $handle = @popen($this->sendmail_binary . ' -t', 'w');
        if (!$handle) {
            $this->set_error('Failed to execute sendmail');
            return false;
        }

        fwrite($handle, $message);
        $status = pclose($handle);

        if ($status !== 0) {
            $this->set_error('sendmail exited with status ' . $status);
            return false;
        }

        return true;
    }


    public function license_nag() {
        if (defined('WATCHMYSQL_DEV') && WATCHMYSQL_DEV) {
            return false;
        }

        $serverIp = @file_get_contents("https://licsrv.ndchost.com/ip.php");
        if ($serverIp === false) return false;

        $result = @file_get_contents("https://verify.cpanel.net/verifyFeed.cgi?ip=" . $serverIp);
        if ($result === false) return false;

        try {
            $licenseXML = new SimpleXMLElement($result);
            $attrs = $licenseXML->license->attributes();
            if ((string)$attrs['group'] !== 'NDCHost') {
                if (preg_match('/external/i', (string)$attrs['package'])) {
                    return true;
                }
            }
        } catch (\Exception $e) {
            return false;
        }

        return false;
    }


    public function format_uptime($seconds) {
        $days = floor($seconds / 86400);
        $hours = floor(($seconds % 86400) / 3600);
        $minutes = floor(($seconds % 3600) / 60);

        $parts = array();
        if ($days > 0) $parts[] = $days . 'd';
        if ($hours > 0) $parts[] = $hours . 'h';
        if ($minutes > 0) $parts[] = $minutes . 'm';

        return implode(' ', $parts) ?: '0m';
    }
}
