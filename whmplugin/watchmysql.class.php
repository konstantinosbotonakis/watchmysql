<?PHP
## Security Check!  Do not allow reseller access!!!
if($_ENV["REMOTE_USER"] != "root") die('Only root is allowed to access this plug-in');

class watchmysql {

    private $errors = array();

    private $global_config = array();
    private $mycnf = array();

    private $dbh = false;

    public function __construct() {
        $mycnf = $this->get_config_mycnf();
        if($mycnf === false) die( $this->get_errors_string() );

        $global_config = $this->get_global_config();
        if($global_config === false) die ( $this->get_errors_string() );

        // bugfix: Newer version of mysql use password directives now instead of pass in .my.cnf
        if(!isset($mycnf['password']) && isset($mycnf['pass'])) $mycnf['password'] = $mycnf['pass'];

        $this->dbh = @mysqli_connect('localhost', $mycnf['user'], $mycnf['password'], '');
        if($this->dbh === false) {
            $this->set_error('MySQL Server Connection Failed');
            return false;
        }
    }

    public function set_error($error) { $this->errors[] = sprintf('%s', $error); }

    public function get_errors() { if(count($this->errors) > 0) return $this->errors; return false; }

    public function clear_errors() { $this->errors = array(); }

    public function get_errors_string() {
        if(count($this->errors) > 0) {
            foreach($this->errors as $error) $errors .= $error . "\n ";
            return $errors;
        }
        return false;
    }

    public function get_version() {
        if(is_file('/usr/sbin/watchmysql') === false) {
            $this->set_error('/usr/sbin/watchmysql is missing, unable to determine version');
            return false;
        }

        $output = `/usr/sbin/watchmysql -v`;

        $version = explode(':', $output, 2);
        return trim($version[1]);
    }

    public function is_latest() {
        $latest_version = @file_get_contents('http://download.ndchost.com/watchmysql/version.php');
        if($latest_version === false) {
            $this->set_error('Failed to get latest version information from download server');
            return false;
        }
        $current_version = $this->get_version();
        if($current_version === false) return false;

        if(version_compare($current_version,$latest_version) == -1 ) return false;

        return true;
    }


    private function get_config_mycnf() {
        if(!is_file('/root/.my.cnf')) {
            $this->set_error('/root/.my.cnf does not exist or is not accessible');
            return false;
        }

        $file_contents = @file('/root/.my.cnf', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES );
        if($file_contents === false) {
            $this->set_error('Failed to read /root/.my.cnf contents');
            return false;
        }

        $return = array();
        foreach($file_contents as $file_line) {
            if(preg_match("/^#/", $file_line) or strlen($file_line) == 0)continue;
            list($key,$value) = explode("=",$file_line,2);
            if(!$key or !$value) continue;
            $value = preg_replace(array( '/^"/', '/"$/' ), '', $value);
            $return[$key] = $value;
        }

        if(count($return) > 0) return $return;

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
            'cpanel' => '1'
        );
        if(!is_file('/etc/watchmysql.config')) {
            $this->set_error('/etc/watchmysql.config does not exist, setting default values');
            return $defaults;
        }

        $file_contents = @file('/etc/watchmysql.config', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES );
        if($file_contents === false) {
            $this->set_error('Failed to read /etc/watchmysql.config, setting default values');
            return $defaults;
        }

        $config = array();
        foreach($file_contents as $file_content) {
            if(preg_match("/^#/", $file_content) or strlen($file_content) == 0) continue;
            list($key, $value) = explode("=", $file_content, 2);
            if(!$key) continue;
            $config[$key] = $value;
        }

        if(count($config) > 0) return $config;

