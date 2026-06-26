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

namespace Ecomteck\StoreLocator\Model;

use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Data\Collection\Db;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filter\FilterManager;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Ecomteck\StoreLocator\Api\Data\StoresInterface;
use Ecomteck\StoreLocator\Model\Url;
use Ecomteck\StoreLocator\Model\ResourceModel\Stores as StoresResourceModel;
use Ecomteck\StoreLocator\Model\Routing\RoutableInterface;
use Ecomteck\StoreLocator\Model\Source\AbstractSource;
use Magento\Framework\Stdlib\DateTime;

/**
 * @method StoresResourceModel _getResource()
 * @method StoresResourceModel getResource()
 */
class Stores extends AbstractModel implements StoresInterface, RoutableInterface
{
    /**
     * @var int
     */
    const STATUS_ENABLED = 1;
    /**
     * @var int
     */
    const STATUS_DISABLED = 0;
    /**
     * @var Url
     */
    public $urlModel;
    /**
     * cache tag
     *
     * @var string
     */
    const CACHE_TAG = 'ecomteck_storelocator';

    /**
     * cache tag
     *
     * @var string
     */
    public $_cacheTag = 'ecomteck_storelocator_stores';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    public $_eventPrefix = 'ecomteck_storelocator_stores';

    /**
     * filter model
     *
     * @var \Magento\Framework\Filter\FilterManager
     */
    public $filter;

    /**
     * @var UploaderPool
     */
    public $uploaderPool;

    /**
     * @var \Ecomteck\StoreLocator\Model\Output
     */
    public $outputProcessor;

    /**
     * @var AbstractSource[]
     */
    public $optionProviders;

    /**
     * @var \Magento\Framework\Json\Helper\Data 
     */
    public $jsonHelper;

    /**
     * @var \Magento\Framework\Locale\ListsInterface|null
     */
    private $localeList = null;

    /**
     * @var \Magento\Framework\Locale\Resolver
     */
    private $localeResolver;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     */
    private $localeDate;

    protected $_countryFactory;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param Output $outputProcessor
     * @param UploaderPool $uploaderPool
     * @param FilterManager $filter
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Magento\Framework\Locale\ListsInterface $localeLists
     * @param \Magento\Framework\Locale\Resolver           $localeResolver Locale Resolver
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param Url $urlModel
     * @param array $optionProviders
     * @param array $data
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     */
    public function __construct(
        Context $context,
        Registry $registry,
        Output $outputProcessor,
        UploaderPool $uploaderPool,
        FilterManager $filter,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Framework\Locale\ListsInterface $localeLists,
        \Magento\Framework\Locale\Resolver $localeResolver,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        Url $urlModel,
        array $optionProviders = [],
        array $data = [],
        ?AbstractResource $resource = null,
        ?AbstractDb $resourceCollection = null
    ) {
        $this->outputProcessor = $outputProcessor;
        $this->uploaderPool    = $uploaderPool;
        $this->filter          = $filter;
        $this->urlModel        = $urlModel;
        $this->optionProviders = $optionProviders;
        $this->jsonHelper = $jsonHelper;
        $this->localeList     = $localeLists;
        $this->localeResolver = $localeResolver;
        $this->_countryFactory = $countryFactory;
        $this->localeDate = $localeDate;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init(StoresResourceModel::class);
    }

    /**
     * Get type
     *
     * @return int
     */
    public function getType()
    {
        return $this->getData(StoresInterface::TYPE);
    }

    /**
     * @param $storeId
     * @return StoresInterface
     */
    public function setStoreId($storeId)
    {
        $this->setData(StoresInterface::STORE_ID, $storeId);
        return $this;
    }

    /**
     * Get country
     *
     * @return string
     */
    public function getCountry()
    {
        return $this->getData(StoresInterface::COUNTRY);
    }

    /**
     * set name
     *
     * @param $name
     * @return StoresInterface
     */
    public function setName($name)
    {
        return $this->setData(StoresInterface::NAME, $name);
    }

    /**
     * set external link
     *
     * @param $external_link
     * @return StoresInterface
     */
    public function setExternalLink($external_link)
    {
        return $this->setData(StoresInterface::EXTERNAL_LINK, $external_link);
    }


