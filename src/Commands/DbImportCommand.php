<?php

namespace Anvil\Commands;

use Anvil\Support\HasAccessToFilesystem;
use GuzzleHttp\Exception\RequestException;
use ZipArchive;

class DbImportCommand
{
    use HasAccessToFilesystem;

    public static $name = 'db-import';

    /**
     * Imports database
     *
     * ## EXAMPLES
     *
     *     wp db-import import
     *
     * @when   after_wp_load
     *
     * @param  array  $args  Array of arguments.
     * @param  array  $assoc_args  Array of arguments using names.
     * @return void
     */
    public function import($args, $assoc_args)
    {
        global $wpdb;
        $this->initFilesystem();

        $dbFile = wp_get_upload_dir()['basedir'] . '/db.sql';
        $mediaFile = wp_get_upload_dir()['basedir'] . '/media.zip';

        $client = new \GuzzleHttp\Client([
            'base_uri' => config('import-credentials.base_uri') . '/wp-json/zncr/v1/',
            'timeout'  => 60 * 15,
            'auth' => [
                config('import-credentials.user'),
                config('import-credentials.password'),
            ],
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ],
        ]);

        \WP_CLI::log('Generating database backup...');

        try {
            $response = $client->post(
                'backup-db-and-url',
                [
                    'json' => [
                        'abspath' => ABSPATH,
                        'site_url' => str_replace(['https:', 'http:'], ['', ''], get_site_url()),
                    ],
                ]
            );
        } catch (RequestException $e) {
            \WP_CLI::error('Error while importing database: ' . $e->getMessage());
            return;
        }

        \WP_CLI::log('Downloading database backup...');

        $this->wpFilesystem->put_contents(
            $dbFile,
            file_get_contents(json_decode($response->getBody()->getContents())->url)
        );

        \WP_CLI::log('Dropping all tables...');

        $wpdb->query('SET FOREIGN_KEY_CHECKS = 0;');
        $tables = $wpdb->get_col('SHOW TABLES');
        foreach ($tables as $table) {
            $wpdb->query("DROP TABLE IF EXISTS `$table`;");
        }
        $wpdb->query('SET FOREIGN_KEY_CHECKS = 1;');

        \WP_CLI::log('Importing database...');

        $db = new \PDO(sprintf("mysql:host=%s;dbname=%s", DB_HOST, DB_NAME), DB_USER, DB_PASSWORD);
        $sql = file_get_contents($dbFile);
        $db->exec($sql);

        unlink($dbFile);

        \WP_CLI::log('Database imported successfully.');

        \WP_CLI::log('Compressing media... (be patient, it\'s about 6GB)');

        try {
            $response = $client->post(
                'compress-media-and-url'
            );
        } catch (RequestException $e) {
            \WP_CLI::error('Error while compressing database: ' . $e->getMessage());
            return;
        }

        \WP_CLI::log('Downloading media zip file... (be patient, it\'s about 6GB)');

        $this->wpFilesystem->put_contents(
            $mediaFile,
            file_get_contents(json_decode($response->getBody()->getContents())->url)
        );

        \WP_CLI::log('Unzipping media files...');

        $zip = new ZipArchive();

        if ($zip->open($mediaFile) !== true) {
            \WP_CLI::error('Unable to open media zip file.');
            return;
        }

        $zip->extractTo(wp_get_upload_dir()['basedir']);
        $zip->close();

        unlink($mediaFile);

        \WP_CLI::log('Media imported successfully.');
        \WP_CLI::success('Database and media imported successfully.');
    }
}
