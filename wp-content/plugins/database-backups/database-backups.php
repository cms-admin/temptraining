<?php
/*
Plugin Name: Database Backups
Description: Simple Plugin that allows do backup of database tables. Manually or auto. Простой плагин, который позволяет делать бэкапы вашей базы данных вручную, либо в атоматическом режиме.
Version: 1.2.2.6
Author: AGriboed
Text Domain: database-backups
Domain Path: /languages
Author URI: https://v1rus.ru/
License: GPL2
*/

define('PLUGIN_LINK', 'tools.php?page=database-backups');

/**
 * Class DatabaseBackups
 */
class DatabaseBackups
{
    /**
     *    Holding the singleton instance
     */
    private static $_instance=null;
    /**
     * @var $db PDO
     * @var $tables - array of all tables of database
     * @var $dir string - directory wich will be saved backup
     */
    private $db, $tables, $dir;

    /**
     * Deleted trash before save content.
     * @var array
     */
    private $deleted_comments=array();
    private $deleted_posts=array();

    /**
     * List of backups
     * @var array
     */
    private $backups=array();

    /**
     * @return DatabaseBackups
     */
    public static function instance()
    {
        if (is_null(self::$_instance))
            self::$_instance=new self();
        return self::$_instance;
    }

    /**
     *  Prevent from creating more instances
     */
    private function __clone()
    {
    }

    /**
     *  Prevent from creating more than one instance
     */
    private function __construct()
    {
        add_action('init', array(&$this, 'cronCheck'));

        $dir=DatabaseBackupsOptions::instance()->getOption('directory');
        $wp_upload_dir=wp_upload_dir();

        if (!empty($dir)) {
            $this->url=$wp_upload_dir['baseurl'] . '/' . $dir . '/';
            $this->dir=$wp_upload_dir['basedir'] . '/' . $dir . '/';
        } else {
            $this->url=$wp_upload_dir['baseurl'] . '/database-backups/';
            $this->dir=$wp_upload_dir['basedir'] . '/database-backups/';
        }
    }

    /**
     * Check tasks from wp cron
     *
     */
    public function cronCheck()
    {
        add_filter('cron_schedules', array(&$this, 'addSchedules'));

        if (!DatabaseBackupsOptions::instance()->getOption('cron'))
            return false;

        add_action('database-backups-cron', array(&$this, 'doBackup'));

        if (!wp_next_scheduled('database-backups-cron')) {
            wp_schedule_event(time(), DatabaseBackupsOptions::instance()->getOption('cron'), 'database-backups-cron');
            self::checkOldBackups();
        }

        return true;
    }

    /**
     * Add schedules to wordpress
     * @param $schedules
     * @return mixed
     */
    public function addSchedules($schedules)
    {
        $schedules['weekly']=array(
                'interval'=>60 * 60 * 24 * 7,
                'display'=>__('Once Weekly', 'database-backups')
        );
        $schedules['weekly_twice']=array(
                'interval'=>round((60 * 60 * 24 * 7) / 2),
                'display'=>__('Twice Weekly', 'database-backups')
        );
        $schedules['monthly']=array(
                'interval'=>60 * 60 * 24 * 7 * 31,
                'display'=>__('Once Monthly', 'database-backups')
        );
        $schedules['monthly_twice']=array(
                'interval'=>round((60 * 60 * 24 * 7 * 31) / 2),
                'display'=>__('Twice Monthly', 'database-backups')
        );
        return $schedules;
    }

    /**
     *
     * @return bool
     */
    private function _getTables()
    {
        $query=$this->db->query('SHOW TABLES')->fetchAll();

        if (DatabaseBackupsOptions::instance()->getOption('prefix')) {
            foreach ($query as $key=>$table) {
                if (substr($table[0], 0, strlen(DatabaseBackupsOptions::instance()->getTablePrefix())) !==
                        DatabaseBackupsOptions::instance()->getTablePrefix()
                )
                    unset($query[$key]);
            }
        }

        foreach ($query as $table) {
            $name=$table[0];
            $this->tables[$name]['name']=$name;
            $this->tables[$name]['create']=$this->_getColumns($name);
            $table_count=$this->_getDataCount($name);
            $limit=DatabaseBackupsOptions::instance()->getOption('limit');
            $limit=($limit > 0) ? $limit : 1000;

            if ($table_count > $limit) // if table have record more than limit
            {
                $steps=ceil($table_count / $limit);
                $this->tables[$name]['data_raw']="";

                for ($step=1; $step <= $steps; $step++) {
                    $start=$step * $limit - $limit;
                    $this->tables[$name]['data_raw'].=$this->_getData($name, $start, $limit);
                }
            } else
                $this->tables[$name]['data_raw']=$this->_getData($name, 0, $table_count);
        }
    }

