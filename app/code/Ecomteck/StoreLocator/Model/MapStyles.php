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

class MapStyles
{
	
    public function toOptionArray()
    {
        return array(
            array(
                'value' => 'default',
                'label' => 'Default Styles',
            ),
            array(
                'value' => 'ultra_light_with_labels',
                'label' => 'Ultra Light with Labels',
            ),
            array(
                'value' => 'light_dream',
                'label' => 'Light Dream',
            ),
            array(
                'value' => 'blue_water',
                'label' => 'Blue water',
            ),
            array(
                'value' => 'pale_Dawn',
                'label' => 'Pale Dawn',
            ),
            array(
                'value' => 'paper',
                'label' => 'Paper',
            ),
            array(
                'value' => 'light_monochrome',
                'label' => 'Light Monochrome',
            ),
            array(
                'value' => 'hopper',
                'label' => 'Hopper',
            ),
            array(
                'value' => 'custom',
                'label' => 'Custom'
            )
        );
    }
}
