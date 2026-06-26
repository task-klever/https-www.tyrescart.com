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

class Output
{
    /**
     * @var \Laminas\Filter\FilterInterface
     */
    public $templateProcessor;

    /**
     * @param \Laminas\Filter\FilterInterface $templateProcessor
     */
    public function __construct(
        \Laminas\Filter\FilterInterface $templateProcessor
    ) {
        $this->templateProcessor = $templateProcessor;
    }

    /**
     * @param $string
     * @return string
     */
    public function filterOutput($string)
    {
        return $this->templateProcessor->filter($string);
    }
}
