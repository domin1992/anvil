<?php

namespace Anvil\Controllers;

use DeliciousBrains\WPMDB\Common\Cli\CliManager;
use DeliciousBrains\WPMDB\Common\Error\ErrorLog;
use DeliciousBrains\WPMDB\Common\FormData\FormData;
use DeliciousBrains\WPMDB\Common\Migration\InitiateMigration;
use DeliciousBrains\WPMDB\Common\Migration\MigrationManager;
use DeliciousBrains\WPMDB\Common\MigrationPersistence\Persistence;
use DeliciousBrains\WPMDB\Common\MigrationState\MigrationStateManager;
use DeliciousBrains\WPMDB\Common\Properties\DynamicProperties;
use DeliciousBrains\WPMDB\Common\Sql\Table;
use DeliciousBrains\WPMDB\Common\Util\Util;
use DeliciousBrains\WPMDB\WPMDBDI;
use ZipArchive;

class ExportDbController
{
    private $form_data;
    private $util;
    private $cli_manager;
    private $table;
    private $dynamic_properties;
    private $initiate_migration;
    private $error_log;
    private $migration_state_manager;
    private $migration_manager;
    private $migration = [];
    private $profile;
    private $post_data = [];

    public function backupDbAndUrl(\WP_REST_Request $request)
    {
        $this->form_data = WPMDBDI::getInstance()->get(FormData::class);
        $this->util = WPMDBDI::getInstance()->get(Util::class);
        $this->cli_manager = WPMDBDI::getInstance()->get(CliManager::class);
        $this->table = WPMDBDI::getInstance()->get(Table::class);
        $this->dynamic_properties = WPMDBDI::getInstance()->get(DynamicProperties::class);
        $this->initiate_migration = WPMDBDI::getInstance()->get(InitiateMigration::class);
        $this->error_log = WPMDBDI::getInstance()->get(ErrorLog::class);
        $this->migration_state_manager = WPMDBDI::getInstance()->get(MigrationStateManager::class);
        $this->migration_manager = WPMDBDI::getInstance()->get(MigrationManager::class);

        $wpmdb_cli = wpmdb_cli();

        $currentTimestamp = time();
        $backupFileName = 'db-backup-' . $currentTimestamp . '.sql';
        $baseDir = wp_get_upload_dir()['basedir'] . '/zncr/';

        if (!file_exists($baseDir)) {
            wp_mkdir_p($baseDir);
        }

        $find = [];
        $replace = [];

        if ($request->has_param('abspath')) {
            $find[] = ABSPATH;
            $replace[] = $request->get_param('abspath');
        }

        if ($request->has_param('site_url')) {
            $find[] = get_site_url();
            $replace[] = $request->get_param('site_url');
        }

        $profile = $wpmdb_cli->get_profile_data_from_args(
            [
                $baseDir . $backupFileName,
            ],
            array_merge(
                [
                    'action' => 'savefile',
                    'export_dest' => $baseDir . $backupFileName,
                ],
                count($find)
                    ? [
                        'find' => implode(',', $find),
                    ]
                    : [],
                count($replace)
                    ? [
                        'replace' => implode(',', $replace),
                    ]
                    : [],
            )
        );

        if (is_wp_error($profile)) {
            return wp_send_json_error([
                'message' => $profile->get_error_message(),
            ]);
        }

        $result = $this->migration($profile);

        if (is_wp_error($result)) {
            return wp_send_json_error([
                'message' => $result->get_error_message(),
            ]);
        }

        return wp_send_json([
            'url' => wp_get_upload_dir()['baseurl'] . '/zncr/' . $backupFileName,
        ]);
    }

    public function compressMediaAndUrl(\WP_REST_Request $request)
    {
        $baseDir = wp_get_upload_dir()['basedir'] . '/zncr/';

        if (!file_exists($baseDir)) {
            wp_mkdir_p($baseDir);
        }

        $dirs = array_values(
            array_filter(
                scandir(wp_get_upload_dir()['basedir']),
                fn (string $file) => !str_starts_with($file, '.')
                    && is_dir(wp_get_upload_dir()['basedir'] . '/' . $file)
                    && preg_match('/^\d+$/', $file, $matches) === 1
            )
        );

        $zipFilename = 'media-' . time() . '.zip';

        $zip = new ZipArchive();
        $filename = $baseDir . $zipFilename;

        if ($zip->open($filename, ZipArchive::CREATE) !== true) {
            wp_send_json_error("cannot open <$filename>");
        }

        foreach ($dirs as $dir) {
            $this->addFilesToZip(
                wp_get_upload_dir()['basedir'] . '/' . $dir,
                $zip,
                $dir . '/'
            );
        }

        $zip->close();

        return wp_send_json([
            'url' => wp_get_upload_dir()['baseurl'] . '/zncr/' . $zipFilename,
        ]);
    }

