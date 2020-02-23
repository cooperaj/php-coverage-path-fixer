<?php

declare(strict_types=1);

namespace CoveragePathFixer\Service;

use SebastianBergmann\CodeCoverage\CodeCoverage;

class CoverageLoader
{
    /**
     * @param string $file
     * @return CodeCoverage
     * @throws \Exception
     */
    public function loadCoverage(string $file): CodeCoverage
    {
        $coverage = include $file;

        if (!($coverage instanceof CodeCoverage)) {
            unset($coverage);
            throw new \Exception('File with coverage extension not resolved to CodeCoverage class');
        }

        return $coverage;
    }
}