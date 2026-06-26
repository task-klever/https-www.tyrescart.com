<?php

namespace Klever\BannerSlider\Plugin;

use Mageplaza\BannerSlider\Controller\Adminhtml\Banner\Save as BannerSave;
use Mageplaza\BannerSlider\Helper\Image;
use Mageplaza\BannerSlider\Model\Config\Source\Type;
use Magento\Framework\Filter\TranslitUrl;

class BannerSavePlugin
{
    private Image $imageHelper;
    private TranslitUrl $translitUrl;

    public function __construct(
        Image $imageHelper,
        TranslitUrl $translitUrl
    ) {
        $this->imageHelper = $imageHelper;
        $this->translitUrl = $translitUrl;
    }

    /**
     * Before save: handle mobile image upload and auto-generate url_key
     */
    public function beforeExecute(BannerSave $subject)
    {
        $request = $subject->getRequest();
        $data = $request->getPost('banner');

        if (!$data) {
            return null;
        }

        // Handle mobile image upload
        if (isset($data['type']) && $data['type'] === Type::IMAGE) {
            $this->imageHelper->uploadImage(
                $data,
                'image_mobile',
                Image::TEMPLATE_MEDIA_TYPE_BANNER,
                $data['image_mobile']['value'] ?? ''
            );
        } else {
            $data['image_mobile'] = isset($data['image_mobile']['value']) ? $data['image_mobile']['value'] : '';
        }

        // Auto-generate url_key from name if empty
        $urlKey = $data['url_key'] ?? '';
        if (empty($urlKey) && !empty($data['name'])) {
            $urlKey = $this->generateUrlKey($data['name']);
        } elseif (!empty($urlKey)) {
            $urlKey = $this->generateUrlKey($urlKey);
        }
        $data['url_key'] = $urlKey;

        // Convert date fields to Y-m-d format for database
        foreach (['display_from', 'display_to'] as $dateField) {
            if (!empty($data[$dateField])) {
                try {
                    $date = new \DateTime($data[$dateField]);
                    $data[$dateField] = $date->format('Y-m-d');
                } catch (\Exception $e) {
                    $data[$dateField] = null;
                }
            } else {
                $data[$dateField] = null;
            }
        }

        $request->setPostValue('banner', $data);

        return null;
    }

    private function generateUrlKey(string $text): string
    {
        $urlKey = $this->translitUrl->filter($text);
        return strtolower(trim($urlKey, '-'));
    }
}
