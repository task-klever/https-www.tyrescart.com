<?php

namespace Hdweb\Vehicles\Plugin\Export;

use Magento\ImportExport\Controller\Adminhtml\Export\Export;

class VehiclesPlugin
{
    public function aroundExecute(Export $subject, \Closure $proceed)
    {
        //check export type is product attributes
        $paramsValue = $subject->getRequest()->getParams();
        if ($paramsValue['entity'] == 'vehicles') {
            // code before the original execute function
            $this->setPostDataBeforeExecute($subject);
        }
        // call the core observed function
        $returnValue = $proceed();
        return $returnValue;
    }

    public function setPostDataBeforeExecute($subject)
    {
        //set some value to post data export filter
        $customData = array('attribute_code' => '', 'attribute_set_name' => '', 'frontend_input' => '');
        $subject->getRequest()->setPostValue('export_filter', $customData);
    }
}
