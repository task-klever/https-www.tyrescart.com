<?php
/**
 * DISCLAIMER
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future.
 *
 * @category  Ecomteck
 * @package   Ecomteck_StoreLocator
 * @author    Ecomteck <ecomteck@gmail.com>
 * @copyright 2017 Ecomteck
 * @license   Open Software License ("OSL") v. 3.0
 */
namespace Ecomteck\StoreLocator\Block\Adminhtml\Stores\Edit\Form;

use Magento\Framework\Stdlib\DateTime;
use Ecomteck\StoreLocator\Api\Data\StoresInterface;
use Ecomteck\StoreLocator\Controller\RegistryConstants;
/**
 * Opening Hours rendering block
 *
 * @category Ecomteck
 * @package  Ecomteck_StoreLocator
 * @author   Ecomteck <ecomteck@gmail.com>
 */
class OpeningHours extends \Magento\Backend\Block\AbstractBlock
{
    /**
     * @var \Magento\Framework\Data\FormFactory
     */
    private $formFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    /**
     * Constructor.
     *
     * @param \Magento\Backend\Block\Context      $context     Block context.
     * @param \Magento\Framework\Data\FormFactory $formFactory Form factory.
     * @param \Magento\Framework\Registry         $registry    Registry.
     * @param array                               $data        Additional data.
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->formFactory = $formFactory;
        $this->registry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     * {@inheritDoc}
     */
    protected function _toHtml()
    {
        return $this->escapeJsQuote($this->getForm()->toHtml());
    }

    /**
     * Get store
     *
     * @return StoresInterface
     */
    private function getStore()
    {
        return $this->registry->registry(RegistryConstants::CURRENT_STORES);
    }

    /**
     * Create the form containing the virtual rule field.
     *
     * @return \Magento\Framework\Data\Form
     */
    private function getForm()
    {
        $form = $this->formFactory->create();
        $form->setHtmlId('opening_hours');

        $openingHoursFieldset = $form->addFieldset(
            'opening_hours',
            ['name' => 'opening_hours', 'label' => __('Opening Hours'), 'container_id' => 'opening_hours']
        );

        if ($this->getStore() && $this->getStore()->getOpeningHours()) {
            $openingHoursFieldset->setOpeningHours($this->getStore()->getOpeningHours());
        }

        $openingHoursRenderer = $this->getLayout()->createBlock('Ecomteck\StoreLocator\Block\Adminhtml\Stores\Edit\Form\OpeningHours\Container\Renderer');
        $openingHoursFieldset->setRenderer($openingHoursRenderer);

        return $form;
    }
}
