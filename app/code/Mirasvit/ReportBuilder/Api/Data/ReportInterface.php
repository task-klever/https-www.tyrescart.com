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
 * @package   mirasvit/module-report-builder
 * @version   1.1.8
 * @copyright Copyright (C) 2024 Mirasvit (https://mirasvit.com/)
 */


declare(strict_types=1);

namespace Mirasvit\ReportBuilder\Api\Data;

use Mirasvit\Report\Api\Data\ReportInterface as CoreReportInterface;

interface ReportInterface extends CoreReportInterface
{
    const TABLE_NAME = 'mst_report_builder_report';

    const ID            = 'report_id';
    const NAME          = 'title';
    const CONFIG        = 'config';
    const USER_ID       = 'user_id';

    /**
     * @return int
     */
    public function getId();

    public function getUserId(): int;

    public function setUserId(int $value): CoreReportInterface;

    public function setName(string $value): CoreReportInterface;

    public function getReportIdentifier(): ?string;

    public function setReportIdentifier(string $identifier): CoreReportInterface;

    public function getIsSharingEnabled(): bool;

    public function setIsSharingEnabled(bool $value): CoreReportInterface;

    public function getShareIdentifier(): ?string;

    public function setShareIdentifier(string $value): CoreReportInterface;

    public function getIsCustomized(): bool;

    public function setIsCustomized(bool $value): CoreReportInterface;

    public function getPageSize(): int;

    public function setPageSize(int $value): CoreReportInterface;
}
