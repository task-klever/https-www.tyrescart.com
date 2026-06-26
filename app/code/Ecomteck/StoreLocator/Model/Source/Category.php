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
namespace Ecomteck\StoreLocator\Model\Source;

use Magento\Framework\Option\ArrayInterface;

class Category extends AbstractSource implements ArrayInterface
{
    /**
     * @var \Ecomteck\StoreLocator\Model\Stores
     */
    public $storelocatorConfig;

    /**
     * @param CountryCollectionFactory $storelocatorConfig
     * @param array $options
     */
    public function __construct(
        \Ecomteck\StoreLocator\Model\Config $storelocatorConfig,
        array $options = []
    ) {
        $this->storelocatorConfig = $storelocatorConfig;
        parent::__construct($options);
    }

    /**
     * get options as key value pair
     *
     * @return array
     */
    public function toOptionArray()
    {
        if (count($this->options) == 0) {
            $categories = $this->storelocatorConfig->getCategoriesSettings();
            foreach($categories as $category){
                $this->options[] = [
                    'value' => $category,
                    'label' => $category
                ];
            }
        }
        return $this->options;
    }
}
