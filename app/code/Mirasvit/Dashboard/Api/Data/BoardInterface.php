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

namespace Mirasvit\Dashboard\Api\Data;

interface BoardInterface
{
    const TABLE_NAME = 'mst_dashboard_board';

    const TYPE_PRIVATE = 'private';
    const TYPE_SHARED  = 'shared';

    const ID                = 'board_id';
    const IDENTIFIER        = 'identifier';
    const TITLE             = 'title';
    const TYPE              = 'type';
    const IS_DEFAULT        = 'is_default';
    const USER_ID           = 'user_id';
    const BLOCKS            = 'blocks';
    const BLOCKS_SERIALIZED = 'blocks_serialized';
    const DATE_RANGE        = 'date_range';
    const IS_MOBILE_ENABLED = 'is_mobile_enabled';
    const MOBILE_TOKEN      = 'mobile_token';
    const CREATED_AT        = 'created_at';
    const UPDATED_AT        = 'updated_at';

    /**
     * @return int
     */
    public function getId();

    public function getIdentifier(): string;

    public function setIdentifier(string $value): self;

    public function getTitle(): string;

    public function setTitle(string $title): self;

    public function getType(): string;

    public function setType(string $data): self;

    public function isDefault(): bool;

    public function setIsDefault(bool $data): self;

    public function getUserId(): int;

    public function setUserId(int $data): self;

    public function isMobileEnable(): bool;

    public function setIsMobileEnabled(bool $input): self;

    public function getMobileToken(): string;

    public function setMobileToken(string $input): self;

    /**
     * @return BlockInterface[]
     */
    public function getBlocks(): array;

    /**
     * @param BlockInterface[] $value
     * @return $this
     */
    public function setBlocks(array $value): self;

    public function setCreatedAt(string $createdAt): self;

    public function setUpdatedAt(string $updatedAt): self;

    public function getDateRange(): string;

    public function setDateRange(string $value): self;
}
