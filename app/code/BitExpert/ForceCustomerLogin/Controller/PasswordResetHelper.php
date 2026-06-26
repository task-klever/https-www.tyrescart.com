<?php

/*
 * This file is part of the Force Login module for Magento2.
 *
 * (c) bitExpert AG
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BitExpert\ForceCustomerLogin\Controller;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\UrlInterface;

/**
 * Class PasswordResetHelper
 * @package BitExpert\ForceCustomerLogin\Controller
 */
class PasswordResetHelper
{
    const CREATE_PASSWORD_DIRECT_URL_SCHEME = '/customer/account/createpassword/\?.*token=';

    /**
     * @param UrlInterface $urlInstance
     * @param RequestInterface $request
     * @return bool
     */
    public function processDirectCreatePasswordRequest(UrlInterface $urlInstance, RequestInterface $request)
    {
        $url = $urlInstance->getCurrentUrl();

        // Explicit behaviour for special urls
        if (preg_match(
                sprintf(
                    '#^.*%s.*$#i',
                    self::CREATE_PASSWORD_DIRECT_URL_SCHEME
                ),
                $url
            ) === 1) {
            $params = $request->getParams();
            unset($params['token']);
            $request->setParams($params);
            return true;
        }
        return false;
    }
}
