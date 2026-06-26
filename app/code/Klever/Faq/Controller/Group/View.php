<?php

namespace Klever\Faq\Controller\Group;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Theme\Block\Html\Title as HtmlTitle;
use Mageprince\Faq\Api\FaqGroupRepositoryInterface;
use Mageprince\Faq\Helper\Data;
use Magento\Framework\Controller\Result\ForwardFactory;

class View extends Action
{
    private PageFactory $resultPageFactory;
    private Data $helper;
    private FaqGroupRepositoryInterface $faqGroupRepository;
    private ForwardFactory $forwardFactory;

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Data $helper,
        FaqGroupRepositoryInterface $faqGroupRepository,
        ForwardFactory $forwardFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->helper = $helper;
        $this->faqGroupRepository = $faqGroupRepository;
        $this->forwardFactory = $forwardFactory;
        parent::__construct($context);
    }

    public function execute(): ResultInterface
    {
        if (!$this->helper->isEnable()) {
            return $this->forwardFactory->create()->forward('noroute');
        }

        $groupId = (int)$this->getRequest()->getParam('group_id');
        if (!$groupId) {
            return $this->forwardFactory->create()->forward('noroute');
        }

        try {
            $group = $this->faqGroupRepository->getById($groupId);
        } catch (\Exception $e) {
            return $this->forwardFactory->create()->forward('noroute');
        }

        $resultPage = $this->resultPageFactory->create();

        $groupName = $group->getGroupName();
        $metaTitle = $group->getData('meta_title');
        $metaDescription = $group->getData('meta_description');

        $pageMainTitle = $resultPage->getLayout()->getBlock('page.main.title');
        if ($pageMainTitle && $pageMainTitle instanceof HtmlTitle) {
            $pageMainTitle->setPageTitle($groupName);
        }

        $resultPage->getConfig()->getTitle()->set(__($metaTitle ?: $groupName . ' - FAQ'));
        $resultPage->getConfig()->setDescription(
            __($metaDescription ?: __('%1 - Frequently Asked Questions', $groupName))
        );

        return $resultPage;
    }
}
