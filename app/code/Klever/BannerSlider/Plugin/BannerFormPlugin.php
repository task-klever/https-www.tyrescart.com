<?php

namespace Klever\BannerSlider\Plugin;

use Mageplaza\BannerSlider\Block\Adminhtml\Banner\Edit\Tab\Banner as BannerTab;
use Mageplaza\BannerSlider\Block\Adminhtml\Banner\Edit\Tab\Render\Image as BannerImage;
use Mageplaza\BannerSlider\Helper\Image as HelperImage;
use Magento\Cms\Model\Wysiwyg\Config as WysiwygConfig;
use Magento\Framework\Registry;

class BannerFormPlugin
{
    private WysiwygConfig $wysiwygConfig;
    private Registry $registry;

    public function __construct(
        WysiwygConfig $wysiwygConfig,
        Registry $registry
    ) {
        $this->wysiwygConfig = $wysiwygConfig;
        $this->registry = $registry;
    }

    /**
     * After form is prepared, modify existing fields and add new ones
     */
    public function afterSetForm(BannerTab $subject, $result)
    {
        $form = $subject->getForm();
        if (!$form) {
            return $result;
        }

        $fieldset = $form->getElement('base_fieldset');
        if (!$fieldset) {
            return $result;
        }

        // Get current banner data for setting values on new fields
        $banner = $this->registry->registry('mpbannerslider_banner');
        $bannerData = $banner ? $banner->getData() : [];

        // Rename "Upload Image" to "Upload Image Desktop"
        $imageField = $form->getElement('image');
        if ($imageField) {
            $imageField->setLabel(__('Upload Image Desktop'));
            $imageField->setTitle(__('Upload Image Desktop'));
        }

        // Rename "Url" to "Banner Link"
        $urlField = $form->getElement('url_banner');
        if ($urlField) {
            $urlField->setLabel(__('Banner Link'));
            $urlField->setTitle(__('Banner Link'));
        }

        // Add "Upload Image Mobile" after image field
        $mobileImageField = $fieldset->addField('image_mobile', BannerImage::class, [
            'name' => 'image_mobile',
            'label' => __('Upload Image Mobile'),
            'title' => __('Upload Image Mobile'),
            'path' => HelperImage::TEMPLATE_MEDIA_TYPE_BANNER,
        ], 'image');

        // Set the mobile image value so preview shows
        if (!empty($bannerData['image_mobile'])) {
            $mobileImageField->setValue($bannerData['image_mobile']);
        }

        // Add URL Key field after name
        $urlKeyField = $fieldset->addField('url_key', 'text', [
            'name' => 'url_key',
            'label' => __('Page URL'),
            'title' => __('Page URL'),
            'note' => __('URL-friendly slug for the banner detail page (e.g. "buy3-get1-free"). Auto-generated from name if left empty.'),
        ], 'name');
        if (!empty($bannerData['url_key'])) {
            $urlKeyField->setValue($bannerData['url_key']);
        }

        // Add Page Content fieldset with WYSIWYG editor
        $contentFieldset = $form->addFieldset('page_content_fieldset', [
            'legend' => __('Page Content'),
            'class' => 'fieldset-wide',
            'collapsable' => true,
        ]);

        $pageContentField = $contentFieldset->addField('page_content', 'editor', [
            'name' => 'page_content',
            'label' => __('Page Content'),
            'title' => __('Page Content'),
            'required' => false,
            'note' => __('Rich content displayed on the banner detail page.'),
            'wysiwyg' => true,
            'config' => $this->wysiwygConfig->getConfig([
                'hidden' => false,
                'add_variables' => true,
                'add_widgets' => true,
                'add_directives' => true,
                'add_images' => true,
                'height' => '300px',
                'width' => '100%',
            ]),
        ]);
        if (!empty($bannerData['page_content'])) {
            $pageContentField->setValue($bannerData['page_content']);
        }

        // Add SEO fieldset
        $seoFieldset = $form->addFieldset('seo_fieldset', [
            'legend' => __('Search Engine Optimization'),
            'class' => 'fieldset-wide',
            'collapsable' => true,
        ]);

        $metaTitleField = $seoFieldset->addField('meta_title', 'text', [
            'name' => 'meta_title',
            'label' => __('Meta Title'),
            'title' => __('Meta Title'),
            'note' => __('Page title for search engines. If empty, banner name will be used.'),
        ]);
        if (!empty($bannerData['meta_title'])) {
            $metaTitleField->setValue($bannerData['meta_title']);
        }

        $metaDescField = $seoFieldset->addField('meta_description', 'textarea', [
            'name' => 'meta_description',
            'label' => __('Meta Description'),
            'title' => __('Meta Description'),
            'note' => __('Description for search engines. If empty, a default will be generated.'),
        ]);
        if (!empty($bannerData['meta_description'])) {
            $metaDescField->setValue($bannerData['meta_description']);
        }

        // Add Display Date fields inside main fieldset (calendar JS requires same fieldset)
        $displayFromField = $fieldset->addField('display_from', 'date', [
            'name' => 'display_from',
            'label' => __('Display From'),
            'title' => __('Display From'),
            'date_format' => 'M/d/yyyy',
            'input_format' => \Magento\Framework\Stdlib\DateTime::DATE_INTERNAL_FORMAT,
            'timezone' => false,
        ]);
        if (!empty($bannerData['display_from'])) {
            $displayFromField->setValue($bannerData['display_from']);
        }

        $displayToField = $fieldset->addField('display_to', 'date', [
            'name' => 'display_to',
            'label' => __('Display To'),
            'title' => __('Display To'),
            'date_format' => 'M/d/yyyy',
            'input_format' => \Magento\Framework\Stdlib\DateTime::DATE_INTERNAL_FORMAT,
            'timezone' => false,
        ]);
        if (!empty($bannerData['display_to'])) {
            $displayToField->setValue($bannerData['display_to']);
        }

        return $result;
    }
}
