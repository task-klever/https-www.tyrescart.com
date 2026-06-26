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
namespace Ecomteck\StoreLocator\Block\Adminhtml\Stores\Edit\Form\SpecialOpeningHours\Container;

use Magento\Backend\Block\Template;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Framework\Stdlib\DateTime;
use Zend_Date;

/**
 * Special Opening Hours fieldset renderer
 *
 * @SuppressWarnings(PHPMD.CamelCasePropertyName)
 *
 * @category Ecomteck
 * @package  Ecomteck\Retailer
 * @author   Ecomteck <ecomteck@gmail.com>
 */
class Renderer extends \Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray
{
    /**
     * @var \Magento\Framework\Data\Form\Element\Factory
     */
    private $elementFactory;

    /**
     * @var \Magento\Framework\Data\Form\Element\AbstractElement
     */
    private $element;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    private $jsonHelper;

    /**
     * @var \Magento\Framework\Locale\Resolver
     */
    private $localeResolver;

    /**
     * @param \Magento\Backend\Block\Template\Context      $context        Application context
     * @param \Magento\Framework\Data\Form\Element\Factory $elementFactory Element Factory
     * @param \Magento\Framework\Json\Helper\Data          $jsonHelper     JSON helper
     * @param \Magento\Framework\Locale\Resolver           $localeResolver Locale Resolver
     * @param array                                        $data           Element Data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Data\Form\Element\Factory $elementFactory,
        JsonHelper $jsonHelper,
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

        if ($element->getSpecialOpeningHours()) {
            $element->setValue($this->parseValuesToArray($element->getSpecialOpeningHours()));
        }

        $this->element->addClass("special-opening-hours-container-fieldset");

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
     * Render array cell for JS template
     *
     * @param string $columnName The column name
     *
     * @return string
     */
    public function renderCellTemplate($columnName)
    {
        if ($columnName == 'date' && isset($this->_columns[$columnName])) {
            return $this->renderDateColumn($columnName);
        }

        if ($columnName == 'opening_hours' && isset($this->_columns[$columnName])) {
            return $this->renderOpeningHoursColumn($columnName);
        }

        return parent::renderCellTemplate($columnName);
    }

    /**
     * Initialise form fields
     *
     * @SuppressWarnings(PHPMD.CamelCaseMethodName) Method is inherited
     */
    protected function _construct()
    {
        $this->addColumn('date', ['label' => 'Date']);
        $this->addColumn('opening_hours', ['label' => __('Special Opening Hours')]);
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add Special Opening Hours');

        parent::_construct();
    }

    /**
     * Wrap this container into a parent div when rendering.
     * Mainly used to have propoer binding via the Ui Component.
     *
     * @SuppressWarnings(PHPMD.CamelCaseMethodName) Method is inherited
     *
     * @param string $html The rendered HTML
     *
     * @return string
     */
    protected function _afterToHtml($html)
    {
        $htmlId = $this->getHtmlId();

        return "<div id=\"{$htmlId}\">{$html}</div>";
    }

    /**
     * Render The "Date" Column
     *
     * @param string $columnName The column name
     *
     * @return string
     */
    private function renderDateColumn($columnName)
    {
        $element = $this->elementFactory->create('date');
        $element->setFormat($this->_localeDate->getDateFormatWithLongYear())
            ->setForm($this->getForm())
            ->setDisabled(false)
            ->setValue('')
            ->setName($this->_getCellInputElementName($columnName))
            ->setHtmlId($this->_getCellInputElementId('<%- _id %>', $columnName))
            ->addClass("ecomteck-special-opening-hours-datepicker");

        $this->appendDatePickerConfiguration($element);

        return $this->escapeScriptTags($element->getElementHtml());
    }