    /**
     * set schedule
     *
     * @param $schedule
     * @return StoresInterface
     */
    public function setSchedule($schedule)
    {
        return $this->setData(StoresInterface::SCHEDULE, $schedule);
    }

    /**
     * set opening hours
     *
     * @param $openingHours
     * @return StoresInterface
     */
    public function setOpeningHours($openingHours)
    {
        return $this->setData(StoresInterface::OPENING_HOURS, $openingHours);
    }

    /**
     * set special opening hours
     *
     * @param $specialOpeningHours
     * @return StoresInterface
     */
    public function setSpecialOpeningHours($specialOpeningHours)
    {
        return $this->setData(StoresInterface::SPECIAL_OPENING_HOURS, $specialOpeningHours);
    }

    /**
     * set distance
     *
     * @param $distance
     * @return StoresInterface
     */
    public function setDistance($distance)
    {
        return $this->setData(StoresInterface::DISTANCE, $distance);
    }

    /**
     * set description
     *
     * @param $description
     * @return StoresInterface
     */
    public function setDescription($description)
    {
        return $this->setData(StoresInterface::DESCRIPTION, $description);
    }

    /**
     * set station
     *
     * @param $station
     * @return StoresInterface
     */
    public function setStation($station)
    {
        return $this->setData(StoresInterface::STATION, $station);
    }

    /**
     * set intro
     *
     * @param $intro
     * @return StoresInterface
     */
    public function setIntro($intro)
    {
        return $this->setData(StoresInterface::INTRO, $intro);
    }

    /**
     * set category
     *
     * @param $category
     * @return StoresInterface
     */
    public function setCategory($category)
    {
        return $this->setData(StoresInterface::CATEGORY, $category);
    }

    /**
     * set is_all_products
     *
     * @param $isAllProducts
     * @return StoresInterface
     */
    public function setIsAllProducts($isAllProducts)
    {
        return $this->setData(StoresInterface::IS_ALL_PRODUCTS, $isAllProducts);
    }

    /**
     * set type
     *
     * @param $type
     * @return StoresInterface
     */
    public function setType($type)
    {
        return $this->setData(StoresInterface::TYPE, $type);
    }

    /**
     * Set country
     *
     * @param $country
     * @return StoresInterface
     */
    public function setCountry($country)
    {
        return $this->setData(StoresInterface::COUNTRY, $country);
    }

    /**
     * set url key
     *
     * @param $urlKey
     * @return StoresInterface
     */
    public function setUrlKey($urlKey)
    {
        return $this->setData(StoresInterface::URL_KEY, $urlKey);
    }

    /**
     * set address
     *
     * @param $address
     * @return StoresInterface
     */
    public function setAddress($address)
    {
        return $this->setData(StoresInterface::ADDRESS, $address);
    }

    /**
     * set city
     *
     * @param $city
     * @return StoresInterface
     */
    public function setCity($city)
    {
        return $this->setData(StoresInterface::CITY, $city);
    }

    /**
     * set postcode
     *
     * @param $postcode
     * @return StoresInterface
     */
    public function setPostcode($postcode)
    {
        return $this->setData(StoresInterface::POSTCODE, $postcode);
    }

    /**
     * set region
     *
     * @param $region
     * @return StoresInterface
     */
    public function setRegion($region)
    {
        return $this->setData(StoresInterface::REGION, $region);
    }

    /**
     * set email
     *
     * @param $email
     * @return StoresInterface
     */
    public function setEmail($email)
    {
        return $this->setData(StoresInterface::EMAIL, $email);
    }

    /**
     * set phone
     *
     * @param $phone
     * @return StoresInterface
     */
    public function setPhone($phone)
    {
        return $this->setData(StoresInterface::PHONE, $phone);
    }

    /**
     * set latitude
     *
     * @param $latitude
     * @return StoresInterface
     */
    public function setLatitude($latitude)
    {
        return $this->setData(StoresInterface::LATITUDE, $latitude);
    }

