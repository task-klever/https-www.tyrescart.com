<?php

namespace BitExpert\ForceCustomerLogin\Test\DataProviders;

class WhitelistDataProvider
{
    public static function get(): array
    {
        return [
            'default' => [
                [
                    '/url1' => [
                        'label'    => 'label 1',
                        'strategy' => '',
                    ],
                    '/url2' => [
                        'label'    => 'label 2',
                        'strategy' => 'regex-all',
                    ],
                ],
            ],
        ];
     }
}
