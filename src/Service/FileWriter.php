<?php

declare(strict_types=1);

namespace CoveragePathFixer\Service;

use SebastianBergmann\CodeCoverage\CodeCoverage;
use SebastianBergmann\CodeCoverage\Report\Clover;
use SebastianBergmann\CodeCoverage\Report\PHP;

class FileWriter
{
    /**
     * @var array<string, CodeCoverage>
     */
    private $files;

    /**
     * FileWriter constructor.
     *
     * @param array<string, CodeCoverage> $files
     */
    public function __construct(array $files = [])
    {
        $this->files = $files;
    }

    /**
     * Fluent method that adds additional coverage files to the list of files to write
     *
     * @param string $path
     * @param CodeCoverage $coverage
     * @return FileWriter
     */
    public function addFile(string $path, CodeCoverage $coverage): FileWriter
    {
        $this->files[$path] = $coverage;

        return $this;
    }

    /**
     * @return array<string, CodeCoverage>
     */
    public function getFiles(): array
    {
        return $this->files;
    }

    /**
     * Fluent method to merge CodeCoverage objects into a single coverage record
     *
     * @param string $path The path into which the merged file will be written
     * @return FileWriter
     */
    public function merge(string $path): FileWriter
    {
        $combinedCoverage = new CodeCoverage();

        foreach ($this->files as $file => $coverage) {
            $combinedCoverage->merge($coverage);
        }

        $this->files = [$path => $combinedCoverage];

        return $this;
    }

    /**
     * Fluent method that allows the replacement of the list of files to write
     *
     * @param array<string, CodeCoverage> $files
     * @return FileWriter
     */
    public function setFiles(array $files): FileWriter
    {
        $this->files = $files;

        return $this;
    }

    /**
     * Writes out the files as defined
     *
     * Optionally writes out Clover format files alongside each .cov file
     *
     * @param bool $asClover Also write out Clover XML files
     */
    public function write(bool $asClover = false): void
    {
        /** @psalm-suppress MixedPropertyTypeCoercion */
        array_walk($this->files, function(CodeCoverage $coverage, string $path) use ($asClover) {
            if ($asClover) {
                $filename = basename($path, '.cov');
                $directory = dirname($path);

                $reportWriter = new Clover();
                $reportWriter->process($coverage, $directory . DIRECTORY_SEPARATOR . $filename . '.xml');
            }

            $reportWriter = new PHP();
            $reportWriter->process($coverage, $path);
        });
    }
}