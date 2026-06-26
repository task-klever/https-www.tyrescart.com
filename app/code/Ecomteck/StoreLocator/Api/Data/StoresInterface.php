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
namespace Ecomteck\StoreLocator\Api\Data;

/**
 * @api
 */
interface StoresInterface
{
    /**
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const STOCKIST_ID         = 'stores_id';
    const NAME                = 'name';
    const ADDRESS             = 'address';
    const CITY                = 'city';
    const POSTCODE            = 'postcode';
    const REGION              = 'region';
    const EMAIL               = 'email';
    const PHONE               = 'phone';
    const LATITUDE            = 'latitude';
    const LONGITUDE           = 'longitude';
    const URL_KEY                = 'url_key';
    const STATUS              = 'status';
    const TYPE                = 'type';
    const COUNTRY             = 'country';
    const IMAGE               = 'image';
    const IMAGE_1             = 'image_1';
    const IMAGE_2             = 'image_2';
    const IMAGE_3             = 'image_3';
    const IMAGE_4             = 'image_4';
    const IMAGE_5             = 'image_5';
    const CREATED_AT          = 'created_at';
    const UPDATED_AT          = 'updated_at';
    const STORE_ID            = 'store_id';
    const SCHEDULE            = 'schedule';
    const INTRO               = 'intro';
    const DESCRIPTION         = 'description';
    const DISTANCE            = 'distance';
    const STATION             = 'station';
    const DETAILS_IMAGE       = 'details_image';
    const EXTERNAL_LINK       = 'external_link';
    const OPENING_HOURS       = 'opening_hours';
    const SPECIAL_OPENING_HOURS       = 'special_opening_hours';
    const CATEGORY            = 'category';
    const IS_ALL_PRODUCTS     = 'is_all_products';
    const ISMOBILEVAN         = 'ismobilevan';
    const SHIPPING_AMOUNT     = 'shipping_amount';
    const INSTALLER_SORT_ORDER     = 'installer_sort_order';
    const GOOGLE_MAP     = 'google_map';
    const SERVICE_INCLUDED     = 'service_included';
    const OPENING_HOURS_ONE     = 'opening_hours_one';
    const OPENING_HOURS_TWO     = 'opening_hours_two';
    const SKIP_DAYS             = 'skip_days';
    const CUTOFF_TIME           = 'cutoff_time';
    const SKIP_HOURS            = 'skip_hours';
    const COMMING_SOON          = 'comming_soon';

    /**
     * Get ID
     *
     * @return int|null
     */
    public function getId();

    /**
     * Get schedule
     *
     * @return string
     */
    public function getSchedule();


    /**
     * Get intro
     *
     * @return string
     */
    public function getIntro();

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription();

    /**
     * Get external link
     *
     * @return string
     */
    public function getExternalLink();

    /**
     * Get distance
     *
     * @return string
     */
    public function getDistance();

    /**
     * Get category
     *
     * @return string
     */
    public function getCategory();

    /**
     * Get is_all_products
     *
     * @return string
     */
    public function getIsAllProducts();

    /**
     * Get station
     *
     * @return string
     */
    public function getStation();

    /**
     * Get store details image
     *
     * @return string
     */
    public function getDetailsImage();


    /**
     * Get name
     *
     * @return string
     */
    public function getName();

    /**
     * Get store url
     *
     * @return string
     */
    public function getUrlKey();
    
    /**
     * Get address
     *
     * @return string
     */
    public function getAddress();
    
    /**
     * Get city
     *
     * @return string
     */
    public function getCity();
    
    /**
     * Get postcode
     *
     * @return string
     */
    public function getPostcode();
    
    /**
     * Get region
     *
     * @return string
     */
    public function getRegion();
    
    /**
     * Get email
     *
     * @return string
     */
    public function getEmail();
    
    /**
     * Get phone
     *
     * @return string
     */
    public function getPhone();
    
    /**
     * Get image
     *
     * @return string
     */
    public function getImage();

    /**
     * Get image 1
     *
     * @return string
     */
    public function getImage1();

        /**
     * Get image 2
     *
     * @return string
     */
    public function getImage2();

        /**
     * Get image 3
     *
     * @return string
     */
    public function getImage3();

        /**
     * Get image 4
     *
     * @return string
     */
    public function getImage4();

        /**
     * Get image 5
     *
     * @return string
     */
    public function getImage5();
    
    /**
     * Get latitude
     *
     * @return string
     */
    public function getLatitude();
    
    /**
     * Get longitude
     *
     * @return string
     */
    public function getLongitude();

    /**
     * Get is active
     *
     * @return bool|int
     */
    public function getStatus();

    /**
     * Get type
     *
     * @return int
     */
    public function getType();

    /**
     * Get country
     *
     * @return string
     */
    public function getCountry();

    /**
     * set id
     *
     * @param $id
     * @return StoresInterface
     */
    public function setId($id);

    /**
     * set name
     *
     * @param $name
     * @return StoresInterface
     */
    public function setName($name);

    /**
     * set url key
     *
     * @param $urlKey
     * @return StoresInterface
     */
    public function setUrlKey($urlKey);
    
    /**
     * set image
     *
     * @param $image
     * @return AuthorInterface
     */
    public function setImage($image);

    /**
     * set image_1
     *
     * @param $image_1
     * @return AuthorInterface
     */
    public function setImage1($image_1);

        /**
     * set image_2
     *
     * @param $image_2 
     * @return AuthorInterface
     */
    public function setImage2($image_2);

        /**
     * set image_3
     *
     * @param $image_3
     * @return AuthorInterface
     */
    public function setImage3($image_3);

        /**
     * set image_4
     *
     * @param $image_4
     * @return AuthorInterface
     */
    public function setImage4($image_4);

