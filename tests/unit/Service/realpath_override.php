<?php

declare(strict_types=1);

namespace CoveragePathFixer\Service;

/**
 * Override php's implementation of realpath in the Service namespace so that when the
 * tests are running vfsStream can be used.
 *
 * @param string $path
 * @return string
 */
function realpath($path) {
    return $path;
}
