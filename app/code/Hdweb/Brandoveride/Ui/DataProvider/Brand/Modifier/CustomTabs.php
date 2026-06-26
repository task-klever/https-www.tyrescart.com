<?php
namespace Hdweb\Brandoveride\Ui\DataProvider\Brand\Modifier;

use Magento\Ui\DataProvider\Modifier\ModifierInterface;

class CustomTabs implements ModifierInterface
{
    public function modifyMeta(array $meta)
    {
        return $meta;
    }

    public function modifyData(array $data)
    {
        foreach ($data as $id => $brand) {
            if (isset($brand['topbanner_image'])) {
                $data[$id]['topbanner_image'] = [
                    [
                        'name' => basename($brand['topbanner_image']),
                        'url' => $brand['topbanner_image'] ? '/pub/media/'.$brand['topbanner_image'] : ''
                    ]
                ];
            }

            if (isset($brand['bottombanner_image'])) {
                $data[$id]['bottombanner_image'] = [
                    [
                        'name' => basename($brand['bottombanner_image']),
                        'url' => $brand['bottombanner_image'] ? '/pub/media/'.$brand['bottombanner_image'] : ''
                    ]
                ];
            }
        }
        return $data;
    }
}
