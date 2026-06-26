<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-dashboard
 * @version   1.3.17
 * @copyright Copyright (C) 2024 Mirasvit (https://mirasvit.com/)
 */


declare(strict_types=1);

namespace Mirasvit\Dashboard\Model;

use Magento\Framework\DataObject;
use Mirasvit\Dashboard\Api\Data\BlockInterface;
use Mirasvit\Dashboard\Model\Block\Config;

class Block extends DataObject implements BlockInterface
{
    public function getIdentifier(): string
    {
        $value = $this->getData(BlockInterface::IDENTIFIER);
        return $value ? $value : hash('sha256', (string)rand(1, intval(microtime(true))));
    }

    public function setIdentifier(string $value): BlockInterface
    {
        return $this->setData(BlockInterface::IDENTIFIER, $value);
    }

    public function getPos(): array
    {
        return $this->getData(BlockInterface::POS);
    }

    public function setPos(array $data): BlockInterface
    {
        return $this->setData(BlockInterface::POS, [(int)$data[0], (int)$data[1]]);
    }

    public function getSize(): array
    {
        return $this->getData(BlockInterface::SIZE);
    }

    public function setSize(array $data): BlockInterface
    {
        return $this->setData(BlockInterface::SIZE, [(int)$data[0], (int)$data[1]]);
    }

    public function getTitle(): string
    {
        return (string)$this->getData(BlockInterface::TITLE);
    }

    public function setTitle(string $value): BlockInterface
    {
        return $this->setData(BlockInterface::TITLE, $value);
    }

    public function getDescription(): string
    {
        return (string)$this->getData(BlockInterface::DESCRIPTION);
    }

    public function setDescription(string $value): BlockInterface
    {
        return $this->setData(BlockInterface::DESCRIPTION, $value);
    }

    public function getConfig(): Config
    {
        $value = $this->getData(BlockInterface::CONFIG);
        if ($value === null) {
            $value = [];
        }

        return new Config($value);
    }

    public function setConfig(array $value): BlockInterface
    {
        return $this->setData(BlockInterface::CONFIG, $value);
    }
}