    /**
     * Render "Special Opening Hours" Column
     *
     * @param string $columnName The column name
     *
     * @return string
     */
    private function renderOpeningHoursColumn($columnName)
    {
        $input = $this->elementFactory->create('text');
        $input->setForm($this->getElement()->getForm());

        $elementRenderer = $this->getLayout()
            ->createBlock(
                'Ecomteck\StoreLocator\Block\Adminhtml\Stores\Edit\Form\OpeningHours\Element\Renderer'
            )->setData("input_id", $this->_getCellInputElementId('<%- _id %>', $columnName));

        $input->setName($this->_getCellInputElementName($columnName));
        $input->setRenderer($elementRenderer);

        return $this->escapeScriptTags($input->toHtml());
    }

    /**
     * Apply date picker on an element.
     * Mandatory since Magento does this with x-magento-init tag which is NOT triggered when adding a field into array dynamically
     *
     * @param AbstractElement $element The element to apply calendar on
     */
    private function appendDatePickerConfiguration($element)
    {
        $inputId = $element->getHtmlId();
        $calendarConfig = $this->jsonHelper->jsonEncode([
            'dateFormat'  => $element->getFormat(),
            'showsTime'   => !empty($element->getTimeFormat()),
            'timeFormat'  => $element->getTimeFormat(),
            'buttonImage' => $element->getImage(),
            'buttonText'  => 'Select Date',
            'disabled'    => $element->getDisabled(),
        ]);

        // Class toggle on change() is mandatory to have the Mutation Observer working properly.
        // Since jquery Ui Datepicker value appliance is made with val(), this does not trigger changes on DOM.
        $datePickerJsInit = <<<JAVASCRIPT
            <script type="text/javascript">
                require(["jquery", "calendar"],
                    function($, calendar) {
                        $("#$inputId").calendar($calendarConfig);
                        $("#$inputId").change(function() { $("#$inputId").toggleClass("updated-datepicker");});
                    }
                );
            </script>
JAVASCRIPT;

        $element->setAfterElementHtml($datePickerJsInit);
    }

    /**
     * Escape a string's contents. Needed since array content rendering fails without this.
     *
     * @param string $string The string to escape
     *
     * @return string
     */
    private function escapeScriptTags($string)
    {
        $string = str_replace("\n", "", $string);
        $string = str_replace("</script>", "<\/script>", $string);

        return $string;
    }

    /**
     * Parse Values to proper array-renderer compatible format
     *
     * @param array $values The values coming from model object
     *
     * @return array
     */
    private function parseValuesToArray($values)
    {
        $arrayValues = [];

        if (!empty($values)) {
            ksort($values);
            $valuesFormat =  [];
            foreach ($values as $value) {
                if(empty($value['date'])){
                    continue;
                }
                if(!isset($valuesFormat[$value['date']])){
                    $valuesFormat[$value['date']] = [];
                }
                try {
                    $valuesFormat[$value['date']] = $this->jsonHelper->jsonDecode($value['opening_hours']);
                } catch(\Exception $e) {
                    $valuesFormat[$value['date']] = null;
                }
                
            }
            foreach ($valuesFormat as $date => $timeSlots) {
                $timeRanges = [];
                if(is_array($timeSlots)){
                    foreach ($timeSlots as $timeSlot) {
                        list($startHour,$startMinute) = explode(':',$timeSlot[0]); 
                        list($endHour,$endMinute) = explode(':',$timeSlot[1]); 
                        
                        if($startHour && $endHour && $startMinute && $endMinute){
                            $timeDate   = new Zend_Date();
                            $timeDate->setLocale($this->localeResolver->getLocale());
                            $startTime  = $timeDate->setHour($startHour)->setMinute($startMinute)->toString(DateTime::DATETIME_INTERNAL_FORMAT);
                            $endTime    = $timeDate->setHour($endHour)->setMinute($endMinute)->toString(DateTime::DATETIME_INTERNAL_FORMAT);
                            $timeRanges[] = [$startTime, $endTime];
                        }
                    }
                    //$date = new Zend_Date($date, DateTime::DATETIME_INTERNAL_FORMAT);
                    $arrayValues[] = [
                        "date" => $date,//->toString($this->_localeDate->getDateFormatWithLongYear()),
                        "opening_hours" => $this->jsonHelper->jsonEncode(array_filter($timeRanges)),
                    ];
                }
            }
        }

        return $arrayValues;
    }
}
