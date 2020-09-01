<?php

declare(strict_types=1);

use theodorejb\PackageAnalyzer\Downloader;

require __DIR__ . '/vendor/autoload.php';

if ($argc < 3) {
    echo "Usage: download.php first-package last-package\n";
    exit(1);
}

$downloader = new Downloader();

foreach ($downloader->getPopularPackages((int) $argv[1], (int) $argv[2]) as $index => $package) {
    echo "[{$index}] {$package}\n";
    $downloader->downloadPackage($package, __DIR__ . '/zipped', __DIR__ . '/extracted');
}
