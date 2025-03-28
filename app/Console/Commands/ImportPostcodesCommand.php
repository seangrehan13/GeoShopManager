<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

final class ImportPostcodesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:import-postcodes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Downloads, unzips and imports a list of UK postcodes/coordinates into the database.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // We need to increase the memory limit since the download is 228 MB
        ini_set('memory_limit', '1024M');

        $startTime = microtime(true);

        $url = 'https://parlvid.mysociety.org/os/ONSPD/2022-11.zip';
        $downloadPath = storage_path('app/public/postcodes.zip');
        $extractToPath = storage_path('app/public/ONSPD');

        $this->downloadFile($url, $downloadPath);

        $this->unzipFile($downloadPath, $extractToPath);

        $this->parseCsvFiles($extractToPath . '/Data/multi_csv');

        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;

        $this->info('Completed in ' . round($executionTime, 2) . ' seconds');
    }

    private function downloadFile(string $url, string $downloadPath): void
    {
        if (file_exists($downloadPath)) {
            $this->info('File already downloaded!');
            return;
        }

        $response = Http::timeout(120)->get($url);

        if ($response->failed()) {
            throw new \Exception('Failed to download the file.');
        }

        Storage::disk('public')->put(basename($downloadPath), $response->body());

        $this->info('File downloaded successfully!');
    }

    private function unzipFile(string $zipPath, string $extractToPath)
    {
        $zip = new \ZipArchive;

        if ($zip->open($zipPath) === true) {
            $zip->extractTo($extractToPath);
            $zip->close();
            $this->info('File unzipped successfully!');
        } else {
            throw new \Exception('Failed to unzip the file.');
        }
    }

    private function parseCsvFiles(string $directoryPath): void
    {
        if (!is_dir($directoryPath)) {
            throw new \Exception('CSV directory not found.');
        }

        $this->newLine();

        $files = glob($directoryPath . '/*.csv');

        $this->withProgressBar($files, function ($file) {
            $csv = array_map('str_getcsv', file($file));
            $this->savePostcodes($csv);
        });

        $this->newLine(2);
        $this->info('CSV files parsed successfully!');
    }

    private function savePostcodes(array $csv): void
    {
        // Need to limit the number of postcodes processed at a time due to memory limit
        $batchSize = 2000;
        $postcodes = [];

        foreach ($csv as $i => $row) {
            // The first row is the csv header, so ignore this row
            if ($i === 0) {
                continue;
            }

            $now = Carbon::now();

            $postcode = strtoupper(str_replace(' ', '', $row[0]));

            $postcodes[] = [
                'postcode' => $postcode,
                'coordinates' => [
                    'latitude' => $row[42],
                    'longitude' => $row[43],
                ],
                'created_at' => $now,
                'updated_at' => $now,
            ];

            if (count($postcodes) === $batchSize) {
                $this->insertPostcodes($postcodes);
                $postcodes = [];
            }
        }

        if (!empty($postcodes)) {
            $this->insertPostcodes($postcodes);
        }
    }

    /**
     * Inserts or updates postcodes in bulk.
     * This is a custom query rather than upsert due to an issue with matanyadaev/laravel-eloquent-spatial package.
     *
     * @param array $postcodes
     * @return void
     */
    private function insertPostcodes(array $postcodes): void
    {
        $values = $bindings = [];

        foreach ($postcodes as $postcode) {
            $values[] = '(?, POINT(?, ?), ?, ?)';
            $bindings[] = $postcode['postcode'];
            $bindings[] = $postcode['coordinates']['longitude'];
            $bindings[] = $postcode['coordinates']['latitude'];
            $bindings[] = $postcode['created_at'];
            $bindings[] = $postcode['updated_at'];
        }

        $query = "INSERT INTO `postcodes` (
            `id`,
            `coordinates`,
            `created_at`,
            `updated_at`
        ) VALUES " . implode(',', $values) . "
        ON DUPLICATE KEY UPDATE
            `coordinates` = VALUES(`coordinates`),
            `updated_at` = VALUES(`updated_at`)";

        DB::statement($query, $bindings);
    }
}
