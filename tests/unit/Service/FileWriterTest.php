<?php

declare(strict_types=1);

namespace CoveragePathFixerTest\Service;

use CoveragePathFixer\Service\FileWriter;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use SebastianBergmann\CodeCoverage\CodeCoverage;

/**
 * Class FileWriterTest
 *
 * @package CoveragePathFixerTest\Service
 *
 * @coversDefaultClass \CoveragePathFixer\Service\FileWriter
 */
class FileWriterTest extends TestCase
{
    /**
     * @test
     * @covers ::__construct
     * @covers ::getFiles
     */
    public function it_can_be_initialised_with_files()
    {
        $fixture = [
            '/path/to/coverage.cov' => new CodeCoverage(),
            '/path/to/other.cov' => new CodeCoverage()
        ];

        $sut = new FileWriter($fixture);

        $this->assertEquals($fixture, $sut->getFiles());
    }

    /**
     * @test
     * @covers ::__construct
     * @covers ::addFile
     * @covers ::getFiles
     */
    public function additional_files_can_be_added()
    {
        $fixture = [
            '/path/to/coverage.cov' => new CodeCoverage(),
            '/path/to/other.cov' => new CodeCoverage()
        ];

        $sut = new FileWriter($fixture);

        $fluent = $sut->addFile('/path/to/addtional.cov', new CodeCoverage());

        $this->assertEquals($sut, $fluent);
        $this->assertArrayHasKey('/path/to/addtional.cov', $fluent->getFiles());
    }

    /**
     * @test
     * @covers ::__construct
     * @covers ::setFiles
     * @covers ::getFiles
     */
    public function file_set_can_be_replaced()
    {
        $fixture = [
            '/path/to/coverage.cov' => new CodeCoverage(),
            '/path/to/other.cov' => new CodeCoverage()
        ];

        $sut = new FileWriter();

        $fluent = $sut->setFiles($fixture);

        $this->assertEquals($sut, $fluent);
        $this->assertEquals($fixture, $fluent->getFiles());
    }

    /**
     * @test
     * @covers ::__construct
     * @covers ::merge
     * @covers ::getFiles
     */
    public function it_reduces_a_fileset_to_a_single_entry_when_merging()
    {
        $fixture = [
            '/path/to/coverage.cov' => new CodeCoverage(),
            '/path/to/other.cov' => new CodeCoverage()
        ];

        $sut = new FileWriter($fixture);

        $fluent = $sut->merge('/path/to/merge.cov');

        $this->assertEquals($sut, $fluent);
        $this->assertCount(1, $fluent->getFiles());
        $this->assertArrayHasKey('/path/to/merge.cov', $fluent->getFiles());
    }

    /**
     * @test
     * @covers ::__construct
     * @covers ::write
     */
    public function it_overwrites_old_cov_files_with_new()
    {
        $fs = vfsStream::setup(
            'root',
            null,
            [
                'path' => [
                    'to' => [
                        'coverage.cov' => 'oldcontent',
                        'other.cov' => 'oldcontent'
                    ]
                ]
            ]
        );

        $fixture = [
            $fs->getChild('path/to/coverage.cov')->url() => new CodeCoverage(),
            $fs->getChild('path/to/other.cov')->url() => new CodeCoverage()
        ];

        $sut = new FileWriter($fixture);

        $sut->write();

        $coverage = include $fs->getChild('path/to/coverage.cov')->url();
        $this->assertInstanceOf(CodeCoverage::class, $coverage);

        $other = include $fs->getChild('path/to/other.cov')->url();
        $this->assertInstanceOf(CodeCoverage::class, $other);
    }

    /**
     * @test
     * @covers ::__construct
     * @covers ::write
     */
    public function it_optionally_writes_out_clover_files()
    {
        $fs = vfsStream::setup(
            'root',
            null,
            [
                'path' => [
                    'to' => [
                        'coverage.cov' => 'oldcontent',
                        'other.cov' => 'oldcontent'
                    ]
                ]
            ]
        );

        $fixture = [
            $fs->getChild('path/to/coverage.cov')->url() => new CodeCoverage(),
            $fs->getChild('path/to/other.cov')->url() => new CodeCoverage()
        ];

        $sut = new FileWriter($fixture);

        $sut->write(true);

        $this->assertTrue($fs->hasChild('path/to/coverage.xml'));
        $this->assertTrue($fs->hasChild('path/to/other.xml'));
    }
}