    /**
     * Get all columns of table
     * @param $tableName
     * @return mixed
     */
    private function _getColumns($tableName)
    {
        $query=$this->db->query('SHOW CREATE TABLE ' . $tableName);
        $q=$query->fetchAll();
        $q[0][1]=preg_replace("/AUTO_INCREMENT=[\w]*./", '', $q[0][1]);
        return $q[0][1];
    }

    /**
     * Get count of records in table
     * @param $tableName
     * @return mixed
     */
    private function _getDataCount($tableName)
    {
        return $this->db->query('SELECT count(*) FROM ' . $tableName)->fetchColumn();
    }

    /**
     * Get table data using limits and write it into the var as SQL
     * @param $tableName
     * @param int $start
     * @param int $limit
     * @return string
     */
    private function _getData($tableName, $start=0, $limit=1)
    {
        $query=$this->db->query('SELECT * FROM ' . $tableName . ' LIMIT ' . $start . ',' . $limit);
        $q=$query->fetchAll(PDO::FETCH_NUM);

        return $q;
    }

    private function _convertData()
    {
        foreach ($this->tables as &$table) {
            $table['data']='';
            $pieces_count=0;

            if (is_array($table['data_raw']) && count($table['data_raw']) > 0) {
                $table['data'].='INSERT INTO ' . $table['name'] . ' VALUES' . "\n";
                foreach ($table['data_raw'] as $pieces) {
                    $pieces_count++;
                    $pieces=str_replace("\n", '\n', $pieces);
                    $pieces=str_replace("\r", '\r', $pieces);
                    $pieces=str_replace("'", "''", $pieces);

                    $table['data'].='(\'' . implode('\',\'', $pieces) . '\')';
                    $table['data'].=($pieces_count < count($table['data_raw'])) ? ',' . "\n" : ';' . "\n";
                }
            }
        }
    }

    /**
     * Delete from generating data posts in trash,
     * posts revisions, comments in trash and comment what marked as spam
     */
    private function _cleanBeforeBackup()
    {
        if (!DatabaseBackupsOptions::instance()->getOption('clean'))
            return false;

        $prefix_len=strlen(DatabaseBackupsOptions::instance()->getTablePrefix());

        foreach ($this->tables as $name=>$table) {
            if (substr($name, $prefix_len) == 'posts') {
                $i=0;
                if (is_array($table['data_raw']) && count($table['data_raw']) > 0) {
                    foreach ($table['data_raw'] as $d) {
                        if ($d[7] == 'trash') {
                            unset($this->tables[$name]['data_raw'][$i]);
                            $this->deleted_posts[]=$d[0];
                        }
                        if ($d[20] == 'revision') {
                            unset($table['data_raw'][$i]);
                            $this->deleted_posts[]=$d[0];
                        }
                        $i++;
                    }
                }
            }
            if (substr($name, $prefix_len) == 'comments') {
                $i=0;
                if (is_array($table['data_raw']) && count($table['data_raw']) > 0) {
                    foreach ($table['data_raw'] as $d) {
                        if ($d[10] == 'spam' || $d['10'] == 'trash') {
                            unset($this->tables[$name]['data_raw'][$i]);
                            $this->deleted_comments[]=$d[0];
                        }
                        $i++;
                    }
                }
            }
        }

        foreach ($this->tables as $name=>$table) {
            if (substr($name, $prefix_len) == 'commentmeta') {
                $i=0;
                if (is_array($table['data_raw']) && count($table['data_raw']) > 0) {
                    foreach ($table['data_raw'] as $d) {
                        if (in_array($d[1], $this->deleted_comments)) {
                            unset($this->tables[$name]['data_raw'][$i]);
                        }
                        $i++;
                    }
                }
            }
            if (substr($name, $prefix_len) == 'postmeta') {
                $i=0;
                if (is_array($table['data_raw']) && count($table['data_raw']) > 0) {
                    foreach ($table['data_raw'] as $d) {
                        if (in_array($d[1], $this->deleted_posts)) {
                            unset($this->tables[$name]['data_raw'][$i]);
                        }
                        $i++;
                    }
                }
            }
        }
    }

