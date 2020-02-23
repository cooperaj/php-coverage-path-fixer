<?php

declare(strict_types=1);

namespace CoveragePathFixer;

use CoveragePathFixer\Command\ChangePathPrefix;
use CoveragePathFixer\Service\CoverageLoader;
use CoveragePathFixer\Service\FileFinder;
use CoveragePathFixer\Service\FileWriter;
use Symfony\Component\Console\Application as ConsoleApplication;

class Application extends ConsoleApplication
{
    public function __construct(string $name = 'CoveragePathFixer', string $version = 'UNKNOWN')
    {
        parent::__construct($name, $version);

        $finder = new FileFinder();
        $loader = new CoverageLoader();
        $writer = new FileWriter();

        $command = new ChangePathPrefix($finder, $loader, $writer);
        $this->add($command);
        $this->setDefaultCommand($command->getName() ?? '', true);
    }
}