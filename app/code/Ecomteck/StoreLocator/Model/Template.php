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

class Template
{
	
    public function toOptionArray()
    {
        return array(
            array(
                'value' => 'full_width_right_sidebar',
                'label' => 'Full Width with right sidebar',
            ),
            array(
                'value' => 'full_width_left_sidebar',
                'label' => 'Full Width with left sidebar',
            ),
            array(
                'value' => 'full_width_float_sidebar',
                'label' => 'Full Width with floating sidebar',
            ),
            array(
                'value' => 'page_right_sidebar',
                'label' => 'Page with right sidebar',
            ),
            array(
                'value' => 'page_left_sidebar',
                'label' => 'Page with left sidebar',
            ),
        );
    }
    
}
