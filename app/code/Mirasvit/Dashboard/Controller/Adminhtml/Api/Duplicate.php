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


namespace Mirasvit\Dashboard\Controller\Adminhtml\Api;


use Magento\Backend\App\Action\Context;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\View\Result\PageFactory;
use Mirasvit\Dashboard\Controller\Adminhtml\Dashboard;
use Mirasvit\Dashboard\Repository\BoardRepository;

class Duplicate extends Dashboard
{
    private $sessionManager;

    public function __construct(
        BoardRepository $boardRepository,
        Context $context,
        PageFactory $resultPageFactory,
        SessionManagerInterface $sessionManager
    ) {
        $this->sessionManager = $sessionManager;

        parent::__construct($boardRepository, $context, $resultPageFactory);
    }

    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();

        $identifier = $this->getRequest()->getParam('identifier');
        $dashboard  = $this->boardRepository->getByIdentifier($identifier);

        if (!$dashboard) {
            $this->messageManager->addErrorMessage((string)__(
                'Dashboard with identifier "%1" does not exist',
                $identifier
            ));

            return $resultRedirect->setRefererUrl();
        }

        $duplicate = $this->boardRepository->create();
        $newBlocks = [];

        foreach ($dashboard->getBlocks() as $block) {
            $block->setIdentifier(substr(hash('sha256', $block->getIdentifier()), 0, 16));

            $newBlocks[] = $block;
        }

        $duplicate->setType($dashboard->getType())
            ->setTitle($dashboard->getTitle() . ' (copy)')
            ->setBlocks($newBlocks)
            ->setIdentifier(substr(hash('sha256', $dashboard->getIdentifier()), 0, 16))
            ->setIsMobileEnabled(false)
            ->setMobileToken(substr(hash('sha256', $dashboard->getMobileToken()), 0, 16))
            ->setIsDefault(false);

        $this->boardRepository->save($duplicate);

        $this->messageManager->addSuccessMessage(
            (string)__('Dashboard "%1" duplicated successfully with name "%2"', $dashboard->getTitle(), $duplicate->getTitle())
        );

        $this->sessionManager->setData('active_board', $duplicate->getIdentifier());

        return $resultRedirect->setPath('dashboard/dashboard/index');
    }
}