        /**
     * set image_5
     *
     * @param $image_5
     * @return AuthorInterface
     */
    public function setImage5($image_5);
    
    /**
     * set address
     *
     * @param $address
     * @return StoresInterface
     */
    public function setAddress($address);

    /**
     * set city
     *
     * @param $city
     * @return StoresInterface
     */
    public function setCity($city);
    
    /**
     * set postcode
     *
     * @param $postcode
     * @return StoresInterface
     */
    public function setPostcode($postcode);


    /**
     * set schedule
     *
     * @param $schedule
     * @return StoresInterface
     */
    public function setSchedule($schedule);

    /**
     * set category
     *
     * @param $category
     * @return StoresInterface
     */
    public function setCategory($category);

    /**
     * set is_all_products
     *
     * @param $isAllProducts
     * @return StoresInterface
     */
    public function setIsAllProducts($isAllProducts);

    /**
     * set description
     *
     * @param $description
     * @return StoresInterface
     */
    public function setDescription($description);

    /**
     * set distance
     *
     * @param $distance
     * @return StoresInterface
     */
    public function setDistance($distance);

    /**
     * set station
     *
     * @param $station
     * @return StoresInterface
     */
    public function setStation($station);

    /**
     * set external link
     *
     * @param $external_link
     * @return StoresInterface
     */
    public function setExternalLink($external_link);

    /**
     * set intro
     *
     * @param $intro
     * @return StoresInterface
     */
    public function setIntro($intro);

    /**
     * set store details image
     *
     * @param $details_image
     * @return StoresInterface
     */
    public function setDetailsImage($details_image);

    /**
     * set region
     *
     * @param $region
     * @return StoresInterface
     */
    public function setRegion($region);

    /**
     * set email
     *
     * @param $email
     * @return StoresInterface
     */
    public function setEmail($email);
    
    /**
     * set phone
     *
     * @param $phone
     * @return StoresInterface
     */
    public function setPhone($phone);

    /**
     * set latitude
     *
     * @param $latitude
     * @return StoresInterface
     */
    public function setLatitude($latitude);
    
    /**
     * set longitude
     *
     * @param $longitude
     * @return StoresInterface
     */
    public function setLongitude($longitude);

    /**
     * Set status
     *
     * @param $status
     * @return StoresInterface
     */
    public function setStatus($status);

    /**
     * set type
     *
     * @param $type
     * @return StoresInterface
     */
    public function setType($type);

    /**
     * Set country
     *
     * @param $country
     * @return StoresInterface
     */
    public function setCountry($country);

    /**
     * Set Is Mobile Van status
     *
     * @param bool|int $isMobileVan
     * @return \Ecomteck\StoreLocator\Api\Data\StoresInterface
     */
    public function setIsmobilevan($ismobilevan);

    /**
    * Set shipping amount
    *
    * @param bool|int $shipping_amount
    * @return \Ecomteck\StoreLocator\Api\Data\StoresInterface
    */
    public function setShippingAmount($shipping_amount);

    /**
     * Set installer sort order
     *
     * @param bool|int $installer_sort_order
     * @return \Ecomteck\StoreLocator\Api\Data\StoresInterface
     */
    public function setInstallerSortOrder($installer_sort_order);

    /**
     * Get created at
     *
     * @return string
     */
    public function getCreatedAt();

    /**
     * set created at
     *
     * @param $createdAt
     * @return StoresInterface
     */
    public function setCreatedAt($createdAt);

    /**
     * Get updated at
     *
     * @return string
     */
    public function getUpdatedAt();

    /**
     * set updated at
     *
     * @param $updatedAt
     * @return StoresInterface
     */
    public function setUpdatedAt($updatedAt);

    /**
     * @param $storeId
     * @return StoresInterface
     */
    public function setStoreId($storeId);

    /**
     * @return int[]
     */
    public function getStoreId();

    /**
     * set opening hours
     *
     * @param $openingHours
     * @return StoresInterface
     */
    public function setOpeningHours($openingHours);

    /**
     * get opening hours
     *
     * @return Array
     */
    public function getOpeningHours();

    /**
     * get opening hours
     *
     * @return Array
     */
    public function getOpeningHoursConfig();
    

    /**
     * get opening hours formated
     *
     * @return string
     */
    public function getOpeningHoursFormated();

    /**
     * set special opening hours
     *
     * @param $specialOpeningHours
     * @return StoresInterface
     */
    public function setSpecialOpeningHours($specialOpeningHours);

    /**
     * get special opening hours
     *
     * @return Array
     */
    public function getSpecialOpeningHours();

    /**
     * get special opening hours formated
     *
     * @return string
     */
    public function getSpecialOpeningHoursFormated();

    /**
     * get special opening hours
     *
     * @return Array
     */
    public function getSpecialOpeningHoursConfig();

    /**
     * Get skip days
     *
     * @return string
     */
    public function getSkipDays();

    /**
     * Set skip days
     *
     * @param $skipDays
     * @return StoresInterface
     */
    public function setSkipDays($skipDays);

    /**
     * Get cutoff time
     *
     * @return string
     */
    public function getCutoffTime();

    /**
     * Set cutoff time
     *
     * @param $cutoffTime
     * @return StoresInterface
     */
    public function setCutoffTime($cutoffTime);

    /**
     * Get skip hours
     *
     * @return int
     */
    public function getSkipHours();

    /**
     * Set skip hours
     *
     * @param $skipHours
     * @return StoresInterface
     */
    public function setSkipHours($skipHours);

    /**
     * Get comming soon
     *
     * @return string
     */
    public function getCommingSoon();

    /**
     * Set comming soon
     *
     * @param $commingSoon
     * @return StoresInterface
     */
    public function setCommingSoon($commingSoon);

}
