<?php

declare(strict_types=1);

namespace theodorejb\PackageAnalyzer;

use Generator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class Analyzer
{
    public function getPhpFiles(string $path): Generator
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($path),
            RecursiveIteratorIterator::LEAVES_ONLY
        );

        /** @var \DirectoryIterator $file */
        foreach ($iterator as $file) {
            $path = $file->getPathname();

            if (preg_match('/\.php$/', $path)) {
                yield $path;
            }
        }
    }
}