    /**
     * set longitude
     *
     * @param $longitude
     * @return StoresInterface
     */
    public function setLongitude($longitude)
    {
        return $this->setData(StoresInterface::LONGITUDE, $longitude);
    }

    /**
     * Set status
     *
     * @param $status
     * @return StoresInterface
     */
    public function setStatus($status)
    {
        return $this->setData(StoresInterface::STATUS, $status);
    }

    /**
     * set image
     *
     * @param $image
     * @return StoresInterface
     */
    public function setImage($image)
    {
        return $this->setData(StoresInterface::IMAGE, $image);
    }

    /**
     * set created at
     *
     * @param $createdAt
     * @return StoresInterface
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(StoresInterface::CREATED_AT, $createdAt);
    }

    /**
     * set updated at
     *
     * @param $updatedAt
     * @return StoresInterface
     */
    public function setUpdatedAt($updatedAt)
    {
        return $this->setData(StoresInterface::UPDATED_AT, $updatedAt);
    }

    public function setIsmobilevan($ismobilevan)
    {
        return $this->setData(StoresInterface::ISMOBILEVAN, $ismobilevan);
    }

    public function setShippingAmount($shipping_amount)
    {
        return $this->setData(StoresInterface::SHIPPING_AMOUNT, $shipping_amount);
    }

    public function setInstallerSortOrder($installer_sort_order)
    {
        return $this->setData(StoresInterface::INSTALLER_SORT_ORDER, $installer_sort_order);
    }

    public function setGoogleMap($google_map)
    {
        return $this->setData(StoresInterface::GOOGLE_MAP, $google_map);
    }

    public function setServiceIncluded($service_included)
    {
        return $this->setData(StoresInterface::SERVICE_INCLUDED, $service_included);
    }

    public function setOpeningHoursOne($opening_hours_one)
    {
        return $this->setData(StoresInterface::OPENING_HOURS_ONE, $opening_hours_one);
    }

    public function setOpeningHoursTwo($opening_hours_two)
    {
        return $this->setData(StoresInterface::OPENING_HOURS_TWO, $opening_hours_two);
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->getData(StoresInterface::NAME);
    }

    /**
     * Get url key
     *
     * @return string
     */
    public function getUrlKey()
    {
        return $this->getData(StoresInterface::URL_KEY);
    }

    /**
     * Get address
     *
     * @return string
     */
    public function getAddress()
    {
        return $this->getData(StoresInterface::ADDRESS);
    }

    /**
     * Get schedule
     *
     * @return string
     */
    public function getSchedule()
    {
        return $this->getData(StoresInterface::SCHEDULE);
    }

    /**
     * Get opening hours
     *
     * @return Array
     */
    public function getOpeningHours()
    {
        if ($this->getData(StoresInterface::OPENING_HOURS)) {
            return $this->jsonHelper->jsonDecode($this->getData(StoresInterface::OPENING_HOURS), true);
        }
    }

    /**
     * Get opening hours
     *
     * @return Array
     */
    public function getOpeningHoursConfig()
    {
        $result = [];
        if ($openingHours = $this->getOpeningHours()) {
            $days = $this->localeList->getOptionWeekdays(true, true);
            foreach ($days as $key => $day) {
                $dayOfWeek = ucfirst($day['label']);
                if (!empty($openingHours[$key])) {
                    $timeSlots = $this->jsonHelper->jsonDecode($openingHours[$key]);
                    if (count($timeSlots) == 0) {
                        $result[$key] = $timeSlots;
                    } else {
                        $result[$key] = $timeSlots;
                    }
                } else {
                    $result[$key] = [];
                }
            }
        }

        return $result;
    }

    public function getOpeningHoursExport()
    {
        $openingHours = $this->getOpeningHoursConfig();
        $format = [];
        $j = 0;
        foreach ($openingHours as $dayOfWeek => $timeSlots) {
            if (count($timeSlots) == 0) {
                continue;
            }
            $j++;
            $format[] = $dayOfWeek;
            $format[] = '=';
            $i = 0;
            foreach ($timeSlots as $slot) {
                $i++;
                $format[] = implode('->', $slot);
                if ($i < count($timeSlots)) {
                    $format[] = ',';
                }
            }
            if ($j < count($openingHours)) {
                $format[] = '|';
            }
        }
        return implode('', $format);
    }

