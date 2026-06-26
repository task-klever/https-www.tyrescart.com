<?php

declare(strict_types=1);

namespace Klever\OrderActions\Plugin;

use Magento\Sales\Block\Adminhtml\Order\View;

class RemoveOrderViewButtons
{
    /**
     * Button IDs to remove from the order view header.
     */
    private const BUTTONS_TO_REMOVE = [
        'generate_po',
        'generate_fpo',
        'generate_dpo',
        'generate_ppo',
        'installer_mail_button',
        'notify_customer_button',
        'edit_installer_button',
        'notify_abandoned_mail',
    ];

    /**
     * Remove custom buttons from order view header bar.
     * Runs after all other plugins have added their buttons (sortOrder=200).
     *
     * @param View $view
     * @return null
     */
    public function beforeSetLayout(View $view)
    {
        foreach (self::BUTTONS_TO_REMOVE as $buttonId) {
            $view->removeButton($buttonId);
        }

        return null;
    }
}