    /**
     * Do database backup and save the output in file
     * @return bool
     */
    public function doBackup()
    {
        global $wpdb;

        $this->db=new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASSWORD);
        $this->db->query('SET NAMES "' . $wpdb->charset . '"');

        $this->_getTables();
        $this->_cleanBeforeBackup();
        $this->_convertData();

        $file='';
        foreach ($this->tables as $table) {
            $file.=$table['create'] . ";\n\n";
            $file.=$table['data'] . "\n\n";
        }

        if (!is_dir($this->dir))
            mkdir($this->dir);

        if (function_exists('mb_convert_encoding') && DatabaseBackupsOptions::instance()->getOption('utf8'))
            $file=mb_convert_encoding($file, "utf-8");

        $filename='db-backup-' . date('d_m_Y_H-i-s') . '.sql';

        if (function_exists('gzencode') && DatabaseBackupsOptions::instance()->getOption('gzip')) {
            $filename.='.gz';
            $file=gzencode($file, 9);
        }

        $handle=fopen($this->dir . $filename, 'w+');
        fwrite($handle, $file);
        fclose($handle);

        $this->_sendAdminNotifity($filename);
        return true;
    }

    /**
     * Get list of backup files
     * @return array of backups sorting by file created date
     */
    public function getBackups()
    {
        if (!is_dir($this->dir))
            mkdir($this->dir);

        $dh=opendir($this->dir);
        $files=array();

        while (($name=readdir($dh)) !== false) {
            if ($name != '.' AND $name != '..') {
                $file=$this->dir . $name;
                if (filetype($file) === 'file' && (substr($file, -4) === '.sql' || substr($file, -7) === '.sql.gz')) {
                    $format='';
                    if (substr($file, -4) === '.sql')
                        $format='sql';
                    if (substr($file, -7) === '.sql.gz')
                        $format='gz';

                    $files[filemtime($file)]=array(
                            'name'=>$name,
                            'size'=>filesize($file),
                            'url'=>$this->url . $name,
                            'date'=>filemtime($file),
                            'format'=>$format,
                    );
                }
            }
        }

        rsort($files);
        $this->backups=$files;
        return $files;
    }

    /**
     * Get one backup from backups list
     * @param $name
     * @return bool|mixed
     */
    public function getBackup($name)
    {
        $this->getBackups();

        foreach ($this->backups as $backup) {
            if ($backup['name'] == $name)
                return $backup;
        }

        return false;
    }

    /**
     * Delete backup file with checking extension
     * @param string $file
     * @return bool
     */
    public function deleteBackup($file)
    {
        if (empty($file))
            return false;

        if (!is_file($this->dir . $file))
            return false;

        if (!substr($file, -4) === '.sql' || !substr($file, -7) === '.sql.gz')
            return false;

        return unlink($this->dir . $file);
    }

    /**
     * Sum all backups space
     * @param array $backups
     * @return int
     */
    public function occupiedSpace($backups=array())
    {
        if (!$backups || !is_array($backups) || count($backups) == 0)
            return 0;
        $occupied_space=0;

        foreach ($backups as $backup) {
            $occupied_space+=$backup['size'];
        }

        return $occupied_space;
    }

    /**
     * Check and delete old backups if option enabled
     * @return bool
     */
    public function checkOldBackups()
    {
        $delete=DatabaseBackupsOptions::instance()->getOption('delete');
        $delete_days=DatabaseBackupsOptions::instance()->getOption('delete_days');
        $delete_copies=DatabaseBackupsOptions::instance()->getOption('delete_copies');

        if (!$delete)
            return false;

        if (!(int)$delete_days && !(int)$delete_copies)
            return false;

        $this->_deleteOldBackups($delete_days, $delete_copies);
    }

    /**
     * Delete backups if it's needed
     * @param int $days
     * @param int $copies
     * @return bool
     */
    private function _deleteOldBackups($days=0, $copies=0)
    {
        if ($days === 0 && $copies === 0)
            return false;

        $backups=$this->getBackups();

        if ($copies > 0 && count($backups) > $copies) {
            $i=0;

            foreach ($backups as $backup) {
                $i++;
                if ($i > $copies)
                    $this->deleteBackup($backup['name']);
            }
        }

        if ($days) {
            foreach ($backups as $backup) {
                $check_date=new DateTime("-" . $days . " ");
                $file_date=new DateTime();
                $file_date->setTimestamp($backup['date']);
                $diff=$file_date->diff($check_date);
                if ($diff->days > $days)
                    $this->deleteBackup($backup['name']);
            }
        }

        return true;
    }

    /**
     * Send notification to admin of backup result
     * @param $backup_name
     * @return bool
     */
    private function _sendAdminNotifity($backup_name)
    {
        if (!DatabaseBackupsOptions::instance()->getOption('notify'))
            return false;

        $backup=$this->getBackup($backup_name);
        $admin_email=get_option('admin_email');
        $blogname=get_option('blogname');
        $siteurl=get_option('siteurl');
        $message='';

        if (!$backup || $backup['size'] === 0) {
            $subject=__('Database Backup was not created at','database-backups') .' '. $blogname;
            $message.='<p></p><p>' . __('Database Backup was not created. Please check settings.','database-backups') . '</p>';
            $message.='<p>' . __('Date') . ': ' . date_i18n('j M Y H:i') . '</p>';
        } else {
            $subject=__('Database Backup was created at ','database-backups') . $blogname;
            $message.='<p></p><p>' . __('Database Backup successfully created.','database-backups') . '</p>';
            $message.='<br><strong>' . __('Date','database-backups') . '</strong>: ' . date_i18n('j M Y H:i', $backup['date']);
            $message.='<br><strong>' . __('Size','database-backups') . '</strong>: ' . round($backup['size'] / 1024 / 1024, 2) . ' MB';
            $message.='<br><strong>' . __('Extension','database-backups') . '</strong>: ' . $backup['format'];
            $message.='<br><strong>' . __('Download','database-backups') . '</strong>: ' . $backup['url'];
        }
        $message.='<p></p><hr><p><a href="' . $siteurl . '">' . $blogname . '</a></p>';

        add_filter('wp_mail_content_type', create_function('', 'return "text/html";'));
        wp_mail($admin_email, $subject, $message);
    }
}