    /**
     * Get opening hours formated
     *
     * @return string
     */
    public function getOpeningHoursFormatedBKP()
    {
        if ($openingHours = $this->getOpeningHours()) {
            $html = [];
            $html[] = '<ul>';
            $days = $this->localeList->getOptionWeekdays(true, true);

            foreach ($days as $key => $day) {
                $dayOfWeek = ucfirst($day['label']);
                if (!empty($openingHours[$key])) {
                    $html[] = '<li>';
                    $html[] = '<b>' . $dayOfWeek . ':</b>';
                    $timeSlots = $this->jsonHelper->jsonDecode($openingHours[$key]);
                    if (count($timeSlots) == 0) {
                        $html[] = '<i>' . __('Close') . '</i>';
                    }
                    if (count($timeSlots) > 1) {
                        $html[] = '<ul>';
                    }

                    foreach ($timeSlots as $timeSlot) {
                        list($startHour, $startMinute) = explode(':', $timeSlot[0]);
                        list($endHour, $endMinute) = explode(':', $timeSlot[1]);

                        if ($startHour && $endHour && $startMinute && $endMinute) {
                            //$date      = new Zend_Date();
                            //$date->setLocale($this->localeResolver->getLocale());
                            //$startTime = $date->setHour($startHour)->setMinute($startMinute)->toString("h:m a");
                            //$endTime   = $date->setHour($endHour)->setMinute($endMinute)->toString("h:m a");
                            $startTime = $this->formatTime($startHour, $startMinute);
                            $endTime = $this->formatTime($endHour, $endMinute);
                            if (count($timeSlots) > 1) {
                                $html[] = '<li><i>' . $startTime . ' - ' . $endTime . '</i></li>';
                            } else {
                                $html[] = '<i>' . $startTime . ' - ' . $endTime . '</i>';
                            }
                        }
                    }
                    if (count($timeSlots) > 1) {
                        $html[] = '</ul>';
                    }
                    $html[] = '</li>';
                }
            }
            $html[] = '</ul>';
            return implode("\n", $html);
        }
        return '';
    }

    public function getOpeningHoursFormated()
    {
        if ($openingHours = $this->getOpeningHours()) {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $timezone = $objectManager->get('Magento\Framework\Stdlib\DateTime\TimezoneInterface');
            $html = [];
            $html[] = '<ul>';
            //$days = $this->localeList->getOptionWeekdays(true, true);
            $days = $this->getWeekdaysInEnglish();
            $timeSlotArray = array();
            foreach ($days as $key => $day) {
                $dayOfWeek = ucfirst($day['label']);
                if (!empty($openingHours[$key])) {
                    $html[] = '<li>';
                    $html[] = '<b>' . $dayOfWeek . ':</b>';
                    $timeSlots = $this->jsonHelper->jsonDecode($openingHours[$key]);
                    if (count($timeSlots) == 0) {
                        $html[] = '<i>' . __('Close') . '</i>';
                    }
                    if (count($timeSlots) > 1) {
                        $html[] = '<ul>';
                    }

                    foreach ($timeSlots as $timeSlot) {
                        list($startHour, $startMinute) = explode(':', $timeSlot[0]);
                        list($endHour, $endMinute) = explode(':', $timeSlot[1]);

                        if ($startHour && $endHour && $startMinute && $endMinute) {
                            $currentDate = date('Y-m-d'); // Current date in 'Y-m-d' format
                            $currentTime = date('H:i');   // Current time in 'H:i' format
                            $dateTimeStart = new \DateTime($currentDate . ' ' . $currentTime, new \DateTimeZone($timezone->getConfigTimezone()));
                            $dateTimeStart->setTimezone(new \DateTimeZone($timezone->getConfigTimezone()));
                            $dateTimeStart->setTime($startHour, $startMinute);
                            $startTime = $dateTimeStart->format('h:i A');

                            $dateTimeEnd = new \DateTime($currentDate . ' ' . $currentTime, new \DateTimeZone($timezone->getConfigTimezone()));
                            $dateTimeEnd->setTimezone(new \DateTimeZone($timezone->getConfigTimezone()));
                            $dateTimeEnd->setTime($endHour, $endMinute);
                            $endTime = $dateTimeEnd->format('h:i A');

                            if (count($timeSlots) > 1) {
                                $html[] = '<li><i>' . $startTime . ' - ' . $endTime . '</i></li>';
                                $timeSlotArray[$dayOfWeek][] = $startTime . ' - ' . $endTime;
                            } else {
                                $html[] = '<i>' . $startTime . ' - ' . $endTime . '</i>';
                                $timeSlotArray[$dayOfWeek][] = $startTime . ' - ' . $endTime;
                            }
                        }
                    }
                    if (count($timeSlots) > 1) {
                        $html[] = '</ul>';
                    }
                    $html[] = '</li>';
                }
            }
            $html[] = '</ul>';
            // return implode("\n",$html);
            return $timeSlotArray;
        }
        return '';
    }

