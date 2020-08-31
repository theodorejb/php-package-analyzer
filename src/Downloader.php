<?php

declare(strict_types=1);

namespace theodorejb\PackageAnalyzer;

class Downloader
{
    private string $platform;

    public function __construct()
    {
        $this->platform = explode(' ', php_uname('s'))[0];
    }

    public function getPopularPackages(int $first, int $last): \Generator
    {
        $pageSize = 15;
        $page = intdiv($first, $pageSize);
        $id = $page * $pageSize;

        while (true) {
            $page++;
            $url = 'https://packagist.org/explore/popular.json?page=' . $page;
            $json = json_decode(file_get_contents($url));

            foreach ($json->packages as $package) {
                yield $id => $package->name;
                $id++;

                if ($id >= $last) {
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

        $url = "https://repo.packagist.org/p/{$lcName}.json";
        $json = json_decode(file_get_contents($url), true);
        $versions = $json['packages'][$lcName];

        if (isset($versions['dev-master'])) {
            $version = 'dev-master';
        } else {
            $version = array_key_last($versions);
        }

        $package = $versions[$version];

        if ($package['dist'] === null) {
            echo "Skipping due to missing dist\n";
            return;
        }

        $dist = $package['dist']['url'];

        echo "Downloading $version...\n";
        $dir = dirname($file);

        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        if ($this->platform === 'Windows') {
            $cmd = "powershell Invoke-WebRequest $dist -OutFile $file";
        } else {
            $cmd = "wget $dist -O $file";
        }

        exec($cmd, $execOutput, $execRetval);

        if ($execRetval !== 0) {
            throw new \Exception("Failed to download package: " . var_export($execOutput, true));
        }
    }
}
