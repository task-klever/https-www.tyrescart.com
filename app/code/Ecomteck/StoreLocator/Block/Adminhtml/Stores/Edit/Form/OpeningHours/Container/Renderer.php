<?php
/**
 * DISCLAIMER
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future.
 *
 * @category  Ecomteck
 * @package   Ecomteck_StoreLocator
 * @author    Ecomteck <ecomteck@gmail.com>
 * @copyright 2016 Ecomteck
 * @license   Open Software License ("OSL") v. 3.0
 */
namespace Ecomteck\StoreLocator\Block\Adminhtml\Stores\Edit\Form\OpeningHours\Container;

use Magento\Backend\Block\Template;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Data\Form\Element\Renderer\RendererInterface;
use Magento\Framework\Stdlib\DateTime;

/**
 * Opening Hours field renderer
 *
 * @SuppressWarnings(PHPMD.CamelCasePropertyName)
 *
 * @category Ecomteck
 * @package  Ecomteck\Retailer
 * @author   Ecomteck <ecomteck@gmail.com>
 */
class Renderer extends Template implements RendererInterface
{
    /**
     * @var \Magento\Framework\Data\Form\Element\Factory
     */
    protected $elementFactory;

    /**
     * @var AbstractElement
     */
    protected $element;

    /**
     * @var \Magento\Framework\Data\Form\Element\Text
     */
    protected $input;

    /**
     * @var string
     */
    protected $_template = 'stores/openinghours/container.phtml';

    /**
     * @var \Magento\Framework\Locale\ListsInterface|null
     */
    private $localeList = null;

    /**
     * Block constructor.
     *
     * @param \Magento\Backend\Block\Template\Context      $context        Templating context.
     * @param \Magento\Framework\Data\Form\Element\Factory $elementFactory Form element factory.
     * @param \Magento\Framework\Locale\ListsInterface     $localeLists    Locale List.
     * @param array                                        $data           Additional data.
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Data\Form\Element\Factory $elementFactory,
        \Magento\Framework\Locale\ListsInterface $localeLists,
        array $data = []
    ) {
        $this->elementFactory = $elementFactory;
        $this->localeList     = $localeLists;

        parent::__construct($context, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function render(AbstractElement $element)
    {
        $this->element = $element;
        $this->element->addClass("opening-hours-container-fieldset");

        return $this->toHtml();
    }

    /**
     * Get currently edited element.
     *
     * @return AbstractElement
     */
    public function getElement()
    {
        return $this->element;
    }

    /**
     * Retrieve element unique container id.
     *
     * @return string
     */
    public function getHtmlId()
    {
        return $this->getElement()->getContainer()->getHtmlId();
    }

    /**
     * Render HTML of the element using the opening hours engine.
     *
     * @return string
     */
    public function getInputHtml()
    {
        if ($this->element->getOpeningHours()) {
            $values = $this->element->getOpeningHours();
        }

        $html = "";
        $days = $this->localeList->getOptionWeekdays(true, true);

        foreach ($days as $key => $day) {
            $input = $this->elementFactory->create('text');
            $input->setForm($this->getElement()->getForm());

            $elementRenderer = $this->getLayout()
                ->createBlock('Ecomteck\StoreLocator\Block\Adminhtml\Stores\Edit\Form\OpeningHours\Element\Renderer');

            $elementRenderer->setDateFormat(DateTime::DATETIME_INTERNAL_FORMAT);

            $input->setLabel(ucfirst($day['label']));
            $input->setName($this->element->getName() . "[$key]");
            $input->setRenderer($elementRenderer);

            if (isset($values[$key])) {
                $input->setValue($values[$key]);
            }

            $html .= $input->toHtml();
        }

        return $html;
    }
}
