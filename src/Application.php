<?php

declare(strict_types=1);

namespace CoveragePathFixer;

use CoveragePathFixer\Command\ChangePathPrefix;
use Symfony\Component\Console\Application as ConsoleApplication;

class Application extends ConsoleApplication
{
    public function __construct(string $name = 'CoveragePathFixer', string $version = 'UNKNOWN')
    {
        parent::__construct($name, $version);

        $command = new ChangePathPrefix();
        $this->add($command);
        $this->setDefaultCommand($command->getName(), true);
    }
}