    public function getWeekdaysInEnglish()
    {
        $englishWeekdays = [
            ['label' => 'Sunday', 'value' => 'Sun'],
            ['label' => 'Monday', 'value' => 'Mon'],
            ['label' => 'Tuesday', 'value' => 'Tue'],
            ['label' => 'Wednesday', 'value' => 'Wed'],
            ['label' => 'Thursday', 'value' => 'Thu'],
            ['label' => 'Friday', 'value' => 'Fri'],
            ['label' => 'Saturday', 'value' => 'Sat'],
        ];

        return $englishWeekdays;
    }

    /**
     * Get special opening hours
     *
     * @return Array
     */
    public function getSpecialOpeningHours()
    {
        if ($this->getData(StoresInterface::SPECIAL_OPENING_HOURS)) {
            try {
                $result = $this->jsonHelper->jsonDecode($this->getData(StoresInterface::SPECIAL_OPENING_HOURS));
                return $result;
            } catch (\Exception $e) {
                return false;
            }
        }
    }

    /**
     * Get opening hours
     *
     * @return Array
     */
    public function getSpecialOpeningHoursConfig()
    {
        $result = [];
        if ($specialOpeningHours = $this->getSpecialOpeningHours()) {
            $valuesFormat =  [];
            foreach ($specialOpeningHours as $value) {
                if (empty($value['date'])) {
                    continue;
                }
                if (!isset($valuesFormat[$value['date']])) {
                    $valuesFormat[$value['date']] = [];
                }
                $valuesFormat[$value['date']] = $this->jsonHelper->jsonDecode($value['opening_hours']);
            }
            foreach ($valuesFormat as $date => $timeSlots) {
                $timeRanges = [];
                if (is_array($timeSlots)) {
                    if (count($timeSlots) > 0) {
                        $result[$date] = $timeSlots;
                    } else {
                        $result[$date] = $timeSlots;
                    }
                } else {
                    $result[$date] = [];
                }
            }
        }

        return $result;
    }

    public function getSpecialOpeningHoursExport()
    {
        $openingHours = $this->getSpecialOpeningHoursConfig();
        $format = [];
        $j = 0;
        foreach ($openingHours as $day => $timeSlots) {
            if (count($timeSlots) == 0) {
                continue;
            }
            $j++;
            $format[] = $day;
            $format[] = '=';
            $i = 0;
            foreach ($timeSlots as $slot) {
                $i++;
                $format[] = implode('->', $slot);
                if ($i < count($timeSlots)) {
                    $format[] = ',';
                }
            }
            if ($j < count($openingHours)) {
                $format[] = '|';
            }
        }
        return implode('', $format);
    }

