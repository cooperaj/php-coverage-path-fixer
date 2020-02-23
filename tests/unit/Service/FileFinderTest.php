<?php

declare(strict_types=1);

namespace CoveragePathFixerTest\Service;

use CoveragePathFixer\Service\FileFinder;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;

require_once 'realpath_override.php';

/**
 * Class FileFinderTest
 *
 * @package CoveragePathFixerTest\Service
 *
 * @coversDefaultClass \CoveragePathFixer\Service\FileFinder
 */
class FileFinderTest extends TestCase
{
    /**
     * @test
     * @covers ::findCoverage
     */
    public function it_recursively_finds_cov_files()
    {
        $fs = vfsStream::setup(
            'root',
            null,
            [
                'path' => [
                    'to' => [
                        'coverage.cov' => 'oldcontent',
                        'other.cov' => 'oldcontent',
                        'other' => [
                            'final.cov' => 'oldcontent'
                        ]
                    ],
                ]
            ]
        );

        $sut = new FileFinder();

        $result = $sut->findCoverage($fs->url());

        $this->assertCount(3, $result);

        $keys = array_keys($result);
        $this->assertContainsEquals('vfs://root/path/to/coverage.cov', $keys);
        $this->assertContainsEquals('vfs://root/path/to/other.cov', $keys);
        $this->assertContainsEquals('vfs://root/path/to/other/final.cov', $keys);
    }
}
