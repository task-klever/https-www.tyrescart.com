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
namespace Ecomteck\StoreLocator\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\Filter\Date;
use Magento\Framework\View\Result\PageFactory;
use Ecomteck\StoreLocator\Api\StoresRepositoryInterface;
use Ecomteck\StoreLocator\Model\StoresFactory;
use Ecomteck\StoreLocator\Api\Data\StoresInterfaceFactory;

abstract class Stores extends Action
{
    /**
     * @var string
     */
    const ACTION_RESOURCE = 'Ecomteck_StoreLocator::stores';
    /**
     * stores factory
     *
     * @var StoresRepositoryInterface
     */
    public $storesRepository;

    /**
     * stores factory
     *
     * @var StoresFactory
     */
    protected $storesFactory;

    /**
     * Core registry
     *
     * @var Registry
     */
    public $coreRegistry;

    /**
     * date filter
     *
     * @var \Magento\Framework\Stdlib\DateTime\Filter\Date
     */
    public $dateFilter;

    /**
     * @var PageFactory
     */
    public $resultPageFactory;

    /**
     * @param Registry $registry
     * @param StoresRepositoryInterface $storesRepository
     * @param StoresInterfaceFactory $storesFactory
     * @param PageFactory $resultPageFactory
     * @param Date $dateFilter
     * @param Context $context
     */
    public function __construct(
        Registry $registry,
        StoresRepositoryInterface $storesRepository,
        StoresInterfaceFactory $storesFactory,
        PageFactory $resultPageFactory,
        Date $dateFilter,
        Context $context

    ) {
        $this->coreRegistry      = $registry;
        $this->storesRepository  = $storesRepository;
        $this->resultPageFactory = $resultPageFactory;
        $this->dateFilter        = $dateFilter;
        $this->storesFactory = $storesFactory;
        parent::__construct($context);
    }

    /**
     * filter dates
     *
     * @param array $data
     * @return array
     */
    public function filterData($data)
    {
        $inputFilter = new \Zend_Filter_Input(
            [],
            $data
        );
        $data = $inputFilter->getUnescaped();
        
        return $data;
    }

}
