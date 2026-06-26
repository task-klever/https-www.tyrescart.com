<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-dashboard
 * @version   1.3.17
 * @copyright Copyright (C) 2024 Mirasvit (https://mirasvit.com/)
 */


declare(strict_types=1);


namespace Mirasvit\Dashboard\Plugin\Magento\Backend\Model\Url;

use Magento\Backend\Model\UrlInterface;
use Magento\Framework\App\RequestInterface;

class DisableSecurityKeyPlugin
{
    private $request;

    public function __construct(RequestInterface $request)
    {
        $this->request = $request;
    }

    public function afterUseSecretKey(UrlInterface $subject, bool $result)
    {
        if (
            $this->request->getFullActionName() == 'dashboard_dashboard_mobile'
            && $this->request->getParam('token')
        ) {
            return false;
        }

        return $result;
    }
}
