<?php

declare(strict_types=1);

namespace CoveragePathFixer\Service;

class PathFixer
{
    /**
     * @var string
     */
    private $originalPrefix;

    /**
     * @var string
     */
    private $replacementPrefix;

    /**
     * PathFixer constructor.
     *
     * @param string $originalPrefix
     * @param string $replacementPrefix
     */
    public function __construct(string $originalPrefix, string $replacementPrefix)
    {
        $this->originalPrefix = $originalPrefix;
        $this->replacementPrefix = $replacementPrefix;
    }

    /**
     * @param array $data
     * @return array
     */
    public function fix(array $data): array
    {
        $originalPrefix = $this->originalPrefix;
        $replacementPrefix = $this->replacementPrefix;

        return array_combine(array_map(function(string $el) use ($originalPrefix, $replacementPrefix) {
            $el = preg_replace('#^' . $originalPrefix . '#', $replacementPrefix, $el);
            return $el;
        }, array_keys($data)), array_values($data));
    }
}