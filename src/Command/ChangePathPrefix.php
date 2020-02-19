<?php

declare(strict_types=1);

namespace CoveragePathFixer\Command;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RecursiveRegexIterator;
use RegexIterator;
use SebastianBergmann\CodeCoverage\CodeCoverage;
use SebastianBergmann\CodeCoverage\Filter;
use SebastianBergmann\CodeCoverage\Report\Clover;
use SebastianBergmann\CodeCoverage\Report\PHP;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ChangePathPrefix extends Command
{
    protected static $defaultName = 'fix';

    protected function configure(): void
    {
        $this->setDescription('Swaps a given path prefix with another in your coverage files')
            ->setHelp(
                'This command will recursively search for coverage (.cov) files and alter the code ' .
                'paths held within such that the files can be found. \n\n' .
                'e.g. "/app/src" can be swapped with e.g. "/home/ci/src"'
            )

            ->addArgument(
                'directory_to_search',
                InputArgument::REQUIRED,
                'The directory to recursively search for ".cov" files',
            )

            ->addArgument(
                'original_prefix',
                InputArgument::REQUIRED,
                'The orginal path prefix e.g. "/app/src"'
            )

            ->addArgument(
                'replacement_prefix',
                InputArgument::REQUIRED,
                'The new path prefix e.g. "/home/ci/src"'
            )

            ->addOption(
                'merge',
                'm',
                InputOption::VALUE_REQUIRED,
                'Merge the discovered files into the specified single output coverage (.cov) file'
            )

            ->addOption(
                'clover',
                'c',
                InputOption::VALUE_NONE,
                'Output an additional clover format coverage file alongside each .cov file that is processed'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $files = $this->findCoverageFiles($input->getArgument('directory_to_search'));

            $files = $this->iterateCoverageFiles(
                $files,
                $input->getArgument('original_prefix'),
                $input->getArgument('replacement_prefix')
            );

            if ($path = $input->getOption('merge')) {
                $files = $this->mergeCoverageFiles($files, $path);
            }

            $this->outputCoverageFiles($files, $input->getOption('clover'));
        } catch (\Exception $ex) {
            return $ex->getCode();
        }

        return 0;
    }

    private function changePrefix(array $data, string $originalPrefix, string $replacementPrefix): array
    {
        return array_combine(array_map(function($el) use ($originalPrefix, $replacementPrefix) {
            $el = preg_replace('#^' . $originalPrefix . '#', $replacementPrefix, $el);
            return $el;
        }, array_keys($data)), array_values($data));
    }

    private function iterateCoverageFiles(array $files, string $originalPrefix, string $replacementPrefix): array
    {
        return array_map(function(array $file) use ($originalPrefix, $replacementPrefix) {
            $coverage = $this->loadCoverageFile($file[0]);

            $data = $this->changePrefix($coverage->getData(), $originalPrefix, $replacementPrefix);
            $whiteList = $this->changePrefix(
                $coverage->filter()->getWhitelistedFiles(),
                $originalPrefix,
                $replacementPrefix
            );

            $filter = new Filter();
            $filter->setWhitelistedFiles($whiteList);

            $coverage = new CodeCoverage(null, $filter);
            $coverage->setData($data);

            return $coverage;
        }, $files);
    }

    private function findCoverageFiles(string $directory): array
    {
        $path = realpath($directory);

        $directory = new RecursiveDirectoryIterator($path);
        $iterator = new RecursiveIteratorIterator($directory);
        $filtered = new RegexIterator($iterator, '/^.+\.cov$/i', RecursiveRegexIterator::GET_MATCH);

        return iterator_to_array($filtered);
    }

    private function loadCoverageFile(string $file): CodeCoverage
    {
        $coverage = include $file;

        if (!($coverage instanceof CodeCoverage)) {
            unset($coverage);
            throw new \Exception('File with coverage extension not resolved to CodeCoverage class');
        }

        return $coverage;
    }

    private function mergeCoverageFiles(array $files, string $path): array
    {
        $coverage = new CodeCoverage();

        foreach ($files as $file => $coverage) {
            $coverage->merge($coverage);
        }

        return [$path => $coverage];
    }

    private function outputCoverageFiles(array $files, bool $asClover = false): void
    {
        array_walk($files, function($coverage, $path) use ($asClover) {
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