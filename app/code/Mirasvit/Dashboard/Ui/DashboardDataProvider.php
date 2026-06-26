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



namespace Mirasvit\Dashboard\Ui;

use Magento\Backend\Block\Template;
use Magento\Framework\Session\SessionManagerInterface;
use Mirasvit\Core\Service\SerializeService;
use Mirasvit\Dashboard\Api\Data\BlockInterface;
use Mirasvit\Dashboard\Api\Data\BoardInterface;
use Mirasvit\Dashboard\Service\BoardService;
use Mirasvit\Report\Api\Service\CastingServiceInterface;

class DashboardDataProvider extends Template
{
    /**
     * @var BoardService
     */
    private $boardService;

    /**
     * @var CastingServiceInterface
     */
    private $castingService;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    private $urlBuilder;
    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    private $serializer;

    public function __construct(
        BoardService $boardService,
        CastingServiceInterface $castingService,
        \Magento\Framework\Serialize\Serializer\Json $serializer,
        Template\Context $context,
        SessionManagerInterface $sessionManager
    ) {
        $this->boardService   = $boardService;
        $this->castingService = $castingService;
        $this->urlBuilder     = $context->getUrlBuilder();
        $this->serializer = $serializer;
        $this->sessionManager = $sessionManager;
        parent::__construct($context);
    }

    /**
     * @return array
     */
    public function getConfigData()
    {
        $boards = [];

        $token = $this->getRequest()->getParam('token');

        foreach ($this->boardService->getAllowedBoards() as $board) {
            if ($token && $token !== $board->getMobileToken()) {
                // for mobile, select only one board
                continue;
            }

            $item = [
                BoardInterface::ID                => $board->getId(),
                BoardInterface::IDENTIFIER        => $board->getIdentifier(),
                BoardInterface::TITLE             => $board->getTitle(),
                BoardInterface::TYPE              => $board->getType(),
                BoardInterface::IS_DEFAULT        => (bool)$board->isDefault(),
                BoardInterface::IS_MOBILE_ENABLED => (bool)$board->isMobileEnable(),
                BoardInterface::MOBILE_TOKEN      => $board->getMobileToken(),
                'time_range'                      => $board->getDateRange()
            ];

            $blocks = [];
            foreach ($board->getBlocks() as $block) {
                $blocks[] = [
                    BlockInterface::IDENTIFIER  => $block->getIdentifier(),
                    BlockInterface::TITLE       => $block->getTitle(),
                    BlockInterface::POS         => $block->getPos(),
                    BlockInterface::SIZE        => $block->getSize(),
                    BlockInterface::DESCRIPTION => $block->getDescription(),
                    BlockInterface::CONFIG      => $block->getConfig()->getData(),
                ];
            }

            $item[BoardInterface::BLOCKS] = $blocks;

            $boards[] = $item;
        }

        $endpoint = $this->urlBuilder->getUrl('dashboard', ['key' => false]);
        $endpoint = str_replace('/index/index', '', $endpoint);

        $library = SerializeService::decode(file_get_contents(dirname(dirname(__FILE__)) . '/Setup/library.json'));

        $duplicatedDashboard = (string)$this->sessionManager->getData('active_board');

        $this->sessionManager->setData('active_board', null); // clear boards state

        return [
            'boards'      => $boards,
            'library'     => $library,
            'endpoint'    => $endpoint,
            'activeBoard' => $duplicatedDashboard
        ];
    }

    public function jsonEncode($data)
    {
        return $this->serializer->serialize($data);
    }

    /**
     * @return string
     */
    public function toHtml()
    {
        $data = $this->castingService->toCamelCase($this->getConfigData());
        $json = $this->serializer->serialize($data);

        return "<script>var dashboardDataProvider = $json</script>";
    }
}
