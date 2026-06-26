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

use Magento\Framework\Model\AbstractModel;
use Mirasvit\Dashboard\Api\Data\BlockInterface;
use Mirasvit\Dashboard\Api\Data\BoardInterface;

class Board extends AbstractModel implements BoardInterface
{
    private $serializer;

    public function __construct(
        \Magento\Framework\Serialize\Serializer\Json $serializer
    ) {
        $this->serializer = $serializer;
    }
    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init(ResourceModel\Board::class);
    }

    public function getId(): ?int
    {
        return $this->getData(self::ID) ? (int)$this->getData(self::ID) : null;
    }

    public function getIdentifier(): string
    {
        return $this->getData(BoardInterface::IDENTIFIER);
    }

    public function setIdentifier(string $value): BoardInterface
    {
        return $this->setData(BoardInterface::IDENTIFIER, $value);
    }

    public function getTitle(): string
    {
        return $this->getData(BoardInterface::TITLE);
    }

    public function setTitle(string $title): BoardInterface
    {
        return $this->setData(BoardInterface::TITLE, $title);
    }

    public function getType(): string
    {
        return $this->getData(BoardInterface::TYPE);
    }

    public function setType(string $data): BoardInterface
    {
        return $this->setData(BoardInterface::TYPE, $data);
    }

    public function isDefault(): bool
    {
        return (bool)$this->getData(BoardInterface::IS_DEFAULT);
    }

    public function setIsDefault(bool $data): BoardInterface
    {
        return $this->setData(BoardInterface::IS_DEFAULT, $data);
    }

    public function getUserId(): int
    {
        return (int)$this->getData(BoardInterface::USER_ID);
    }

    public function setUserId(int $data): BoardInterface
    {
        return $this->setData(BoardInterface::USER_ID, $data);
    }

    public function isMobileEnable(): bool
    {
        return (bool)$this->getData(BoardInterface::IS_MOBILE_ENABLED);
    }

    public function setIsMobileEnabled(bool $input): BoardInterface
    {
        return $this->setData(BoardInterface::IS_MOBILE_ENABLED, $input);
    }

    public function getMobileToken(): string
    {
        return $this->getData(BoardInterface::MOBILE_TOKEN);
    }

    public function setMobileToken(string $input): BoardInterface
    {
        return $this->setData(BoardInterface::MOBILE_TOKEN, $input);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlocks(): array
    {
        $blocks = [];
        try {
            $data = $this->serializer->unserialize($this->getData(BoardInterface::BLOCKS_SERIALIZED));
            if ($data === null) {
                $data = [];
            }

            foreach ($data as $item) {
                $blocks[] = new Block($item);
            }
        } catch (\Exception $e) {
            $blocks = [];
        }

        return $blocks;
    }

    /**
     * {@inheritdoc}
     */
    public function setBlocks(array $blocks): BoardInterface
    {
        $data = [];

        foreach ($blocks as $item) {
            $data[] = [
                BlockInterface::IDENTIFIER  => $item->getIdentifier(),
                BlockInterface::TITLE       => $item->getTitle(),
                BlockInterface::SIZE        => $item->getSize(),
                BlockInterface::POS         => $item->getPos(),
                BlockInterface::DESCRIPTION => $item->getDescription(),
                BlockInterface::CONFIG      => $item->getConfig()->getData(),
            ];
        }

        return $this->setData(BoardInterface::BLOCKS_SERIALIZED, $this->serializer->serialize($data));
    }

    public function setCreatedAt(string $createdAt): BoardInterface
    {
        $this->setData(self::CREATED_AT, $createdAt);

        return $this;
    }

    public function setUpdatedAt(string $updatedAt): BoardInterface
    {
        $this->setData(self::UPDATED_AT, $updatedAt);

        return $this;
    }

    public function getDateRange(): string
    {
        return $this->getData(self::DATE_RANGE);
    }

    public function setDateRange(string $value): BoardInterface
    {
        return $this->setData(self::DATE_RANGE, $value);
    }
}