/**
 * Class DatabaseBackupsOptions
 */
class DatabaseBackupsOptions
{
    /**
     *    Holding the singleton instance
     */
    private static $_instance=null;

    /**
     * @return DatabaseBackupsOptions
     */
    public static function instance()
    {
        if (is_null(self::$_instance))
            self::$_instance=new self();
        return self::$_instance;
    }

    /**
     *    Prevent from creating more instances
     */
    private function __clone()
    {
    }

    /**
     *    Prevent from creating more than one instance
     */
    private function __construct()
    {
        add_action('admin_init', array(&$this, 'admin_init'));
        add_action('admin_menu', array(&$this, 'add_options_page'));
        add_filter('plugin_action_links_' . plugin_basename(__FILE__), array($this, 'edit_plugin_list_links'), 10, 2);
    }

    /**
     *
     */
    public function admin_init()
    {
        load_plugin_textdomain('database-backups', '', trailingslashit(plugin_basename(WP_PLUGIN_DIR .
                        '/database-backups/')) .
                'languages');
    }

    /**
     * Edit links that appear on installed plugins list page, for our plugin.
     */
    public function edit_plugin_list_links($links)
    {
        unset($links['edit']);

        return array_merge(array(
                '<a href="' .
                admin_url(PLUGIN_LINK) .
                '">' .
                __('Settings', 'database-backups') .
                '</a>',
        ), $links);
    }

    /**
     * Return option value from WP options table
     * @param $option_name
     * @return mixed|void
     */
    public static function getOption($option_name)
    {
        return get_option('database-backups_' . $option_name);
    }

    public static function getTablePrefix()
    {
        global $wpdb;
        return $wpdb->prefix;
    }

