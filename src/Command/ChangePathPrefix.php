<?php

declare(strict_types=1);

namespace CoveragePathFixer\Command;

use CoveragePathFixer\Service\{CoverageLoader, FileFinder, FileWriter, PathFixer};
use SebastianBergmann\CodeCoverage\{CodeCoverage, Filter};
use Symfony\Component\Console\{Command\Command,
    Input\InputArgument,
    Input\InputInterface,
    Input\InputOption,
    Output\OutputInterface};

class ChangePathPrefix extends Command
{
    /**
     * @var string Name of the command
     */
    protected static $defaultName = 'fix';

    /**
     * @var FileFinder
     */
    private $finder;

    /**
     * @var CoverageLoader
     */
    private $loader;

    /**
     * @var FileWriter
     */
    private $writer;

    public function __construct(FileFinder $finder, CoverageLoader $loader, FileWriter $writer)
    {
        parent::__construct(self::$defaultName);

        $this->finder = $finder;
        $this->loader = $loader;
        $this->writer = $writer;
    }

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
        $fixer = new PathFixer(
            $input->getArgument('original_prefix'),
            $input->getArgument('replacement_prefix')
        );

        try {
            $files = $this->finder->findCoverage($input->getArgument('directory_to_search'));
            $output->writeln(sprintf('%d .cov files found', count($files)));

            $files = $this->iterateCoverageFiles(
                $files,
                $fixer
            );

            $this->writer->setFiles($files);

            if ($path = $input->getOption('merge')) {
                $this->writer->merge($path);
            }

            $this->writer->write($input->getOption('clover'));
        } catch (\Exception $ex) {
            $output->writeln($ex->getMessage());
            return 1;
        }

        return 0;
    }

    protected function iterateCoverageFiles(array $files, PathFixer $fixer): array
    {
        return array_map(function(array $file) use ($fixer) {
            $coverage = $this->loader->loadCoverage($file[0]);

            $data = $fixer->fix($coverage->getData());
            $whiteList = $fixer->fix( $coverage->filter()->getWhitelistedFiles());

            $filter = new Filter();
            $filter->setWhitelistedFiles($whiteList);

            $coverage = new CodeCoverage(null, $filter);
            $coverage->setData($data);

            return $coverage;
        }, $files);
    }
}