    private function pre_migration_check($profile)
    {
        $profile = apply_filters('wpmdb_cli_profile_before_migration', $profile);

        if (is_wp_error($profile)) {
            return $profile;
        }

        if (is_array($profile)) {
            Persistence::cleanupStateOptions();
            $profile = $this->form_data->parse_and_save_migration_form_data(json_encode($profile));
        }

        $this->profile = $profile;

        if (!isset($this->profile['current_migration']['stages'])) {
            $this->profile['current_migration']['stages'] = array('tables');
        }

        $this->profile['current_migration']['migration_id'] = Util::uuidv4();

        return true;
    }

    private function cli_initiate_migration()
    {
        do_action('wpmdb_cli_before_initiate_migration', $this->profile);

        $migration_args                          = $this->post_data;
        $migration_args['form_data']             = json_encode($this->profile);
        $migration_args['stage']                 = 'migrate';
        $migration_args['site_details']['local'] = $this->util->site_details();

        if ('find_replace' === $this->profile['action']) {
            $migration_args['stage'] = 'find_replace';
        }

        $this->post_data = apply_filters('wpmdb_cli_initiate_migration_args', $migration_args, $this->profile);

        $this->post_data['site_details'] = json_encode($this->post_data['site_details']);

        $response = $this->initiate_migration($this->post_data);

        $initiate_migration_response = $this->verify_cli_response($response, 'initiate_migration()');
        if (!is_wp_error($initiate_migration_response)) {
            $initiate_migration_response = apply_filters('wpmdb_cli_initiate_migration_response', $initiate_migration_response);
        }

        return $initiate_migration_response;
    }

    private function verify_cli_response($response, $function_name)
    {
        if (is_wp_error($response)) {
            return $response;
        }

        $response = trim($response);
        if (false === $response) {
            return new \WP_Error('wpmdb_cli_error', $this->error_log->getError());
        }

        if (false === Util::is_json($response)) {
            return new \WP_Error('wpmdb_cli_error', sprintf(__('We were expecting a JSON response, instead we received: %2$s (function name: %1$s)', 'wp-migrate-db-cli'), $function_name, $response));
        }

        $response = json_decode($response, true);
        if (isset($response['wpmdb_error'])) {
            return new \WP_Error('wpmdb_cli_error', $response['body']);
        }

        // Display warnings and non fatal error messages as CLI warnings without aborting.
        if (isset($response['wpmdb_warning']) || isset($response['wpmdb_non_fatal_error'])) {
            $body     = (isset($response['cli_body'])) ? $response['cli_body'] : $response['body'];
            $messages = maybe_unserialize($body);
            foreach ((array) $messages as $message) {
                if ($message) {
                    return new \WP_Error('wpmdb_cli_error', $message);
                }
            }
        }

        return $response;
    }

    private function initiate_migration($args = false)
    {
        $_POST    = $args;
        $response = $this->initiate_migration->ajax_initiate_migration();

        return $response;
    }

    private function finalize_migration()
    {
        do_action('wpmdb_cli_before_finalize_migration', $this->profile, $this->migration);

        $finalize = apply_filters('wpmdb_cli_finalize_migration', true, $this->profile, $this->migration);
        if (is_wp_error($finalize)) {
            return $finalize;
        }

        $this->post_data = apply_filters('wpmdb_cli_finalize_migration_args', $this->post_data, $this->profile, $this->migration);

        $this->dynamic_properties->post_data = $this->post_data;

        if ('savefile' === $this->post_data['intent']) {
            return $this->finalize_export();
        }

        $response = apply_filters('wpmdb_cli_finalize_migration_response', null, $this->post_data);
        $response = $this->verify_cli_response($response, 'finalize_migration()');

        if (is_wp_error($response)) {
            return $response;
        }

        do_action('wpmdb_cli_after_finalize_migration', $this->profile, $this->migration);

        return true;
    }

