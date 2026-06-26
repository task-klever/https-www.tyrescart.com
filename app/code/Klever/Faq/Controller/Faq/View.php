<?php

namespace Klever\Faq\Controller\Faq;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Theme\Block\Html\Title as HtmlTitle;
use Mageprince\Faq\Api\FaqRepositoryInterface;
use Mageprince\Faq\Api\FaqGroupRepositoryInterface;
use Mageprince\Faq\Helper\Data;
use Magento\Framework\Controller\Result\ForwardFactory;

class View extends Action
{
    private PageFactory $resultPageFactory;
    private Data $helper;
    private FaqRepositoryInterface $faqRepository;
    private FaqGroupRepositoryInterface $faqGroupRepository;
    private ForwardFactory $forwardFactory;

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Data $helper,
        FaqRepositoryInterface $faqRepository,
        FaqGroupRepositoryInterface $faqGroupRepository,
        ForwardFactory $forwardFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->helper = $helper;
        $this->faqRepository = $faqRepository;
        $this->faqGroupRepository = $faqGroupRepository;
        $this->forwardFactory = $forwardFactory;
        parent::__construct($context);
    }

    public function execute(): ResultInterface
    {
        if (!$this->helper->isEnable()) {
            return $this->forwardFactory->create()->forward('noroute');
        }

        $faqId = (int)$this->getRequest()->getParam('faq_id');
        $groupId = (int)$this->getRequest()->getParam('group_id');

        if (!$faqId || !$groupId) {
            return $this->forwardFactory->create()->forward('noroute');
        }

        try {
            $faq = $this->faqRepository->getById($faqId);
            $group = $this->faqGroupRepository->getById($groupId);
        } catch (\Exception $e) {
            return $this->forwardFactory->create()->forward('noroute');
        }

        $faqTitle = $faq->getTitle();
        $groupName = $group->getGroupName();
        $metaTitle = $faq->getData('meta_title');
        $metaDescription = $faq->getData('meta_description');

        $resultPage = $this->resultPageFactory->create();

        $pageMainTitle = $resultPage->getLayout()->getBlock('page.main.title');
        if ($pageMainTitle && $pageMainTitle instanceof HtmlTitle) {
            $pageMainTitle->setPageTitle($faqTitle);
        }

        $resultPage->getConfig()->getTitle()->set(__($metaTitle ?: $faqTitle . ' - ' . $groupName . ' FAQ'));
        $resultPage->getConfig()->setDescription(
            __($metaDescription ?: __('%1 - %2 FAQ', $faqTitle, $groupName))
        );

        return $resultPage;
    }
}