    /**
     * Get special opening hours formated
     *
     * @return string
     */
    public function getSpecialOpeningHoursFormated()
    {
        if ($specialOpeningHours = $this->getSpecialOpeningHours()) {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $timezone = $objectManager->get('Magento\Framework\Stdlib\DateTime\TimezoneInterface');
            $html = [];
            $html[] = '<ul>';
            $valuesFormat =  [];
            foreach ($specialOpeningHours as $value) {
                if (empty($value['date'])) {
                    continue;
                }
                if (!isset($valuesFormat[$value['date']])) {
                    $valuesFormat[$value['date']] = [];
                }
                $valuesFormat[$value['date']] = $this->jsonHelper->jsonDecode($value['opening_hours']);
            }
            foreach ($valuesFormat as $date => $timeSlots) {
                $timeRanges = [];
                if (is_array($timeSlots)) {
                    $dateObj = new DateTime($date, DateTime::DATETIME_INTERNAL_FORMAT);
                    $html[] = '<li>';
                    $html[] = '<b>' . $dateObj->toString($this->localeDate->getDateFormatWithLongYear()) . ':</b>';
                    if (count($timeSlots) > 1) {
                        $html[] = '<ul>';
                    }

                    foreach ($timeSlots as $timeSlot) {
                        list($startHour, $startMinute) = explode(':', $timeSlot[0]);
                        list($endHour, $endMinute) = explode(':', $timeSlot[1]);

                        if ($startHour && $endHour && $startMinute && $endMinute) {
                            $currentDate = date('Y-m-d'); // Current date in 'Y-m-d' format
                            $currentTime = date('H:i');   // Current time in 'H:i' format
                            $dateTimeStart = new \DateTime($currentDate . ' ' . $currentTime, new \DateTimeZone($timezone->getConfigTimezone()));
                            $dateTimeStart->setTimezone(new \DateTimeZone($timezone->getConfigTimezone()));
                            $dateTimeStart->setTime($startHour, $startMinute);
                            $startTime = $dateTimeStart->format('h:i A');

                            $dateTimeEnd = new \DateTime($currentDate . ' ' . $currentTime, new \DateTimeZone($timezone->getConfigTimezone()));
                            $dateTimeEnd->setTimezone(new \DateTimeZone($timezone->getConfigTimezone()));
                            $dateTimeEnd->setTime($endHour, $endMinute);
                            $endTime = $dateTimeEnd->format('h:i A');

                            if (count($timeSlots) > 1) {
                                $html[] = '<li><i>' . $startTime . ' - ' . $endTime . '</i></li>';
                            } else {
                                $html[] = '<i>' . $startTime . ' - ' . $endTime . '</i>';
                            }
                        }
                    }
                    if (count($timeSlots) > 1) {
                        $html[] = '</ul>';
                    }
                    $html[] = '</li>';
                }
            }
            $html[] = '</ul>';
            return implode("\n", $html);
        }
        return '';
    }

    /**
     * Get intro
     *
     * @return string
     */
    public function getIntro()
    {
        return $this->getData(StoresInterface::INTRO);
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->getData(StoresInterface::DESCRIPTION);
    }

    /**
     * Get station
     *
     * @return string
     */
    public function getStation()
    {
        return $this->getData(StoresInterface::STATION);
    }

    /**
     * Get distance
     *
     * @return string
     */
    public function getDistance()
    {
        return $this->getData(StoresInterface::DISTANCE);
    }

    /**
     * Get details image
     *
     * @return string
     */
    public function getDetailsImage()
    {
        return $this->getData(StoresInterface::DETAILS_IMAGE);
    }

    /**
     * Get city
     *
     * @return string
     */
    public function getCity()
    {
        return $this->getData(StoresInterface::CITY);
    }

    /**
     * Get postcode
     *
     * @return string
     */
    public function getPostcode()
    {
        return $this->getData(StoresInterface::POSTCODE);
    }

    /**
     * Get region
     *
     * @return string
     */
    public function getRegion()
    {
        return $this->getData(StoresInterface::REGION);
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->getData(StoresInterface::EMAIL);
    }

    /**
     * Get image
     *
     * @return string
     */
    public function getImage()
    {
        return $this->getData(StoresInterface::IMAGE);
    }

    /**
     * @return bool|string
     * @throws LocalizedException
     */
    public function getImageUrl()
    {
        $url = false;
        $image = $this->getImage();
        if ($image) {
            if (is_string($image)) {
                $uploader = $this->uploaderPool->getUploader('image');
                $url = $uploader->getBaseUrl() . $uploader->getBasePath() . $image;
            } else {
                throw new LocalizedException(
                    __('Something went wrong while getting the image url.')
                );
            }
        }
        return $url;
    }

