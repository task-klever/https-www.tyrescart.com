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
namespace Ecomteck\StoreLocator\Block\Adminhtml\Stores\Edit\Form\OpeningHours\Element;

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
    protected $_template = 'stores/openinghours/element.phtml';

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    private $jsonHelper;

    /**
     * @var \Magento\Framework\Locale\Resolver
     */
    private $localeResolver;

    /**
     * Block constructor.
     *
     * @param \Magento\Backend\Block\Template\Context      $context        Templating context.
     * @param \Magento\Framework\Data\Form\Element\Factory $elementFactory Form element factory.
     * @param \Magento\Framework\Json\Helper\Data          $jsonHelper     Helper for JSON
     * @param \Magento\Framework\Locale\Resolver           $localeResolver Locale Resolver
     * @param array                                        $data           Additional data.
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Data\Form\Element\Factory $elementFactory,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Framework\Locale\Resolver $localeResolver,
        array $data = []
    ) {
        $this->elementFactory = $elementFactory;
        $this->jsonHelper     = $jsonHelper;
        $this->localeResolver = $localeResolver;

        parent::__construct($context, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function render(AbstractElement $element)
    {
        $this->element = $element;
        $this->input   = $this->elementFactory->create('hidden');
        $this->input->setForm($this->getElement()->getForm());

        $inputId = $this->getData("input_id") !== null ? $this->getData("input_id") : "opening_hours" . uniqid();

        $this->input->setId($inputId);
        $this->input->setName($element->getName());

        $this->element->addClass("opening-hours-wrapper")->removeClass("admin__control-text");

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
        return $this->input->getHtmlId();
    }

    /**
     * Render HTML of the element using the opening hours engine.
     *
     * @return string
     */
    public function getInputHtml()
    {
        if ($this->element->getValue()) {
            $this->input->setValue($this->getJsonValues());
        }

        return $this->input->toHtml();
    }

    /**
     * Retrieve element values in Json.
     *
     * @return string
     */
    public function getJsonValues()
    {
        $values = $this->getValues();

        return $this->jsonHelper->jsonEncode($values);
    }

    /**
     * Retrieve element values
     *
     * @return array
     */
    private function getValues()
    {
        $values = [];
        if ($this->element->getValue()) {
            $timeSlots = $this->jsonHelper->jsonDecode($this->element->getValue());
            foreach ($timeSlots as $timeSlot) {
                list($startHour,$startMinute) = explode(':',$timeSlot[0]); 
                list($endHour,$endMinute) = explode(':',$timeSlot[1]); 
                
                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                $timezone = $objectManager->get('Magento\Framework\Stdlib\DateTime\TimezoneInterface');

                if($startHour && $endHour && $startMinute && $endMinute){ 
                    $currentDateTime = date('Y-m-d H:i:s');

                    // Create a DateTime object from the formatted date
                    $dateTimeStart = $timezone->date(new \DateTime($currentDateTime));
            
                    // Set the desired hour and minute
                    $dateTimeStart->setTime($startHour, $startMinute); // Sets the hour and minute
            
                    // Get the updated date and time
                    $startTime = $dateTimeStart->format('Y-m-d H:i:s');

                    // Create a DateTime object from the formatted date
                    $dateTimeEnd = $timezone->date(new \DateTime($currentDateTime));
            
                    // Set the desired hour and minute
                    $dateTimeEnd->setTime($endHour, $endMinute); // Sets the hour and minute
            
                    // Get the updated date and time
                    $endTime = $dateTimeEnd->format('Y-m-d H:i:s');
                    
                    $values[]  = [$startTime, $endTime];
                }
            }
        }

        return $values;
    }
}
