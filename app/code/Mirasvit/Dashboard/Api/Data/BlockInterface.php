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

use Mirasvit\Dashboard\Model\Block\Config;

interface BlockInterface
{
    const IDENTIFIER  = 'identifier';
    const POS         = 'pos';
    const SIZE        = 'size';
    const TITLE       = 'title';
    const DESCRIPTION = 'description';
    const CONFIG      = 'config';
    
    const RENDERER_SINGLE = 'single';
    const RENDERER_TABLE  = 'table';
    const RENDERER_CHART  = 'chart';
    const RENDERER_HTML   = 'html';

    public function getIdentifier(): string;

    public function setIdentifier(string $value): self;

    public function getPos(): array;

    public function setPos(array $pos): self;

    public function getSize(): array;

    public function setSize(array $size): self;

    public function getTitle(): string;

    public function setTitle(string $value): self;

    public function getDescription(): string;

    public function setDescription(string $value): self;

    public function getConfig(): Config;

    public function setConfig(array $value): self;
}