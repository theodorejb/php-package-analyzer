<?php

declare(strict_types=1);

namespace theodorejb\PackageAnalyzer;

use Exception;
use Generator;

class Downloader
{
    public const PACKAGIST_POPULAR_URL = 'https://packagist.org/explore/popular.json';

    public const PACKAGIST_PACKAGE_DATA_BASE_URL = 'https://repo.packagist.org/p/';

    private string $platform;

    public function __construct()
    {
        $this->platform = explode(' ', php_uname('s'))[0];
    }

    public function getPopularPackages(int $first, int $last): Generator
    {
        $pageSize = 15;
        $page = intdiv($first, $pageSize);
        $id = $page * $pageSize;

        while (true) {
            $page++;
            $json = json_decode(file_get_contents(self::PACKAGIST_POPULAR_URL . '?' . http_build_query(['page' => $page])));

            foreach ($json->packages as $package) {
                yield $id => $package->name;
                $id++;

                if ($id === $last) {
                    return;
                }
            }
        }
    }

    public function downloadPackage(string $name, string $directory): void
    {
        $lcName = strtolower($name);
        $file = "{$directory}/{$lcName}.zip";

        if (file_exists($file)) {
            return;
        }

        $json = json_decode(file_get_contents(self::PACKAGIST_PACKAGE_DATA_BASE_URL . "{$lcName}.json"), true);
        $versions = $json['packages'][$lcName];

        $version = isset($versions['dev-master'])
            ? 'dev-master'
            : array_key_last($versions);

        $package = $versions[$version];

        if ($package['dist'] === null) {
            echo "Skipping due to missing dist\n";
            return;
        }

        $dist = $package['dist']['url'];

        echo "Downloading {$version}...\n";
        $dir = dirname($file);

        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        $cmd =  $this->platform === 'Windows'
            ? "powershell Invoke-WebRequest {$dist} -OutFile {$file}"
            : "wget {$dist} -O {$file}";

        exec($cmd, $execOutput, $execRetval);

        if ($execRetval !== 0) {
            throw new Exception("Failed to download package: " . var_export($execOutput, true));
        }
    }
}