    private function finalize_export()
    {
        $state_data = $this->migration_state_manager->set_post_data();

        $temp_file = $state_data['dump_path'];
        if (!isset($state_data['export_dest']) || 'ORIGIN' === $state_data['export_dest']) {
            $response = $temp_file;
        } else {
            $dest_file = $state_data['export_dest'];
            if (file_exists($temp_file) && rename($temp_file, $dest_file)) {
                $response = $dest_file;
            } else {
                $response = new \WP_Error('wpmdb_cli_error', __('Unable to move exported file.', 'wp-migrate-db'));
            }
        }

        return $response;
    }

    public function migration($profile, $assoc_args = array())
    {
        $pre_check = $this->pre_migration_check($profile);
        if (is_wp_error($pre_check)) {
            return $pre_check;
        }

        // At this point, $profile has been checked a retrieved into $this->profile, so should not be used in this function any further.
        if (empty($this->profile)) {
            return new \WP_Error('wpmdb_cli_error', __('Profile not found or unable to be generated from params.', 'wp-migrate-db-cli'));
        }
        unset($profile);

        $this->util->set_time_limit();
        $this->cli_manager->set_cli_migration();

        if ('savefile' === $this->profile['action']) {
            $this->post_data['intent'] = 'savefile';
            if (!empty($this->profile['export_dest'])) {
                $this->post_data['export_dest'] = $this->profile['export_dest'];
            } else {
                $this->post_data['export_dest'] = 'ORIGIN';
            }
        }

        if (
            isset($this->profile['current_migration'], $this->profile['current_migration']['intent'])
            && 'backup_local' === $this->profile['current_migration']['intent']
        ) {
            $this->post_data['intent'] = 'savefile';
        }

        // Ensure local site_details available.
        $this->post_data['site_details']['local'] = $this->util->site_details();

        $this->profile = apply_filters('wpmdb_cli_filter_before_cli_initiate_migration', $this->profile, $this->post_data);

        if (is_wp_error($this->profile)) {
            return new \WP_Error('wpmdb_cli_error', $this->profile->get_error_message());
        }

        // Check for tables specified in migration profile that do not exist in the source database
        if (!empty($this->profile['select_tables']) && 'import' !== $this->profile['action']) {
            $source_tables = apply_filters('wpmdb_cli_filter_source_tables', $this->table->get_tables(), $this->profile);

            if (!empty($source_tables)) {
                // Return error if selected tables do not exist in source database
                $nonexistent_tables = array();
                foreach ($this->profile['select_tables'] as $table) {
                    if (!in_array($table, $source_tables)) {
                        $nonexistent_tables[] = $table;
                    }
                }

                if (!empty($nonexistent_tables)) {
                    $local_or_remote = ('pull' === $this->profile['action']) ? 'remote' : 'local';

                    return new \WP_Error('wpmdb_cli_error', sprintf(__('The following table(s) do not exist in the %1$s database: %2$s', 'wp-migrate-db-cli'), $local_or_remote, implode(', ', $nonexistent_tables)));
                }
            }
        }

        if (!empty($this->dynamic_properties->post_data)) {
            $this->post_data = $this->dynamic_properties->post_data;
        }

        if (is_wp_error($this->profile)) {
            return $this->profile;
        }

        $this->profile = apply_filters('wpmdb_cli_filter_before_migration', $this->profile, $this->post_data);
        do_action('wpmdb_cli_before_migration', $this->post_data, $this->profile);
        $this->migration = $this->cli_initiate_migration();

        if (is_wp_error($this->migration)) {
            return $this->migration;
        }

        $tables_to_process = $this->migrate_tables();

        if (is_wp_error($tables_to_process)) {
            return $tables_to_process;
        }

        $this->post_data['tables'] = implode(',', $tables_to_process);

        do_action('wpmdb_cli_during_cli_migration', $this->post_data, $this->profile);

        $finalize = $this->finalize_migration();

        if (is_wp_error($finalize) || in_array($this->profile['action'], ['savefile', 'backup_local'])) {
            return $finalize;
        }

        return true;
    }