    /**
     * Set options for current plugin
     * @param $options
     */
    public function setOptions($options)
    {
        $cron_old=self::getOption('cron');

        if (isset($options['directory']) && !empty($options['directory']))
            self::setOption('directory', trim($options['directory']));

        self::setOption('limit', isset($options['limit']) ? (int)$options['limit'] : 0);
        self::setOption('prefix', isset($options['prefix']) ? 1 : 0);
        self::setOption('clean', isset($options['clean']) ? 1 : 0);
        self::setOption('notify', isset($options['notify']) ? 1 : 0);
        self::setOption('gzip', isset($options['gzip']) ? 1 : 0);
        self::setOption('utf8', isset($options['utf8']) ? 1 : 0);
        self::setOption('cron', isset($options['cron']) ? $options['cron'] : 0);
        self::setOption('delete', isset($options['delete']) ? 1 : 0);
        self::setOption('delete_days', isset($options['delete_days']) ? (int)$options['delete_days'] : 0);
        self::setOption('delete_copies', isset($options['delete_copies']) ? (int)$options['delete_copies'] : 0);

        if (self::getOption('delete_copies') == 0 && self::getOption('delete_days') == 0)
            self::setOption('delete', 0);

        if (isset($options['cron']) && $options['cron'] !== $cron_old)
            wp_clear_scheduled_hook('database-backups-cron');
    }

    /**
     * Get options for current plugin
     * @param $option_name
     * @param $option_value
     */
    public static function setOption($option_name, $option_value)
    {
        $option_name='database-backups_' . $option_name;

        if (get_option($option_name) !== false) {
            update_option($option_name, $option_value);
        } else {
            $deprecated=null;
            $autoload='no';
            add_option($option_name, $option_value, $deprecated, $autoload);
        }
    }

    /**
     *    Admin menu hook, adds blogs local options page
     */
    public function add_options_page()
    {
        add_submenu_page('tools.php', __('Database Backups', 'database-backups'), __('Database Backups', 'database-backups'),
                'manage_options', 'database-backups',
                array(&$this, 'render_options_page'));
    }

