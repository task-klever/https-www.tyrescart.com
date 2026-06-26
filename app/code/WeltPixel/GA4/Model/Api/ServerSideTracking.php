<?php

namespace WeltPixel\GA4\Model\Api;

/**
 * Class \WeltPixel\GA4\Model\Api
 */
class ServerSideTracking extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Item types
     */
    const TYPE_CLIENT_GAAW = 'gaaw_client';
    const TYPE_CLIENT_GTM = 'gtm_client';
    const TYPE_VARIABLE_CONSTANT = 'c';
    const TYPE_TRIGGER_CUSTOM_EVENT = 'customEvent';
    const TYPE_TAG_SGTMGAAW = 'sgtmgaaw';


    const CLIENT_GA4 = 'GA4';

    /**
     * Variable names
     */
    const VARIABLE_MEASUREMENT_ID = 'WP - Measurement ID';

    /**
     * Trigger names
     */
    const TRIGGER_SELECT_ITEM = 'WP - Select Item';
    const TRIGGER_ADD_TO_CART = 'WP - Add To Cart';
    const TRIGGER_REMOVE_FROM_CART = 'WP - Remove From Cart';
    const TRIGGER_VIEW_CART = 'WP - View Cart';
    const TRIGGER_VIEW_ITEM = 'WP - View Item';
    const TRIGGER_VIEW_ITEM_LIST = 'WP - View Item List';
    const TRIGGER_SELECT_PROMOTION = 'WP - Select Promotion';
    const TRIGGER_VIEW_PROMOTION = 'WP - View Promotion';
    const TRIGGER_BEGIN_CHECKOUT = 'WP - Begin Checkout';
    const TRIGGER_PURCHASE = 'WP - Purchase';
    const TRIGGER_ADD_SHIPPING_INFO = 'WP - Add Shipping Info';
    const TRIGGER_ADD_PAYMENT_INFO = 'WP - Add Payment Info';
    const TRIGGER_ADD_TO_WISHLIST = 'WP - Add To Wishlist';
    const TRIGGER_SEARCH = 'WP - Search';
    const TRIGGER_LOGIN = 'WP - Login';
    const TRIGGER_SIGNUP = 'WP - Sign Up';

    const TRIGGER_ALL_PAGES_ID = '2147479574';

    /**
     * Tag names
     */
    const TAG_MEASUREMENT_ID = 'WP - GA4';
    const TAG_SELECT_ITEM = 'WP - Select Item';
    const TAG_ADD_TO_CART = 'WP - Add To Cart';
    const TAG_REMOVE_FROM_CART = 'WP - Remove From Cart';
    const TAG_VIEW_CART = 'WP - View Cart';
    const TAG_VIEW_ITEM = 'WP - View Item';
    const TAG_VIEW_ITEM_LIST = 'WP - View Item List';
    const TAG_SELECT_PROMOTION = 'WP - Select Promotion';
    const TAG_VIEW_PROMOTION = 'WP - View Promotion';
    const TAG_BEGIN_CHECKOUT = 'WP - Begin Checkout';
    const TAG_PURCHASE = 'WP - Purchase';
    const TAG_ADD_SHIPPING_INFO = 'WP - Add Shipping Info';
    const TAG_ADD_PAYMENT_INFO = 'WP - Add Payment Info';
    const TAG_ADD_TO_WISHLIST = 'WP - Add To Wishlist';
    const TAG_SEARCH = 'WP - Search';
    const TAG_LOGIN = 'WP - Login';
    const TAG_SIGNUP = 'WP - Sign Up';

    private function _getClients($publicId)
    {
        $clients = [
            self::CLIENT_GA4 => [
                'name' => self::CLIENT_GA4,
                'type' => self::TYPE_CLIENT_GAAW,
                'parameter' => [
                    [
                        'type' => 'boolean',
                        'key' => 'activateDefaultPaths',
                        'value' => "true"
                    ],
                    [
                        'type' => 'template',
                        'key' => 'cookieManagement',
                        'value' => "server"
                    ],
                    [
                        'type' => 'template',
                        'key' => 'cookieName',
                        'value' => "FPID"
                    ],
                    [
                        'type' => 'template',
                        'key' => 'cookieDomain',
                        'value' => "auto"
                    ],
                    [
                        'type' => 'template',
                        'key' => 'cookiePath',
                        'value' => "/"
                    ],
                    [
                        'type' => 'template',
                        'key' => 'cookieMaxAgeInSec',
                        'value' => "63072000"
                    ]
                ]
            ]
        ];

        return $clients;
    }

    /**
     * Return list of variables for api creation
     * @param $measurementId
     * @return array
     */
    private function _getVariables($measurementId)
    {
        $variables = [
            self::VARIABLE_MEASUREMENT_ID => [
                'name' => self::VARIABLE_MEASUREMENT_ID,
                'type' => self::TYPE_VARIABLE_CONSTANT,
                'parameter' => [
                    [
                        'type' => 'template',
                        'key' => 'value',
                        'value' => $measurementId
                    ]
                ]
            ]
        ];
        return $variables;
    }

    /**
     * Return list of triggers for api creation
     * @return array
     */
    private function _getTriggers()
    {
        $triggers = [
            self::TRIGGER_SELECT_ITEM => [
                'name' => self::TRIGGER_SELECT_ITEM,
                'type' => self::TYPE_TRIGGER_CUSTOM_EVENT,
                'customEventFilter' => [
                    [
                        'type' => 'equals',
                        'parameter' => [
                            [
                                'type' => 'template',
                                'key' => 'arg0',
                                'value' => '{{_event}}'
                            ],
                            [
                                'type' => 'template',
                                'key' => 'arg1',
                                'value' => 'select_item'
                            ]
                        ]
                    ]
                ]
            ],
            self::TRIGGER_ADD_TO_CART => [
                'name' => self::TRIGGER_ADD_TO_CART,
                'type' => self::TYPE_TRIGGER_CUSTOM_EVENT,
                'customEventFilter' => [
                    [
                        'type' => 'equals',
                        'parameter' => [
                            [
                                'type' => 'template',
                                'key' => 'arg0',
                                'value' => '{{_event}}'
                            ],
                            [
                                'type' => 'template',
                                'key' => 'arg1',
                                'value' => 'add_to_cart'
                            ]
                        ]
                    ]
                ]
            ],
            self::TRIGGER_REMOVE_FROM_CART => [
                'name' => self::TRIGGER_REMOVE_FROM_CART,
                'type' => self::TYPE_TRIGGER_CUSTOM_EVENT,
                'customEventFilter' => [
                    [
                        'type' => 'equals',
                        'parameter' => [
                            [
                                'type' => 'template',
                                'key' => 'arg0',
                                'value' => '{{_event}}'
                            ],
                            [
                                'type' => 'template',
                                'key' => 'arg1',
                                'value' => 'remove_from_cart'
                            ]
                        ]
                    ]
                ]
            ],
            self::TRIGGER_VIEW_CART => [
                'name' => self::TRIGGER_VIEW_CART,
                'type' => self::TYPE_TRIGGER_CUSTOM_EVENT,
                'customEventFilter' => [
                    [
                        'type' => 'equals',
                        'parameter' => [
                            [
                                'type' => 'template',
                                'key' => 'arg0',
                                'value' => '{{_event}}'
                            ],
                            [
                                'type' => 'template',
                                'key' => 'arg1',
                                'value' => 'view_cart'
                            ]
                        ]
                    ]
                ]
            ],
            self::TRIGGER_SELECT_PROMOTION => [
                'name' => self::TRIGGER_SELECT_PROMOTION,
                'type' => self::TYPE_TRIGGER_CUSTOM_EVENT,
                'customEventFilter' => [
                    [
                        'type' => 'equals',
                        'parameter' => [
                            [
                                'type' => 'template',
                                'key' => 'arg0',
                                'value' => '{{_event}}'
                            ],
                            [
                                'type' => 'template',
                                'key' => 'arg1',
                                'value' => 'select_promotion'
                            ]
                        ]
                    ]
                ]
            ],
            self::TRIGGER_BEGIN_CHECKOUT => [
                'name' => self::TRIGGER_BEGIN_CHECKOUT,
                'type' => self::TYPE_TRIGGER_CUSTOM_EVENT,
                'customEventFilter' => [
                    [
                        'type' => 'equals',
                        'parameter' => [
                            [
                                'type' => 'template',
                                'key' => 'arg0',
                                'value' => '{{_event}}'
                            ],
                            [
                                'type' => 'template',
                                'key' => 'arg1',
                                'value' => 'begin_checkout'
                            ]
                        ]
                    ]
                ]
            ],
            self::TRIGGER_VIEW_ITEM_LIST => [
                'name' => self::TRIGGER_VIEW_ITEM_LIST,
                'type' => self::TYPE_TRIGGER_CUSTOM_EVENT,
                'customEventFilter' => [
                    [
                        'type' => 'equals',
                        'parameter' => [
                            [
                                'type' => 'template',
                                'key' => 'arg0',
                                'value' => '{{_event}}'
                            ],
                            [
                                'type' => 'template',
                                'key' => 'arg1',
                                'value' => 'view_item_list'
                            ]
                        ]
                    ]
                ]
            ],
            self::TRIGGER_VIEW_ITEM => [
                'name' => self::TRIGGER_VIEW_ITEM,
                'type' => self::TYPE_TRIGGER_CUSTOM_EVENT,
                'customEventFilter' => [
                    [
                        'type' => 'equals',
                        'parameter' => [
                            [
                                'type' => 'template',
                                'key' => 'arg0',
                                'value' => '{{_event}}'
                            ],
                            [
                                'type' => 'template',
                                'key' => 'arg1',
                                'value' => 'view_item'
                            ]
                        ]
                    ]
                ]
            ],
            self::TRIGGER_VIEW_PROMOTION => [
                'name' => self::TRIGGER_VIEW_PROMOTION,
                'type' => self::TYPE_TRIGGER_CUSTOM_EVENT,
                'customEventFilter' => [
                    [
                        'type' => 'equals',
                        'parameter' => [
                            [
                                'type' => 'template',
                                'key' => 'arg0',
                                'value' => '{{_event}}'
                            ],
                            [
                                'type' => 'template',
                                'key' => 'arg1',
                                'value' => 'view_promotion'
                            ]
                        ]
                    ]
                ]
            ],
            self::TRIGGER_PURCHASE => [
                'name' => self::TRIGGER_PURCHASE,
                'type' => self::TYPE_TRIGGER_CUSTOM_EVENT,
                'customEventFilter' => [
                    [
                        'type' => 'equals',
                        'parameter' => [
                            [
                                'type' => 'template',
                                'key' => 'arg0',
                                'value' => '{{_event}}'
                            ],
                            [
                                'type' => 'template',
                                'key' => 'arg1',
                                'value' => 'purchase'
                            ]
                        ]
                    ]
                ]
            ],
            self::TRIGGER_ADD_SHIPPING_INFO => [
                'name' => self::TRIGGER_ADD_SHIPPING_INFO,
                'type' => self::TYPE_TRIGGER_CUSTOM_EVENT,
                'customEventFilter' => [
                    [
                        'type' => 'equals',
                        'parameter' => [
                            [
                                'type' => 'template',
                                'key' => 'arg0',
                                'value' => '{{_event}}'
                            ],
                            [
                                'type' => 'template',
                                'key' => 'arg1',
                                'value' => 'add_shipping_info'
                            ]
                        ]
                    ]
                ]
            ],
            self::TRIGGER_ADD_PAYMENT_INFO => [
                'name' => self::TRIGGER_ADD_PAYMENT_INFO,
                'type' => self::TYPE_TRIGGER_CUSTOM_EVENT,
                'customEventFilter' => [
                    [
                        'type' => 'equals',
                        'parameter' => [
                            [
                                'type' => 'template',
                                'key' => 'arg0',
                                'value' => '{{_event}}'
                            ],
                            [
                                'type' => 'template',
                                'key' => 'arg1',
                                'value' => 'add_payment_info'
                            ]
                        ]
                    ]
                ]
            ],
            self::TRIGGER_ADD_TO_WISHLIST => [
                'name' => self::TRIGGER_ADD_TO_WISHLIST ,
                'type' => self::TYPE_TRIGGER_CUSTOM_EVENT,
                'customEventFilter' => [
                    [
                        'type' => 'equals',
                        'parameter' => [
                            [
                                'type' => 'template',
                                'key' => 'arg0',
                                'value' => '{{_event}}'
                            ],
                            [
                                'type' => 'template',
                                'key' => 'arg1',
                                'value' => 'add_to_wishlist'
                            ]
                        ]
                    ]
                ]
            ],
            self::TRIGGER_SEARCH => [
                'name' => self::TRIGGER_SEARCH ,
                'type' => self::TYPE_TRIGGER_CUSTOM_EVENT,
                'customEventFilter' => [
                    [
                        'type' => 'equals',
                        'parameter' => [
                            [
                                'type' => 'template',
                                'key' => 'arg0',
                                'value' => '{{_event}}'
                            ],
                            [
                                'type' => 'template',
                                'key' => 'arg1',
                                'value' => 'search'
                            ]
                        ]
                    ]
                ]
            ],
            self::TRIGGER_LOGIN => [
                'name' => self::TRIGGER_LOGIN ,
                'type' => self::TYPE_TRIGGER_CUSTOM_EVENT,
                'customEventFilter' => [
                    [
                        'type' => 'equals',
                        'parameter' => [
                            [
                                'type' => 'template',
                                'key' => 'arg0',
                                'value' => '{{_event}}'
                            ],
                            [
                                'type' => 'template',
                                'key' => 'arg1',
                                'value' => 'login'
                            ]
                        ]
                    ]
                ]
            ],
            self::TRIGGER_SIGNUP => [
                'name' => self::TRIGGER_SIGNUP ,
                'type' => self::TYPE_TRIGGER_CUSTOM_EVENT,
                'customEventFilter' => [
                    [
                        'type' => 'equals',
                        'parameter' => [
                            [
                                'type' => 'template',
                                'key' => 'arg0',
                                'value' => '{{_event}}'
                            ],
                            [
                                'type' => 'template',
                                'key' => 'arg1',
                                'value' => 'sign_up'
                            ]
                        ]
                    ]
                ]
            ]
        ];
        return $triggers;
    }

    /**
     * Return list of tags for api creation
     * @param array $triggers
     * @param string $measurementId
     * @return array
     */
    private function _getTags($triggers, $measurementId)
    {
        $tags = [
            self::TAG_MEASUREMENT_ID => [
                'name' => self::TAG_MEASUREMENT_ID,
                'firingTriggerId' => [
                    self::TRIGGER_ALL_PAGES_ID
                ],
                'tagFiringOption' => 'oncePerEvent',
                'type' => self::TYPE_TAG_SGTMGAAW,
                'parameter' => [
                    [
                        'type' => 'boolean',
                        'key' => 'redactVisitorIp',
                        'value' => "false"
                    ],
                    [
                        'type' => 'TEMPLATE',
                        'key' => 'epToIncludeDropdown',
                        'value' => "all"
                    ],
                    [
                        'type' => 'TEMPLATE',
                        'key' => 'eventName',
                        'value' => "page_view"
                    ],
                    [
                        'type' => 'TEMPLATE',
                        'key' => 'upToIncludeDropdown',
                        'value' => "all"
                    ],
                    [
                        'type' => 'TEMPLATE',
                        'key' => 'measurementId',
                        'value' => $measurementId
                    ]
                ],
                'monitoringMetadata' => [
                    'type' => "MAP"
                ],
                'consentSettings' => [
                    'consentStatus' => "NOT_SET"
                ]
            ],
            self::TAG_ADD_TO_CART => [
                'name' => self::TAG_ADD_TO_CART,
                'firingTriggerId' => [
                    $triggers[self::TRIGGER_ADD_TO_CART]
                ],
                'tagFiringOption' => 'oncePerEvent',
                'type' => self::TYPE_TAG_SGTMGAAW,
                'parameter' => [
                    [
                        'type' => 'boolean',
                        'key' => 'redactVisitorIp',
                        'value' => "false"
                    ],
                    [
                        'type' => 'TEMPLATE',
                        'key' => 'epToIncludeDropdown',
                        'value' => "all"
                    ],
                    [
                        'type' => 'TEMPLATE',
                        'key' => 'eventName',
                        'value' => "add_to_cart"
                    ],
                    [
                        'type' => 'TEMPLATE',
                        'key' => 'upToIncludeDropdown',
                        'value' => "all"
                    ],
                    [
                        'type' => 'TEMPLATE',
                        'key' => 'measurementId',
                        'value' => '{{' . self::VARIABLE_MEASUREMENT_ID . '}}'
                    ]
                ],
                'monitoringMetadata' => [
                    'type' => "MAP"
                ],
                'consentSettings' => [
                    'consentStatus' => "NOT_SET"
                ]
            ],
            self::TAG_PURCHASE => [
                'name' => self::TAG_PURCHASE,
                'firingTriggerId' => [
                    $triggers[self::TRIGGER_PURCHASE]
                ],
                'tagFiringOption' => 'oncePerEvent',
                'type' => self::TYPE_TAG_SGTMGAAW,
                'parameter' => [
                    [
                        'type' => 'boolean',
                        'key' => 'redactVisitorIp',
                        'value' => "false"
                    ],
                    [
                        'type' => 'TEMPLATE',
                        'key' => 'epToIncludeDropdown',
                        'value' => "all"
                    ],
                    [
                        'type' => 'TEMPLATE',
                        'key' => 'eventName',
                        'value' => "purchase"
                    ],
                    [
                        'type' => 'TEMPLATE',
                        'key' => 'upToIncludeDropdown',
                        'value' => "all"
                    ],
                    [
                        'type' => 'TEMPLATE',
                        'key' => 'measurementId',
                        'value' => '{{' . self::VARIABLE_MEASUREMENT_ID . '}}'
                    ]
                ],
                'monitoringMetadata' => [
                    'type' => "MAP"
                ],
                'consentSettings' => [
                    'consentStatus' => "NOT_SET"
                ]
            ],
            self::TAG_BEGIN_CHECKOUT => [
                'name' => self::TAG_BEGIN_CHECKOUT,
                'firingTriggerId' => [
                    $triggers[self::TRIGGER_BEGIN_CHECKOUT]
                ],
                'tagFiringOption' => 'oncePerEvent',
                'type' => self::TYPE_TAG_SGTMGAAW,
                'parameter' => [
                    [
                        'type' => 'boolean',
                        'key' => 'redactVisitorIp',
                        'value' => "false"
                    ],
                    [
                        'type' => 'TEMPLATE',
                        'key' => 'epToIncludeDropdown',
                        'value' => "all"
                    ],
                    [
                        'type' => 'TEMPLATE',
                        'key' => 'eventName',
                        'value' => "begin_checkout"
                    ],
                    [
                        'type' => 'TEMPLATE',
                        'key' => 'upToIncludeDropdown',
                        'value' => "all"
                    ],
                    [
                        'type' => 'TEMPLATE',
                        'key' => 'measurementId',
                        'value' => '{{' . self::VARIABLE_MEASUREMENT_ID . '}}'
                    ]
                ],
                'monitoringMetadata' => [
                    'type' => "MAP"
                ],
                'consentSettings' => [
                    'consentStatus' => "NOT_SET"
                ]
            ],
            self::TAG_VIEW_ITEM_LIST => [
                'name' => self::TAG_VIEW_ITEM_LIST,
                'firingTriggerId' => [
                    $triggers[self::TRIGGER_VIEW_ITEM_LIST]
                ],
                'tagFiringOption' => 'oncePerEvent',
                'type' => self::TYPE_TAG_SGTMGAAW,
                'parameter' => [
                    [
                        'type' => 'boolean',
                        'key' => 'redactVisitorIp',
                        'value' => "false"
                    ],
                    [
                        'type' => 'TEMPLATE',
                        'key' => 'epToIncludeDropdown',
                        'value' => "all"
                    ],
                    [
                        'type' => 'TEMPLATE',
                        'key' => 'eventName',
                        'value' => "view_item_list"
                    ],
                    [
                        'type' => 'TEMPLATE',
                        'key' => 'upToIncludeDropdown',
                        'value' => "all"
                    ],
                    [
                        'type' => 'TEMPLATE',
                        'key' => 'measurementId',
                        'value' => '{{' . self::VARIABLE_MEASUREMENT_ID . '}}'
                    ]
                ],
                'monitoringMetadata' => [
                    'type' => "MAP"
                ],
                'consentSettings' => [
                    'consentStatus' => "NOT_SET"
                ]
            ],
            self::TAG_VIEW_ITEM => [
                'name' => self::TAG_VIEW_ITEM,
                'firingTriggerId' => [
                    $triggers[self::TRIGGER_VIEW_ITEM]
                ],
                'tagFiringOption' => 'oncePerEvent',
                'type' => self::TYPE_TAG_SGTMGAAW,
                'parameter' => [
                    [
                        'type' => 'boolean',
                        'key' => 'redactVisitorIp',
                        'value' => "false"
                    ],
                    [
                        'type' => 'TEMPLATE',
                        'key' => 'epToIncludeDropdown',
                        'value' => "all"
                    ],
                    [
                        'type' => 'TEMPLATE',
                        'key' => 'eventName',
                        'value' => "view_item"
                    ],
                    [
                        'type' => 'TEMPLATE',
                        'key' => 'upToIncludeDropdown',
                        'value' => "all"
                    ],
                    [
                        'type' => 'TEMPLATE',
                        'key' => 'measurementId',
                        'value' => '{{' . self::VARIABLE_MEASUREMENT_ID . '}}'
                    ]
                ],
                'monitoringMetadata' => [
                    'type' => "MAP"
                ],
                'consentSettings' => [
                    'consentStatus' => "NOT_SET"
                ]
            ],
            self::TAG_REMOVE_FROM_CART => [
                'name' => self::TAG_REMOVE_FROM_CART,
                'firingTriggerId' => [
                    $triggers[self::TRIGGER_REMOVE_FROM_CART]
                ],
                'tagFiringOption' => 'oncePerEvent',
                'type' => self::TYPE_TAG_SGTMGAAW,
                'parameter' => [
                    [
                        'type' => 'boolean',
                        'key' => 'redactVisitorIp',
                        'value' => "false"
                    ],
                    [
                        'type' => 'TEMPLATE',
                        'key' => 'epToIncludeDropdown',
                        'value' => "all"
                    ],
                    [
                        'type' => 'TEMPLATE',
                        'key' => 'eventName',
                        'value' => "remove_from_cart"
                    ],
                    [
                        'type' => 'TEMPLATE',
                        'key' => 'upToIncludeDropdown',
                        'value' => "all"
                    ],
                    [
                        'type' => 'TEMPLATE',
                        'key' => 'measurementId',
                        'value' => '{{' . self::VARIABLE_MEASUREMENT_ID . '}}'
                    ]
                ],
                'monitoringMetadata' => [
                    'type' => "MAP"
                ],
                'consentSettings' => [
                    'consentStatus' => "NOT_SET"
                ]
            ],
            self::TAG_ADD_PAYMENT_INFO => [
                'name' => self::TAG_ADD_PAYMENT_INFO,
                'firingTriggerId' => [
                    $triggers[self::TRIGGER_ADD_PAYMENT_INFO]
                ],
                'tagFiringOption' => 'oncePerEvent',
                'type' => self::TYPE_TAG_SGTMGAAW,
                'parameter' => [
                    [
                        'type' => 'boolean',
                        'key' => 'redactVisitorIp',
                        'value' => "false"
                    ],
                    [
                        'type' => 'TEMPLATE',
                        'key' => 'epToIncludeDropdown',
                        'value' => "all"
                    ],
                    [
                        'type' => 'TEMPLATE',
                        'key' => 'eventName',
                        'value' => "add_payment_info"
                    ],
                    [
                        'type' => 'TEMPLATE',
                        'key' => 'upToIncludeDropdown',
                        'value' => "all"
                    ],
                    [
                        'type' => 'TEMPLATE',
                        'key' => 'measurementId',
                        'value' => '{{' . self::VARIABLE_MEASUREMENT_ID . '}}'
                    ]
                ],
                'monitoringMetadata' => [
                    'type' => "MAP"
                ],
                'consentSettings' => [
                    'consentStatus' => "NOT_SET"
                ]
            ],
            self::TAG_ADD_SHIPPING_INFO => [
                'name' => self::TAG_ADD_SHIPPING_INFO,
                'firingTriggerId' => [
                    $triggers[self::TRIGGER_ADD_SHIPPING_INFO]
                ],
                'tagFiringOption' => 'oncePerEvent',
                'type' => self::TYPE_TAG_SGTMGAAW,
                'parameter' => [
                    [
                        'type' => 'boolean',
                        'key' => 'redactVisitorIp',
                        'value' => "false"
                    ],
                    [
                        'type' => 'TEMPLATE',
                        'key' => 'epToIncludeDropdown',
                        'value' => "all"
                    ],
                    [
                        'type' => 'TEMPLATE',
                        'key' => 'eventName',
                        'value' => "add_shipping_info"
                    ],
                    [
                        'type' => 'TEMPLATE',
                        'key' => 'upToIncludeDropdown',
                        'value' => "all"
                    ],
                    [
                        'type' => 'TEMPLATE',
                        'key' => 'measurementId',
                        'value' => '{{' . self::VARIABLE_MEASUREMENT_ID . '}}'
                    ]
                ],
                'monitoringMetadata' => [
                    'type' => "MAP"
                ],
                'consentSettings' => [
                    'consentStatus' => "NOT_SET"
                ]
            ],
            self::TAG_ADD_TO_WISHLIST => [
                'name' => self::TAG_ADD_TO_WISHLIST,
                'firingTriggerId' => [
                    $triggers[self::TRIGGER_ADD_TO_WISHLIST]
                ],
                'tagFiringOption' => 'oncePerEvent',
                'type' => self::TYPE_TAG_SGTMGAAW,
                'parameter' => [
                    [
                        'type' => 'boolean',
                        'key' => 'redactVisitorIp',
                        'value' => "false"
                    ],
                    [
                        'type' => 'TEMPLATE',
                        'key' => 'epToIncludeDropdown',
                        'value' => "all"
                    ],
                    [
                        'type' => 'TEMPLATE',
                        'key' => 'eventName',
                        'value' => "add_to_wishlist"
                    ],
                    [
                        'type' => 'TEMPLATE',
                        'key' => 'upToIncludeDropdown',
                        'value' => "all"
                    ],
                    [
                        'type' => 'TEMPLATE',
                        'key' => 'measurementId',
                        'value' => '{{' . self::VARIABLE_MEASUREMENT_ID . '}}'
                    ]
                ],
                'monitoringMetadata' => [
                    'type' => "MAP"
                ],
                'consentSettings' => [
                    'consentStatus' => "NOT_SET"
                ]
            ],
            self::TAG_SELECT_PROMOTION => [
                'name' => self::TAG_SELECT_PROMOTION,
                'firingTriggerId' => [
                    $triggers[self::TRIGGER_SELECT_PROMOTION]
                ],
                'tagFiringOption' => 'oncePerEvent',
                'type' => self::TYPE_TAG_SGTMGAAW,
                'parameter' => [
                    [
                        'type' => 'boolean',
                        'key' => 'redactVisitorIp',
                        'value' => "false"
                    ],
                    [
                        'type' => 'TEMPLATE',
                        'key' => 'epToIncludeDropdown',
                        'value' => "all"
                    ],
                    [
                        'type' => 'TEMPLATE',
                        'key' => 'eventName',
                        'value' => "select_promotion"
                    ],
                    [
                        'type' => 'TEMPLATE',
                        'key' => 'upToIncludeDropdown',
                        'value' => "all"
                    ],
                    [
                        'type' => 'TEMPLATE',
                        'key' => 'measurementId',
                        'value' => '{{' . self::VARIABLE_MEASUREMENT_ID . '}}'
                    ]
                ],
                'monitoringMetadata' => [
                    'type' => "MAP"
                ],
                'consentSettings' => [
                    'consentStatus' => "NOT_SET"
                ]
            ],
            self::TAG_VIEW_PROMOTION => [
                'name' => self::TAG_VIEW_PROMOTION,
                'firingTriggerId' => [
                    $triggers[self::TRIGGER_VIEW_PROMOTION]
                ],
                'tagFiringOption' => 'oncePerEvent',
                'type' => self::TYPE_TAG_SGTMGAAW,
                'parameter' => [
                    [
                        'type' => 'boolean',
                        'key' => 'redactVisitorIp',
                        'value' => "false"
                    ],
                    [
                        'type' => 'TEMPLATE',
                        'key' => 'epToIncludeDropdown',
                        'value' => "all"
                    ],
                    [
                        'type' => 'TEMPLATE',
                        'key' => 'eventName',
                        'value' => "view_promotion"
                    ],
                    [
                        'type' => 'TEMPLATE',
                        'key' => 'upToIncludeDropdown',
                        'value' => "all"
                    ],
                    [
                        'type' => 'TEMPLATE',
                        'key' => 'measurementId',
                        'value' => '{{' . self::VARIABLE_MEASUREMENT_ID . '}}'
                    ]
                ],
                'monitoringMetadata' => [
                    'type' => "MAP"
                ],
                'consentSettings' => [
                    'consentStatus' => "NOT_SET"
                ]
            ],
            self::TAG_LOGIN => [
                'name' => self::TAG_LOGIN,
                'firingTriggerId' => [
                    $triggers[self::TRIGGER_LOGIN]
                ],
                'tagFiringOption' => 'oncePerEvent',
                'type' => self::TYPE_TAG_SGTMGAAW,
                'parameter' => [
                    [
                        'type' => 'boolean',
                        'key' => 'redactVisitorIp',
                        'value' => "false"
                    ],
                    [
                        'type' => 'TEMPLATE',
                        'key' => 'epToIncludeDropdown',
                        'value' => "all"
                    ],
                    [
                        'type' => 'TEMPLATE',
                        'key' => 'eventName',
                        'value' => "login"
                    ],
                    [
                        'type' => 'TEMPLATE',
                        'key' => 'upToIncludeDropdown',
                        'value' => "all"
                    ],
                    [
                        'type' => 'TEMPLATE',
                        'key' => 'measurementId',
                        'value' => '{{' . self::VARIABLE_MEASUREMENT_ID . '}}'
                    ]
                ],
                'monitoringMetadata' => [
                    'type' => "MAP"
                ],
                'consentSettings' => [
                    'consentStatus' => "NOT_SET"
                ]
            ],
            self::TAG_SELECT_ITEM => [
                'name' => self::TAG_SELECT_ITEM,
                'firingTriggerId' => [
                    $triggers[self::TRIGGER_SELECT_ITEM]
                ],
                'tagFiringOption' => 'oncePerEvent',
                'type' => self::TYPE_TAG_SGTMGAAW,
                'parameter' => [
                    [
                        'type' => 'boolean',
                        'key' => 'redactVisitorIp',
                        'value' => "false"
                    ],
                    [
                        'type' => 'TEMPLATE',
                        'key' => 'epToIncludeDropdown',
                        'value' => "all"
                    ],
                    [
                        'type' => 'TEMPLATE',
                        'key' => 'eventName',
                        'value' => "select_item"
                    ],
                    [
                        'type' => 'TEMPLATE',
                        'key' => 'upToIncludeDropdown',
                        'value' => "all"
                    ],
                    [
                        'type' => 'TEMPLATE',
                        'key' => 'measurementId',
                        'value' => '{{' . self::VARIABLE_MEASUREMENT_ID . '}}'
                    ]
                ],
                'monitoringMetadata' => [
                    'type' => "MAP"
                ],
                'consentSettings' => [
                    'consentStatus' => "NOT_SET"
                ]
            ],
            self::TAG_SEARCH => [
                'name' => self::TAG_SEARCH,
                'firingTriggerId' => [
                    $triggers[self::TRIGGER_SEARCH]
                ],
                'tagFiringOption' => 'oncePerEvent',
                'type' => self::TYPE_TAG_SGTMGAAW,
                'parameter' => [
                    [
                        'type' => 'boolean',
                        'key' => 'redactVisitorIp',
                        'value' => "false"
                    ],
                    [
                        'type' => 'TEMPLATE',
                        'key' => 'epToIncludeDropdown',
                        'value' => "all"
                    ],
                    [
                        'type' => 'TEMPLATE',
                        'key' => 'eventName',
                        'value' => "search"
                    ],
                    [
                        'type' => 'TEMPLATE',
                        'key' => 'upToIncludeDropdown',
                        'value' => "all"
                    ],
                    [
                        'type' => 'TEMPLATE',
                        'key' => 'measurementId',
                        'value' => '{{' . self::VARIABLE_MEASUREMENT_ID . '}}'
                    ]
                ],
                'monitoringMetadata' => [
                    'type' => "MAP"
                ],
                'consentSettings' => [
                    'consentStatus' => "NOT_SET"
                ]
            ],
            self::TAG_SIGNUP => [
                'name' => self::TAG_SIGNUP,
                'firingTriggerId' => [
                    $triggers[self::TRIGGER_SIGNUP]
                ],
                'tagFiringOption' => 'oncePerEvent',
                'type' => self::TYPE_TAG_SGTMGAAW,
                'parameter' => [
                    [
                        'type' => 'boolean',
                        'key' => 'redactVisitorIp',
                        'value' => "false"
                    ],
                    [
                        'type' => 'TEMPLATE',
                        'key' => 'epToIncludeDropdown',
                        'value' => "all"
                    ],
                    [
                        'type' => 'TEMPLATE',
                        'key' => 'eventName',
                        'value' => "sign_up"
                    ],
                    [
                        'type' => 'TEMPLATE',
                        'key' => 'upToIncludeDropdown',
                        'value' => "all"
                    ],
                    [
                        'type' => 'TEMPLATE',
                        'key' => 'measurementId',
                        'value' => '{{' . self::VARIABLE_MEASUREMENT_ID . '}}'
                    ]
                ],
                'monitoringMetadata' => [
                    'type' => "MAP"
                ],
                'consentSettings' => [
                    'consentStatus' => "NOT_SET"
                ]
            ],
            self::TAG_VIEW_CART => [
                'name' => self::TAG_VIEW_CART,
                'firingTriggerId' => [
                    $triggers[self::TRIGGER_VIEW_CART]
                ],
                'tagFiringOption' => 'oncePerEvent',
                'type' => self::TYPE_TAG_SGTMGAAW,
                'parameter' => [
                    [
                        'type' => 'boolean',
                        'key' => 'redactVisitorIp',
                        'value' => "false"
                    ],
                    [
                        'type' => 'TEMPLATE',
                        'key' => 'epToIncludeDropdown',
                        'value' => "all"
                    ],
                    [
                        'type' => 'TEMPLATE',
                        'key' => 'eventName',
                        'value' => "view_cart"
                    ],
                    [
                        'type' => 'TEMPLATE',
                        'key' => 'upToIncludeDropdown',
                        'value' => "all"
                    ],
                    [
                        'type' => 'TEMPLATE',
                        'key' => 'measurementId',
                        'value' => '{{' . self::VARIABLE_MEASUREMENT_ID . '}}'
                    ]
                ],
                'monitoringMetadata' => [
                    'type' => "MAP"
                ],
                'consentSettings' => [
                    'consentStatus' => "NOT_SET"
                ]
            ],
        ];

        return $tags;
    }

    public function getClientsList($publicId)
    {
        return $this->_getClients($publicId);
    }

    /**
     * @param string $measurementId
     * @return array
     */
    public function getVariablesList($measurementId)
    {
        return $this->_getVariables($measurementId);
    }

    /**
     * @return array
     */
    public function getTriggersList()
    {
        return $this->_getTriggers();
    }

    /**
     * @param array $triggersMapping
     * @param string $measurementId
     * @return array
     */
    public function getTagsList($triggersMapping, $measurementId)
    {
        return $this->_getTags($triggersMapping, $measurementId);
    }
}