        $this->set_error('/etc/watchmysql.config is empty, setting default values;');
        return $defaults;
    }

    public function save_global_config($config=array()) {
        $flat_config = '';
        // Automatic Updates Validation
        if(array_key_exists('automatic_updates', $config)) {
            if($config['automatic_updates'] == '1') {
                $flat_config .= "automatic_updates=1\n";
            } else {
                $flat_config .= "automatic_updates=0\n";
            }
        } else {
            $flat_config .= "automatic_updates=1\n";
        }

        // Admin Notify Validation
        if(array_key_exists('notify_admin', $config)) {
            if($config['notify_admin'] == '1') {
                $flat_config .= "notify_admin=1\n";
            } else {
                $flat_config .= "notify_admin=0\n";
            }
        } else {
            $flat_config .= "notify_admin=1\n";
        }

        // User Notify Validation
        if(array_key_exists('notify_user', $config)) {
            if($config['notify_user'] == '1') {
                $flat_config .= "notify_user=1\n";
            } else {
                $flat_config .= "notify_user=0\n";
            }
        } else {
            $flat_config .= "notify_user=1\n";
        }

        // Kill Connections Validation
        if(array_key_exists('kill_connections', $config)) {
            if($config['kill_connections'] == '1') {
                $flat_config .= "kill_connections=1\n";
            } else {
                $flat_config .= "kill_connections=0\n";
            }
        } else {
            $flat_config .= "kill_connections=1\n";
        }

        // Default Limit Validation
        if(array_key_exists('connection_limit', $config)) {
            if(is_numeric($config['connection_limit']) && $config['connection_limit'] >= 0) {
                $flat_config .= "connection_limit=" . $config['connection_limit'] . "\n";
            } else {
                $this->set_error("Invalid connection limit, setting to a default value of 10");
                $flat_config .= "connection_limit=10\n";
            }
        } else {
            $this->set_error("No connection limit set, setting to a default value of 10");
            $flat_config .= "connection_limit=10\n";
        }

        // Check Interval Validation
        if(array_key_exists('check_interval', $config)) {
            if(is_numeric($config['check_interval']) && $config['check_interval'] >= 300) {
                $flat_config .= "check_interval=" . $config['check_interval'] . "\n";
            } else {
                $this->set_error("Invalid check interval, setting to a default value of 900 seconds");
                $flat_config .= "check_interval=900\n";
            }
        } else {
            $this->set_error("No check_interval set, setting to a default value of 900 seconds");
            $flat_config .= "check_interval=900\n";
        }

        // cPanel Server
        $flat_config .= "cpanel=1\n";

        $result = @file_put_contents('/etc/watchmysql.config', $flat_config, LOCK_EX);
        if(!$result) {
            $this->set_error('Failed to write to /etc/watchmysql.config');
            return false;
        }

        $this->reload_watchmysql();

        return true;
    }


    public function mysql_process_list() {
        if(!$this->dbh) return false;

        $query = "SHOW PROCESSLIST";
        $result = mysqli_query($this->dbh, $query);
        if($result === false) {
            $this->set_error("ERROR MySQL - Process List");
            return false;
        }
        $data = array();
        while($row = mysqli_fetch_assoc($result)) $data[$row['Id']] = $row;

        return $data;
    }


    public function mysql_kill_id($id) {
        if(!$this->dbh) return false;

        $query = sprintf("kill %d", $id);
        $result = mysqli_query($this->dbh, $query);
        if($result === false) {
            $this->set_error("ERROR MySQL - Kill ID");
            return false;
        }
        return true;
    }


    public function get_user_list() {
        $users = array();

        if(is_dir("/var/cpanel/users")) {
            if($dh = opendir("/var/cpanel/users")) {
                while(($file = readdir($dh)) !== false) {
                    if(!is_file("/var/cpanel/users/" . $file))continue;
                    if(!posix_getpwnam($file))continue;
                    $user_file_contents = @file('/var/cpanel/users/' . $file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES );
                    foreach($user_file_contents as $line) {
                        list($d,$v) = explode('=', $line, 2);
                        $users[$file][$d] = $v;
                    }
                }
                closedir($dh);
            }
        }
        return $users;
    }


    public function get_package_list() {
        $packages = array();

        if(is_dir("/var/cpanel/packages/")) {
            if($dh = opendir("/var/cpanel/packages")) {
                while(($file = readdir($dh)) !== false) {
                    if(!is_file("/var/cpanel/packages/" . $file))continue;
                    $file_contents = @file('/var/cpanel/packages/' . $file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES );
                    foreach($file_contents as $line) {
                        list($d,$v) = explode('=', $line, 2);
                        $packages[$file][$d] = $v;
                    }
                }
            }
        }
        return $packages;
    }


    public function get_user_limits() {
        $userlimits = array();

        $contents = @file('/etc/watchmysql.userlimits', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES );
        if($contents === false) return $userlimits;

        foreach($contents as $content) {
            if(preg_match("/^#/", $content) or strlen($content) == 0)continue;
            list($key,$value) = explode("=", $content, 2);
            if(!$key) continue;
            $userlimits[$key] = $value;
        }
        return $userlimits;
    }


    public function get_package_limits() {
        $package_limits = array();

        $contents = @file('/etc/watchmysql.packagelimits', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES );
        if($contents === false) return $package_limits;

        foreach($contents as $content) {
            if(preg_match("/^#/", $content) or strlen($content) == 0)continue;
            list($key,$value) = explode("=", $content, 2);
            if(!$key) continue;
            $package_limits[$key] = $value;
        }

        return $package_limits;
    }


    public function remove_user_limit($user=false) {
        if($user === false) {
            $this->set_error("remove_user_limit function requires a username");
            return false;
        }

        $user_limits = $this->get_user_limits();
        if($user_limits === false) return false;

        if(!array_key_exists($user,$user_limits)) {
            $this->set_error("Username " . $user . " does not have a limit set, nothing to remove!");
            return false;
        }

        $user_limits = $this->remove_key_from_array($user, $user_limits);

        $result = $this->save_user_limits($user_limits);
        if($result === false) return false;

        return true;
    }


    public function remove_package_limit($package=false) {
        if($package === false) {
            $this->set_error("failed to remove pacakge limit, package name is missing");
            return false;
        }

        $package_limits = $this->get_package_limits();
        if($package_limits === false) return false;

        if(!array_key_exists($package, $package_limits)) {
            $this->set_error("Package " . $package . " does not have a limit set, nothing to remove");
            return false;
        }

        $package_limits = $this->remove_key_from_array($package, $package_limits);

        $result = $this->save_package_limits($package_limits);
        if($result === false) return false;

        return true;
    }


    public function remove_key_from_array($key,$array) {
        $new_array = array();
        foreach($array as $k => $v) {
            if($k == $key) continue;
            $new_array[$k] = $v;
        }
        return $new_array;
    }


    public function save_user_limits($limits=false) {
        if($limits === false) {
            $this->set_error("save_user_limits function requires an array of user limits");
            return false;
        }

        $users = $this->get_user_list();

        $flat = '';
        foreach($limits as $key => $value) {
            if(!array_key_exists($key, $users)) continue;
            if(!is_numeric($value)) continue;
            $flat .= $key . "=" . $value . "\n";
        }

        $result = @file_put_contents('/etc/watchmysql.userlimits', $flat, LOCK_EX );
        if($result === false) {
            $this->set_error('Failed to write new limits file to /etc/watchmysql.userlimits');
            return false;
        }

        $this->reload_watchmysql();

        return true;
    }


    public function save_package_limits($limits=false) {
        if($limits === false) {
            $this->set_error("save_package_limits function requires an array of package limits");
            return false;
        }

        $packages = $this->get_package_list();

        $flat = '';
        foreach($limits as $key => $value) {
            if(!array_key_exists($key, $packages)) continue;
            if(!is_numeric($value)) continue;
            $flat .= $key . "=" . $value . "\n";
        }

        $result = @file_put_contents('/etc/watchmysql.packagelimits', $flat, LOCK_EX );
        if($result === false) {
            $this->set_error('Failed to write new limits file to /etc/watchmysql.packagelimits');
            return false;
        }

        $this->reload_watchmysql();

        return true;
    }


    public function add_user_limit($user=false,$limit=false) {
        if($user === false) {
            $this->set_error("add_user_limit function requires username param");
            return false;
        }

        $user_list = $this->get_user_list();
        if(!array_key_exists($user, $user_list)) {
            $this->set_error("Invalid user, you can only set limits for users who exist");
            return false;
        }

        if($limit === false) {
            $this->set_error("add_user_limit function requires limit param");
            return false;
        }

        if(!is_numeric($limit)) {
            $this->set_error("Limit must be a numeric value");
            return false;
        }

        $user_limits = $this->get_user_limits();
        if($user_limits === false) return false;

        $user_limits[$user] = $limit;

        $result = $this->save_user_limits($user_limits);
        if($result === false) return false;

        return true;
    }


    public function add_package_limit($package=false,$limit=false) {
        if($package === false) {
            $this->set_error("failed to add package limit, package name missing");
            return false;
        }

        $package_list = $this->get_package_list();
        if(!array_key_exists($package, $package_list)) {
            $this->set_error("package name " . $package . " does not exist");
            return false;
        }

        if($limit === false) {
            $this->set_error("failed to add package limit, limit missing");
            return false;
        }

        if(!is_numeric($limit)) {
            $this->set_error("failed to add package limit, package limit must be numeric");
            return false;
        }

        $package_limits = $this->get_package_limits();
        if($package_limits === false) return false;

        $package_limits[$package] = $limit;

        $result = $this->save_package_limits($package_limits);
        if($result === false) return false;

        return true;
    }


    public function get_watchmysql_pid() {
        if(!is_file('/sbin/pidof')) {
            $this->set_error('/sbin/pidof is missing');
            return false;
        }

        $result = `/sbin/pidof -s watchmysql`;
        $result = trim($result);
        if(is_numeric($result)) return $result;

        return false;
    }


    public function start_watchmysql() {
        if(!is_file('/etc/init.d/watchmysql')) {
            $this->set_error('/etc/init.d/watchmysql is missing');
            return false;
        }

        $result = `/etc/init.d/watchmysql start`;

        $watchmysql_pid = $this->get_watchmysql_pid();
        if($watchmysql_pid) return $watchmysql_pid;

        $this->set_error('Failed to start watchmysql daemon');
        return false;
    }


    public function reload_watchmysql() {
        $watchmysql_pid = $this->get_watchmysql_pid();
        if(!$watchmysql_pid) return false;

        $result = posix_kill($watchmysql_pid, 10);
        if(!$result) return false;

        return true;
    }

    public function license_nag() {
        $serverIp = @file_get_contents("http://licsrv.ndchost.com/ip.php");
        if($serverIp === false) return true;

        $result = @file_get_contents("http://verify.cpanel.net/verifyFeed.cgi?ip=".$serverIp);
        if($result === false) return true;

        $licenseXML = new SimpleXMLElement($result);
        if($licenseXML->license->attributes['group'] != 'NDCHost') {
            if(preg_match('/external/i',$licenseXML->license->attributes['package'])) {
                return true;
            }
        }
        return false;
    }
}
