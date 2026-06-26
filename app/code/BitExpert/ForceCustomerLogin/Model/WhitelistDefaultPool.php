<?php

/*
 * This file is part of the Force Login module for Magento2.
 *
 * (c) bitExpert AG
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BitExpert\ForceCustomerLogin\Model;

/**
 * Class WhitelistEntryDefaultPool
 *
 * @package BitExpert\ForceCustomerLogin\Model
 * @codingStandardsIgnoreFile
 */
class WhitelistDefaultPool
{
    private array $entries = [];

    /**
     * Pool constructor
     *
     * @param array[] $entries
     */
    public function __construct(
        array $entries = []
    ) {
        $this->entries = $entries;
    }

    /**
     * Get default entries from pool
     *
     * @return array[string, array]
     */
    public function getEntries(): array
    {
        return $this->entries;
    }
}
