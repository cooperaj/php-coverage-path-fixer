<?php

declare(strict_types=1);

namespace CoveragePathFixer\Service;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RecursiveRegexIterator;
use RegexIterator;

class FileFinder
{
    /**
     * @param string $directory
     * @return string[]
     */
    public function findCoverage(string $directory): array
    {
        $path = realpath($directory);

        $directory = new RecursiveDirectoryIterator($path);
        $iterator = new RecursiveIteratorIterator($directory);
        $filtered = new RegexIterator($iterator, '/^.+\.cov$/i', RecursiveRegexIterator::GET_MATCH);

        /** @var string[] */
        return array_keys(iterator_to_array($filtered));
    }
}