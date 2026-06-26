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



namespace Mirasvit\ReportBuilder\Controller\Adminhtml\Api;

use Magento\Backend\App\Action\Context;
use Mirasvit\Report\Controller\Adminhtml\Api\AbstractApi;
use Mirasvit\ReportBuilder\Repository\ReportRepository as BuilderReportRepository;

class Save extends AbstractApi
{
    /**
     * @var BuilderReportRepository
     */
    private $builderReportRepository;
    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    private $serializer;

    /**
     * Save constructor.
     * @param BuilderReportRepository $builderReportRepository
     * @param Context $context
     */
    public function __construct(
        BuilderReportRepository $builderReportRepository,
        \Magento\Framework\Serialize\Serializer\Json $serializer,
        Context $context
    ) {
        $this->builderReportRepository = $builderReportRepository;
        $this->serializer = $serializer;
        parent::__construct($context, $serializer);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        $request = $this->getRequest();

        $identifier = $request->getParam('identifier');

        $model = $this->builderReportRepository->get($identifier);

        if (!$model || !$model->getId()) { // saving predefined reports
            $model = $this->builderReportRepository->getByReportIdentifier($identifier);

            if (!$model) {
                $model = $this->builderReportRepository->create();
            }

            $model->setReportIdentifier($identifier);
        }

        $model->setName($request->getParam('title'));
        $model->setUserId($this->builderReportRepository->getUserId());

        $model->setColumns($request->getParam('columns', []))
            ->setDimensions($request->getParam('dimensions', []))
            ->setInternalFilters($request->getParam('internalFilters', []))
            ->setPrimaryDimensions($request->getParam('primaryDimensions', []))
            ->setPrimaryFilters($request->getParam('primaryFilters', []))
            ->setFilters($request->getParam('filters', []))
            ->setPageSize($request->getParam('pageSize', 20))
            ->setTimeRange($request->getParam('timeRange', ''));

        $model->setIsSharingEnabled($request->getParam('isShareEnabled', false));

        if ($model->getIsSharingEnabled() && !$model->getShareIdentifier()) {
            $model->setShareIdentifier(sha1($model->getName() . time()));
        }

        $model->setSortOrders($request->getParam('sortOrders', []));

        $this->builderReportRepository->save($model);

        /** @var \Magento\Framework\App\Response\Http $jsonResponse */
        $jsonResponse = $this->getResponse();
        $jsonResponse->representJson($this->serializer->serialize([
            'success' => true,
            'message' => 'Report was saved.',
        ]));
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Mirasvit_ReportBuilder::save');
    }
}