    /**
     *    Rendering the options page
     */
    public function render_options_page()
    {
        if (isset($_POST['do_backup_manually']) && $_POST['do_backup_manually'] == 1) {
            if (DatabaseBackups::instance()->doBackup())
                echo '<div id="message" class="updated notice notice-success is-dismissible"><p>' .
                        __('Backup Created', 'database-backups') .
                        '</p><button type="button" class="notice-dismiss"><span class="screen-reader-text">' .
                        _('Close') .
                        '</span></button></div>';
        }

        $saved=false;

        if (isset($_POST['options'])) {
            $this->setOptions($_POST['options']);
            echo '<div id="message" class="updated notice notice-success is-dismissible"><p>' .
                    __('Settings Saved', 'database-backups') .
                    '</p><button type="button" class="notice-dismiss"><span class="screen-reader-text">' .
                    _('Close') .
                    '</span></button></div>';
            $saved=true;
        }

        if (isset($_GET['delete']) && !empty($_GET['delete']))
            DatabaseBackups::instance()->deleteBackup($_GET['delete']);

        DatabaseBackups::instance()->checkOldBackups();
        $backups=DatabaseBackups::instance()->getBackups();
        $gzip_status=!function_exists('gzencode') ? 'disabled' : '';
        $utf8_status=!function_exists('mb_detect_encoding') ? 'disabled' : '';
        $wp_upload_dir=wp_upload_dir();
        $directory=self::getOption('directory');
        $limit=self::getOption('limit');
        $prefix=self::getOption('prefix') ? 'checked' : '';
        $clean=self::getOption('clean') ? 'checked' : '';
        $notify=self::getOption('notify') ? 'checked' : '';
        $gzip=self::getOption('gzip') ? 'checked' : '';
        $utf8=self::getOption('utf8') ? 'checked' : '';
        $cronOption=self::getOption('cron');
        $delete=self::getOption('delete') ? 'checked' : '';
        $delete_days=self::getOption('delete_days');
        $delete_copies=self::getOption('delete_copies');
        $occupied_space=round(DatabaseBackups::instance()->occupiedSpace($backups) / 1024 / 1024, 2);
        $total_free_space=round(disk_free_space($_SERVER['DOCUMENT_ROOT']) / 1024 / 1024, 2);

        ?>
        <div class="wrap"><h1><?php _e('Database Backups'); ?></h1>
            <h2><a href="#" class="settings-toggle"><?php _e('Settings'); ?></a></h2>
            <form action="<?php echo PLUGIN_LINK; ?>" method="post" class="settings-wrap">
                <table class="form-table">
                    <tbody>
                    <tr>
                        <th scope="row"><?php _e('Directory name', 'database-backups'); ?></th>
                        <td>
                            <?php echo $wp_upload_dir['baseurl']; ?>/<input type="text" name="options[directory]"
                                                                            placeholder="database-backups"
                                                                            value="<?php echo (!empty($directory)) ? $directory : 'database-backups'; ?>"
                                                                            required>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Use limits', 'database-backups'); ?></th>
                        <td>
                            <input type="number" min="0" max="10000" name="options[limit]"
                                   placeholder="0"
                                   value="<?php echo $limit; ?>">
                            <p class="description"><?php _e('Set round value if you see PHP memory error. 0 - don\'t use limits', 'database-backups'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Use prefix', 'database-backups'); ?></th>
                        <td>
                            <input type="checkbox" name="options[prefix]" <?php echo $prefix; ?>>
                            <p class="description"><?php _e('Backup only WP tables with prefix', 'database-backups'); ?>
                                "<?php echo self::getTablePrefix(); ?>"</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Clean database', 'database-backups'); ?></th>
                        <td>
                            <input type="checkbox" name="options[clean]" <?php echo $clean; ?>>
                            <p class="description"><?php _e('Don\'t save in backup file: posts/page revisions, posts/page in trash, spam comments, comments in trash', 'database-backups'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Notification to admin', 'database-backups'); ?></th>
                        <td>
                            <input type="checkbox" name="options[notify]" <?php echo $notify; ?>>
                            <p class="description"><?php _e('Send Email to admin with result of backup operation', 'database-backups'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Enable GZip compression', 'database-backups'); ?></th>
                        <td>
                            <input type="checkbox" name="options[gzip]" <?php echo $gzip . ' ' . $gzip_status; ?>>
                            <p class="description"><?php _e('Allow to pack your backup in GZ archive', 'database-backups'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Enable converting to UTF-8', 'database-backups'); ?></th>
                        <td>
                            <input type="checkbox" name="options[utf8]" <?php echo $utf8 . ' ' . $utf8_status; ?>>
                            <p class="description"><?php _e('You can check this option if have problems with encoding when import database', 'database-backups'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Enable auto backups', 'database-backups'); ?></th>
                        <td>
                            <select name="options[cron]">
                                <option value="" <?php echo empty($cronOption) ? 'selected' : ''; ?>>
                                    <?php _e('Disabled', 'database-backups'); ?>
                                </option>
                                <?php foreach (wp_get_schedules() as $cron=>$value): ?>
                                    <option value="<?php echo $cron; ?>" <?php echo ($cronOption ==
                                            $cron) ? 'selected' : ''; ?>><?php echo $value['display'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Enable auto delete', 'database-backups'); ?></th>
                        <td>
                            <input type="checkbox" class="auto-delete-checkbox"
                                   name="options[delete]" <?php echo $delete; ?>>
                            <p class="description"><?php _e('Check this option if you want auto deleting the old backups', 'database-backups'); ?></p>
                        </td>
                    </tr>
                    <tr class="auto-delete">
                        <th scope="row"><?php _e('Delete after * days', 'database-backups'); ?></th>
                        <td>
                            <input type="number" name="options[delete_days]" value="<?php echo $delete_days ?>" min="0"
                                   max="1000">
                            <p class="description"><?php _e('How many days to store the backups. 0 - for disable this option', 'database-backups'); ?></p>
                        </td>
                    </tr>
                    <tr class="auto-delete">
                        <th scope="row"><?php _e('Delete after * copies', 'database-backups'); ?></th>
                        <td>
                            <input type="number" name="options[delete_copies]" value="<?php echo $delete_copies ?>"
                                   min="0" max="1000">
                            <p class="description"><?php _e('How many copies of backups to store. 0 - for disable this option', 'database-backups'); ?></p>
                        </td>
                    </tr>
                    </tbody>
                </table>
                <?php
                submit_button(null, 'primary', null, null, array('autocomplete'=>'off'));
                ?></form>
            <div class="wrap">
                <h2><?php _e('All backups', 'database-backups'); ?></h2>
                <?php
                if (count($backups) === 0)
                    echo "<div class='no-backups'><p>" . __('No backups yet', 'database-backups') . "</p></div>";
                else {
                    $i=1;
                    echo "<table class='wp-list-table widefat striped'><thead><tr><th>" .
                            __('ID', 'database-backups') .
                            "</th><th>" .
                            __('File Name', 'database-backups') .
                            "</th><th>" .
                            __('Size', 'database-backups') .
                            "</th><th>" .
                            __('Date', 'database-backups') .
                            "</th><th>" .
                            __('Actions', 'database-backups') .
                            "</th></tr></thead>";
                    foreach ($backups as $backup) {
                        $icon='';
                        if ($backup['format'] == 'sql')
                            $icon='dashicons-media-spreadsheet';
                        elseif ($backup['format'] == 'gz')
                            $icon='dashicons-portfolio';
                        echo "<tr><td>" .
                                $i++ .
                                "<td><span class='icon dashicons " . $icon . "'></span>" . $backup['name'] .
                                "</td><td>" .
                                round($backup['size'] / 1024 / 1024, 2) .
                                " MB</td><td>" .
                                date_i18n('j M Y H:i', $backup['date']) .
                                "</td><td><a href='" .
                                $backup['url'] .
                                "' class='button'><span class='icon dashicons dashicons-download'></span>" .
                                __('Download', 'database-backups') .
                                "</a>
                            <a href='" . PLUGIN_LINK . "&delete=" .
                                $backup['name'] .
                                "' class='button'><span class='icon dashicons dashicons-trash'></span>" .
                                __('Delete', 'database-backups') .
                                "</a>
                            </td></tr>";
                    }
                    echo "<tfoot><tr><th>" .
                            ($i - 1) .
                            "</th><th></th><th>" .
                            $occupied_space .
                            " MB / " .
                            $total_free_space .
                            " MB</th><th></th><th></th></tr></tfoot></table>";
                }
                ?>
            </div>
            <form action="<?php echo PLUGIN_LINK; ?>" method="post">
                <p></p>
                <input type="hidden" name="do_backup_manually" value="1">
                <?php
                submit_button(__('Do backup', 'database-backups'), 'primary', null, null, array('autocomplete'=>'off'));
                ?>
            </form>
        </div>
        <style>
            .settings-toggle {
                text-decoration: none;
                border-bottom: 1px dotted;
                font-weight: 400;
            }

            .settings-toggle:focus {
                box-shadow: none;
            }

            .settings-wrap {
            <?php echo ($saved) ? 'display:block' : 'display:none';?>;
            }

            .settings-wrap .auto-delete {
            <?php echo ($delete) ? 'display:table-row' : 'display:none';?>;
            }

            .wp-list-table span.icon {
                margin: 0 5px 0 0;
            }

            .wp-list-table a span.icon {
                line-height: 1.3em;
            }

            .no-backups {
                margin: 5px 0 15px;
                border-left: 3px solid #ffb900;
                background: #fff;
                -webkit-box-shadow: 0 1px 1px 0 rgba(0, 0, 0, .1);
                box-shadow: 0 1px 1px 0 rgba(0, 0, 0, .1);
                padding: 1px 12px;
            }
        </style>
        <script>
            jQuery('.settings-toggle').click(function () {
                var s = jQuery('.settings-wrap');

                if (s.css('display') == 'block')
                    s.fadeOut();
                else
                    s.fadeIn(200);
            });

            jQuery('.auto-delete-checkbox').click(function () {
                var c = jQuery(this);
                var a = jQuery('.auto-delete');

                if (c.attr('checked')) {
                    a.fadeIn(200);
                } else {
                    a.fadeOut(200);
                }
            });
        </script>
        <?php
    }
}

DatabaseBackups::instance();

if (is_admin())
    DatabaseBackupsOptions::instance();