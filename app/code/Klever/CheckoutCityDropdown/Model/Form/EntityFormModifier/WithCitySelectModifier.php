<?php

declare(strict_types=1);

namespace Klever\CheckoutCityDropdown\Model\Form\EntityFormModifier;

use Hyva\Checkout\Model\Form\EntityFormInterface;
use Hyva\Checkout\Model\Form\EntityFormModifierInterface;
use Magento\Quote\Api\Data\AddressInterface;

class WithCitySelectModifier implements EntityFormModifierInterface
{
    public function apply(EntityFormInterface $form): EntityFormInterface
    {
        $form->registerModificationListener(
            'applyCitySelectOptions',
            'form:build',
            [$this, 'applyCitySelectOptions']
        );

        return $form;
    }

    public function applyCitySelectOptions(EntityFormInterface $form): EntityFormInterface
    {
        $cityField = $form->getField(AddressInterface::KEY_CITY);

        if (!$cityField) {
            return $form;
        }

        $cityField->setOptions([
            ['value' => '', 'label' => 'Select City'],
            ['value' => 'Dubai', 'label' => 'Dubai'],
            ['value' => 'Abu Dhabi', 'label' => 'Abu Dhabi'],
            ['value' => 'Al Ain', 'label' => 'Al Ain'],
            ['value' => 'Ajman', 'label' => 'Ajman'],
            ['value' => 'Fujairah', 'label' => 'Fujairah'],
            ['value' => 'Sharjah', 'label' => 'Sharjah'],
            ['value' => 'Ras Al Khaimah', 'label' => 'Ras Al Khaimah'],
            ['value' => 'Umm Al Quwain', 'label' => 'Umm Al Quwain'],
        ]);

        return $form;
    }
}
