<?php
namespace Hdweb\Brandoveride\Block\Brand;

use Magento\Framework\View\Element\Template;
use Magento\Framework\Registry;

class Breadcrumbs extends Template
{
    protected $_registry;

    public function __construct(
        Template\Context $context,
        Registry $registry,
        array $data = []
    ) {
        $this->_registry = $registry;
        parent::__construct($context, $data);
    }

    public function getBrandName()
    {
        $brand = $this->_registry->registry('current_brand'); // Make sure controller sets it
        if ($brand && $brand->getId()) {
            return $brand->getName();
        }
        return '';
    }
}
