<?php

declare(strict_types=1);

namespace CoveragePathFixerTest\Command;

use CoveragePathFixer\Application;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * Class ChangePathPrefixTest
 *
 * @package CoveragePathFixerTest\Command
 *
 * @coversDefaultClass \CoveragePathFixer\Command\ChangePathPrefix
 */
class ChangePathPrefixTest extends TestCase
{
    /**
     * @test
     * @covers ::execute
     * @covers \CoveragePathFixer\Application::__construct
     * @covers \CoveragePathFixer\Command\ChangePathPrefix::__construct
     * @covers \CoveragePathFixer\Command\ChangePathPrefix::configure
     * @covers \CoveragePathFixer\Command\ChangePathPrefix::iterateCoverageFiles
     * @covers \CoveragePathFixer\Service\CoverageLoader::loadCoverage
     * @covers \CoveragePathFixer\Service\FileFinder::findCoverage
     * @covers \CoveragePathFixer\Service\FileWriter::__construct
     * @covers \CoveragePathFixer\Service\FileWriter::setFiles
     * @covers \CoveragePathFixer\Service\FileWriter::write
     * @covers \CoveragePathFixer\Service\PathFixer::__construct
     * @covers \CoveragePathFixer\Service\PathFixer::fix
     */
    public function its_a_command()
    {
        $fs = vfsStream::setup(
            'root',
            null,
            [
                'path' => [
                    'to' => [
                        'coverage.cov' => '<?php return new \SebastianBergmann\CodeCoverage\CodeCoverage();',
                    ],
                ]
            ]
        );


        $application = new Application();
        $command = $application->find('fix');

        $commandTester = new CommandTester($command);
        $commandTester->execute(
            [
                'directory_to_search' => $fs->getChild('path')->url(),
                'original_prefix' => '/path/to',
                'replacement_prefix' => '/path/from'
            ]
        );

        $output = $commandTester->getDisplay();

        $this->assertStringContainsString('1 .cov files found', $output);
    }
}