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
 * @package   mirasvit/module-sorting
 * @version   1.3.15
 * @copyright Copyright (C) 2023 Mirasvit (https://mirasvit.com/)
 */


declare(strict_types=1);

namespace Mirasvit\Sorting\Ui\RankingFactor\Form\Control;

use Magento\Backend\Block\Widget\Context;
use Mirasvit\Sorting\Api\Data\IndexInterface;
use Mirasvit\Sorting\Api\Data\RankingFactorInterface;
use Mirasvit\Sorting\Model\Indexer;
use Mirasvit\Sorting\Repository\RankingFactorRepository;

class PreviewButton extends ButtonAbstract
{
    private $repository;
    
    public function __construct(RankingFactorRepository $repository, Context $context)
    {
        $this->repository = $repository;

        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    public function getButtonData()
    {
        $ns = 'sorting_rankingFactor_form.sorting_rankingFactor_form';

        $resource = $this->repository->getCollection()->getResource();
        $connection = $resource->getConnection();

        $isFirstReindexMade = $this->getId() && $connection->tableColumnExists(
            $resource->getTable(IndexInterface::TABLE_NAME),
            Indexer::getScoreColumnById((int)$this->getId())
        );

        return [
            'label'          => __('Preview'),
            'class'          => 'preview',
            'sort_order'     => 30,
            'on_click'       => '',
            'disabled'       => !$isFirstReindexMade,
            'data_attribute' => [
                'mage-init' => [
                    'buttonAdapter' => [
                        'actions' => [
                            [
                                'targetName' => $ns . '.preview_modal',
                                'actionName' => 'toggleModal',
                            ],
                            [
                                'targetName' => $ns . '.preview_modal.preview_listing',
                                'actionName' => 'render',
                            ],
                            [
                                'targetName' => $ns . '.preview_modal.preview_listing',
                                'actionName' => 'reload',
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
