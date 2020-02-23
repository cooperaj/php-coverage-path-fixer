<?php

declare(strict_types=1);

namespace CoveragePathFixerTest\Service;

use CoveragePathFixer\Service\PathFixer;
use PHPUnit\Framework\TestCase;

/**
 * Class PathFixerTest
 *
 * @package CoveragePathFixerTest\Service
 *
 * @coversDefaultClass \CoveragePathFixer\Service\PathFixer
 */
class PathFixerTest extends TestCase
{
    /**
     * @test
     * @covers ::__construct
     * @covers ::fix
     */
    public function it_changes_prefixes_on_array_keys()
    {
        $one = new \stdClass();

        $fixture = [
            '/orig/prefix/one' => $one,
            '/orig/prefix/two' => new \stdClass(),
            '/unchanged/prefix/one' => new \stdClass()
        ];

        $sut = new PathFixer('/orig/prefix', '/new/prefix');

        $fixed = $sut->fix($fixture);

        $this->assertCount(count($fixture), $fixed);
        $this->assertArrayHasKey('/new/prefix/one', $fixed);
        $this->assertArrayHasKey('/new/prefix/two', $fixed);
        $this->assertArrayHasKey('/unchanged/prefix/one', $fixed);
        $this->assertEquals($one, $fixed['/new/prefix/one']);
    }
}
