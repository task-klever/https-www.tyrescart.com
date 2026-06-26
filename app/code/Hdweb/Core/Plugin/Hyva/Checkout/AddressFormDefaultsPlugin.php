<?php

declare(strict_types=1);

namespace Hdweb\Core\Plugin\Hyva\Checkout;

use Hyva\Checkout\Magewire\Checkout\AddressView\AbstractMagewireAddressForm;

class AddressFormDefaultsPlugin
{
    /**
     * After boot, set default values for hidden fields on both address property and form.
     */
    public function afterBoot(AbstractMagewireAddressForm $subject, $result = null): void
    {
        $this->applyDefaultsToComponent($subject);
    }

    /**
     * Before updatingAddress, inject defaults for hidden fields.
     */
    public function beforeUpdatingAddress(AbstractMagewireAddressForm $subject, array $address): array
    {
        return [$this->applyDefaults($address)];
    }

    /**
     * Before any save operation, ensure defaults are applied to address and form.
     */
    public function beforeSave(AbstractMagewireAddressForm $subject): void
    {
        $this->applyDefaultsToComponent($subject);
    }

    /**
     * Before dehydrate, ensure defaults are applied (dehydrate can trigger save).
     */
    public function beforeDehydrate(AbstractMagewireAddressForm $subject): void
    {
        $this->applyDefaultsToComponent($subject);
    }

    /**
     * Apply defaults to the component's address property and sync to form.
     */
    private function applyDefaultsToComponent(AbstractMagewireAddressForm $subject): void
    {
        $address = $this->applyDefaults($subject->address);
        $subject->address = $address;

        try {
            $subject->getForm()->fill($address);
        } catch (\Exception $e) {
            // Form may not be initialized yet
        }
    }

    /**
     * Apply default values to hidden address fields.
     */
    private function applyDefaults(array $address): array
    {
        if (empty($address['lastname'])) {
            $address['lastname'] = '.';
        }

        if (isset($address['street']) && is_array($address['street'])) {
            foreach ($address['street'] as $key => $line) {
                if (empty($line)) {
                    $address['street'][$key] = '.';
                }
            }
        } elseif (empty($address['street'])) {
            $address['street'] = ['.'];
        }

        return $address;
    }
}
