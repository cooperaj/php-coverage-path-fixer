<?php

declare(strict_types=1);

namespace CoveragePathFixerTest\Service;

use CoveragePathFixer\Service\CoverageLoader;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use SebastianBergmann\CodeCoverage\CodeCoverage;

/**
 * Class CoverageLoaderTest
 *
 * @package CoveragePathFixerTest\Service
 *
 * @coversDefaultClass \CoveragePathFixer\Service\CoverageLoader
 */
class CoverageLoaderTest extends TestCase
{
    /**
     * @test
     * @covers ::loadCoverage
     */
    public function it_loads_a_valid_codecoverage_object_from_file()
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

        $sut = new CoverageLoader();

        $coverage = $sut->loadCoverage($fs->getChild('path/to/coverage.cov')->url());

        $this->assertInstanceOf(CodeCoverage::class, $coverage);
    }

    /**
     * @test
     * @covers ::loadCoverage
     */
    public function it_thows_an_exception_if_the_file_isnt_valid()
    {
        $fs = vfsStream::setup(
            'root',
            null,
            [
                'path' => [
                    'to' => [
                        'coverage.cov' => '<?php //not a coverage file',
                    ],
                ]
            ]
        );

        $sut = new CoverageLoader();

        $this->expectException(\Exception::class);
        $sut->loadCoverage($fs->getChild('path/to/coverage.cov')->url());
    }
}