    private function migrate_tables()
    {
        $tables_to_migrate                   = $this->get_tables_to_migrate();
        $this->dynamic_properties->post_data = $this->post_data;

        $tables         = $tables_to_migrate;
        $stage_iterator = 2;

        $filtered_vars = apply_filters('wpmdb_cli_filter_before_migrate_tables', array(
            'tables'         => $tables,
            'stage_iterator' => $stage_iterator,
        ));
        if (!is_array($filtered_vars)) {
            return $filtered_vars;
        } else {
            extract($filtered_vars, EXTR_OVERWRITE);
        }

        $table_rows = $this->get_row_counts_from_table_list($tables, $stage_iterator);

        do_action('wpmdb_cli_before_migrate_tables', $this->profile, $this->migration);

        $args   = $this->post_data;

        do {
            $migration_progress = 0;

            foreach ($tables as $key => $table) {
                $current_row         = -1;
                $primary_keys        = '';
                $table_progress      = 0;
                $table_progress_last = 0;

                $args['table']      = $table;
                $args['last_table'] = ($key == count($tables) - 1) ? '1' : '0';

                do {
                    // reset the current chunk
                    $this->table->empty_current_chunk();

                    $args['current_row']  = $current_row;
                    $args['primary_keys'] = $primary_keys;
                    $args                 = apply_filters('wpmdb_cli_migrate_table_args', $args, $this->profile, $this->migration);

                    $response = $this->migrate_table($args);

                    $migrate_table_response = $this->verify_cli_response($response, 'migrate_table()');

                    if (is_wp_error($migrate_table_response)) {
                        return $migrate_table_response;
                    }

                    $migrate_table_response = apply_filters('wpmdb_cli_migrate_table_response', $migrate_table_response, $_POST, $this->profile, $this->migration);

                    $current_row  = $migrate_table_response['current_row'];
                    $primary_keys = $migrate_table_response['primary_keys'];

                    $last_migration_progress = $migration_progress;

                    if (-1 == $current_row) {
                        $migration_progress -= $table_progress;
                        $migration_progress += $table_rows[$table];
                    } else {
                        if (0 === $table_progress_last) {
                            $table_progress_last = $current_row;
                            $table_progress      = $table_progress_last;
                            $migration_progress  += $table_progress_last;
                        } else {
                            $iteration_progress  = $current_row - $table_progress_last;
                            $table_progress_last = $current_row;
                            $table_progress      += $iteration_progress;
                            $migration_progress  += $iteration_progress;
                        }
                    }

                    $increment = $migration_progress - $last_migration_progress;

                } while (-1 != $current_row);
            }

            ++$stage_iterator;
            $args['stage'] = 'migrate';

            if ('find_replace' === $args['intent']) {
                $args['stage'] = 'find_replace';
            }

            if ('import' === $args['intent']) {
                break;
            }

            $tables     = $tables_to_migrate;
            $table_rows = $this->get_row_counts_from_table_list($tables, $stage_iterator);
        } while ($stage_iterator < 3);

        $this->post_data = $args;

        return $tables;
    }

    private function get_tables_to_migrate()
    {
        $tables_to_migrate = $this->table->get_tables('prefix');

        // @TODO Hack to get profile and post_data info available in other areas of the codebase...
        $this->dynamic_properties->profile   = $this->profile;
        $this->dynamic_properties->post_data = $this->post_data;

        return apply_filters('wpmdb_cli_tables_to_migrate', $tables_to_migrate, $this->profile, $this->migration);
    }

    private function get_row_counts_from_table_list($tables, $stage)
    {
        static $cached_results = array();

        if (isset($cached_results[$stage])) {
            return $cached_results[$stage];
        }

        $local_table_rows         = $this->table->get_table_row_count();
        $cached_results[$stage] = apply_filters('wpmdb_cli_get_row_counts_from_table_list', $local_table_rows, $stage);

        return $cached_results[$stage];
    }

    private function migrate_table($args = false)
    {
        $_POST    = $args;
        $response = $this->migration_manager->ajax_migrate_table();

        return $response;
    }

    private function addFilesToZip($folder, &$zip, $parentFolder = '')
    {
        $handle = opendir($folder);
        if (!$handle) {
            return false;
        }

        while (($file = readdir($handle)) !== false) {
            if ($file == '.' || $file == '..') {
                continue;
            }

            $filePath = $folder . DIRECTORY_SEPARATOR . $file;
            $localPath = $parentFolder . $file;

            if (is_dir($filePath)) {
                // Add directory to zip
                $zip->addEmptyDir($localPath);
                // Recurse into subdirectory
                $this->addFilesToZip($filePath, $zip, $localPath . '/');
            } else {
                // Add file to zip
                $zip->addFile($filePath, $localPath);
            }
        }

        closedir($handle);
        return true;
    }
}
