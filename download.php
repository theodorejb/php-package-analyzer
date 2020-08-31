<?php

declare(strict_types=1);

require 'vendor/autoload.php';

if ($argc < 3) {
    echo "Usage: download.php first-package last-package\n";
    return;
}

$downloader = new \theodorejb\PackageAnalyzer\Downloader();

foreach ($downloader->getPopularPackages((int) $argv[1], (int) $argv[2]) as $i => $name) {
    echo "[$i] $name\n";
    $downloader->downloadPackage($name, __DIR__ . '/zipped');
}
