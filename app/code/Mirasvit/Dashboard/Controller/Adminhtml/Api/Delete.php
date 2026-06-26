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



namespace Mirasvit\Dashboard\Controller\Adminhtml\Api;

use Magento\Backend\App\Action\Context;
use Mirasvit\Dashboard\Repository\BoardRepository;
use Mirasvit\Report\Controller\Adminhtml\Api\AbstractApi;

class Delete extends AbstractApi
{
    /**
     * @var BoardRepository
     */
    private $boardRepository;

    /**
     * Delete constructor.
     * @param BoardRepository $boardRepository
     * @param Context $context
     */
    public function __construct(
        BoardRepository $boardRepository,
        \Magento\Framework\Serialize\Serializer\Json $serializer,
        Context $context
    ) {
        $this->boardRepository = $boardRepository;
        $this->serializer = $serializer;
        parent::__construct($context, $serializer);
    }

    /**
     * @return \Magento\Framework\App\Response\Http|\Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Framework\App\Response\Http $jsonResponse */
        $jsonResponse = $this->getResponse();

        $identifier = $this->getRequest()->getParam('identifier');

        $model = $this->boardRepository->getByIdentifier($identifier);

        if ($model) {
            try {
                $this->boardRepository->delete($model);
            } catch (\Exception $e) {
                return $jsonResponse->representJson($this->serializer->serialize([
                    'success' => false,
                    'message' => $e->getMessage(),
                ]));
            }
        }

        return $jsonResponse->representJson($this->serializer->serialize([
            'success' => true,
            'message' => 'Board was removed.',
        ]));
    }
}
