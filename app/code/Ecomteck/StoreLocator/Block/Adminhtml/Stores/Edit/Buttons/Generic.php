<?php
/**
 * Ecomteck_StoreLocator extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MIT License
 * that is bundled with this package in the file LICENSE
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/mit-license.php
 *
 * @category  Ecomteck
 * @package   Ecomteck_StoreLocator
 * @copyright 2016 Ecomteck
 * @license   http://opensource.org/licenses/mit-license.php MIT License
 * @author    Ecomteck
 */
namespace Ecomteck\StoreLocator\Block\Adminhtml\Stores\Edit\Buttons;

use Magento\Backend\Block\Widget\Context;
use Ecomteck\StoreLocator\Api\StoresRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;

class Generic
{
    /**
     * @var Context
     */
    public $context;

    /**
     * @var StoresRepositoryInterface
     */
    public $storesRepository;

    /**
     * @param Context $context
     * @param StoresRepositoryInterface $storesRepository
     */
    public function __construct(
        Context $context,
        StoresRepositoryInterface $storesRepository
    ) {
        $this->context = $context;
        $this->storesRepository = $storesRepository;
    }

    /**
     * Return Stores page ID
     *
     * @return int|null
     */
    public function getStoresId()
    {
        try {
            return $this->storesRepository->getById(
                $this->context->getRequest()->getParam('stores_id')
            )->getId();
        } catch (NoSuchEntityException $e) {
            return null;
        }
    }

    /**
     * Generate url by route and parameters
     *
     * @param   string $route
     * @param   array $params
     * @return  string
     */
    public function getUrl($route = '', $params = [])
    {
        return $this->context->getUrlBuilder()->getUrl($route, $params);
    }
}
