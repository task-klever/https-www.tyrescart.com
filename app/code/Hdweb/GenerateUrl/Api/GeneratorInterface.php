<?php

declare(strict_types=1);

namespace Hdweb\GenerateUrl\Api;

interface GeneratorInterface
{
    /**
     * Generate LLMs.txt content
     *
     * @param int|null $storeId
     * @return string
     */
    public function generate(?int $storeId = null): string;
}