    /**
     * @return bool|string
     * @throws LocalizedException
     */
    public function getDetailsImageUrl()
    {
        $url = false;
        $image = $this->getDetailsImage();
        if ($image) {
            if (is_string($image)) {
                $uploader = $this->uploaderPool->getUploader('image');
                $url = $uploader->getBaseUrl() . $uploader->getBasePath() . $image;
            } else {
                throw new LocalizedException(
                    __('Something went wrong while getting the image url.')
                );
            }
        }
        return $url;
    }

    /**
     * Get external link
     *
     * @return string
     */
    public function getExternalLink()
    {
        return $this->getData(StoresInterface::EXTERNAL_LINK);
    }

    /**
     * set details image
     *
     * @param $details_image
     * @return StoresInterface
     */
    public function setDetailsImage($details_image)
    {
        return $this->setData(StoresInterface::DETAILS_IMAGE, $details_image);
    }

    /**
     * Get phone
     *
     * @return string
     */
    public function getPhone()
    {
        return $this->getData(StoresInterface::PHONE);
    }

    /**
     * Get phone
     *
     * @return string
     */
    public function getCategory()
    {
        return $this->getData(StoresInterface::CATEGORY);
    }

    /**
     * Get is_all_products
     *
     * @return string
     */
    public function getIsAllProducts()
    {
        return $this->getData(StoresInterface::IS_ALL_PRODUCTS);
    }

    /**
     * Get latitude
     *
     * @return string
     */
    public function getLatitude()
    {
        return $this->getData(StoresInterface::LATITUDE);
    }

    /**
     * Get longitude
     *
     * @return string
     */
    public function getLongitude()
    {
        return $this->getData(StoresInterface::LONGITUDE);
    }

    /**
     * Get status
     *
     * @return bool|int
     */
    public function getStatus()
    {
        return $this->getData(StoresInterface::STATUS);
    }


    /**
     * Get created at
     *
     * @return string
     */
    public function getCreatedAt()
    {
        return $this->getData(StoresInterface::CREATED_AT);
    }

    /**
     * Get updated at
     *
     * @return string
     */
    public function getUpdatedAt()
    {
        return $this->getData(StoresInterface::UPDATED_AT);
    }

    /**
     * Get identities
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * @return array
     */
    public function getStoreId()
    {
        return $this->getData(StoresInterface::STORE_ID);
    }

    /**
     * sanitize the url key
     *
     * @param $string
     * @return string
     */
    public function formatUrlKey($string)
    {
        return $this->filter->translitUrl($string);
    }

    /**
     * @return mixed
     */
    public function getStoresUrl()
    {
        return $this->urlModel->getUrl($this);
    }

    /**
     * @return bool
     */
    public function status()
    {
        return (bool)$this->getStatus();
    }

    /**
     * @param $attribute
     * @return string
     */
    public function getAttributeText($attribute)
    {
        if (!isset($this->optionProviders[$attribute])) {
            return '';
        }
        if (!($this->optionProviders[$attribute] instanceof AbstractSource)) {
            return '';
        }
        return $this->optionProviders[$attribute]->getOptionText($this->getData($attribute));
    }

    /**
     * Additional products for current slider
     * @return array
     */
    public function getProductsPosition()
    {
        if (!$this->getId()) {
            return [];
        }

        $array = $this->getData('products_position');
        if ($array === null) {
            $array = $this->getResource()->getProductsPosition($this);
            $this->setData('products_position', $array);
        }
        return $array;
    }

    public function getCountryName()
    {
        if ($this->hasData('country')) {
            try {
                $country = $this->_countryFactory->create()->loadByCode($this->getData('country'));
                return $country->getName();
            } catch (\Exception $e) {
            }
        }
        return '';
    }

    private function formatTime($hour, $minute)
    {
        $time = strtotime("$hour:$minute");
        return $this->localeDate->date($time)->format('h:i a');
    }
}
