<?php
/**
 * Ecomteck
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Ecomteck.com license that is
 * available through the world-wide-web at this URL:
 * https://ecomteck.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Ecomteck
 * @package     Ecomteck_CustomerCustomAttributes
 * @copyright   Copyright (c) 2018 Ecomteck (https://ecomteck.com/)
 * @license     https://ecomteck.com/LICENSE.txt
 */
namespace Ecomteck\CustomerCustomAttributes\Controller\Adminhtml\Customer\Formtype;

class Edit extends \Ecomteck\CustomerCustomAttributes\Controller\Adminhtml\Customer\Formtype
{
    /**
     * Edit Form Type
     *
     * @return void
     */
    public function execute()
    {
        $this->_coreRegistry->register('edit_mode', 'edit');
        $this->_initFormType();
        $this->_initAction();
        $this->_view->renderLayout();
    }
}
