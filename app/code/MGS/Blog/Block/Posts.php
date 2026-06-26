<?php

namespace MGS\Blog\Block;

use Magento\Customer\Model\Context as CustomerContext;
use MGS\Blog\Model\Resource\Post as PostResource;

class Posts extends \Magento\Framework\View\Element\Template
{
    protected $_coreRegistry = null;
    protected $_blogHelper;
    protected $_post;
    protected $httpContext;
    protected $resource;
    protected $storeManager;
    protected $_collection;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \MGS\Blog\Helper\Data $blogHelper,
        \MGS\Blog\Model\Post $post,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        PostResource $resource,
        array $data = []
    ) {
        $this->resource = $resource;
        $this->storeManager = $storeManager;
        $this->_post = $post;
        $this->_coreRegistry = $registry;
        $this->_blogHelper = $blogHelper;
        $this->httpContext = $httpContext;
        parent::__construct($context, $data);
    }

    public function _construct()
    {
        if (!$this->getConfig('general_settings/enabled')) return;
        parent::_construct();
        $post = $this->_post;

        $postCollection = $post->getCollection()
            ->addFieldToFilter('status', 1)
            ->addStoreFilter($this->_storeManager->getStore()->getId())
            //->setOrder('created_at', $this->getConfig('general_settings/default_sort'));
            ->setOrder('published_at', $this->getConfig('general_settings/default_sort'));
        $this->setCollection($postCollection);
    }

    public function getCacheKeyInfo()
    {
        return [
            'BLOG_POST_LIST',
            $this->_storeManager->getStore()->getId(),
            $this->_design->getDesignTheme()->getId(),
            $this->httpContext->getValue(CustomerContext::CONTEXT_GROUP),
            'template' => $this->getTemplate()
        ];
    }

    protected function _addBreadcrumbs()
    {
        $breadcrumbsBlock = $this->getLayout()->getBlock('breadcrumbs');
        $baseUrl = $this->_storeManager->getStore()->getBaseUrl();
        $pageTitle = $this->_blogHelper->getConfig('general_settings/title');
        $breadcrumbsBlock->addCrumb(
            'home',
            [
                'label' => __('Home'),
                'title' => __('Go to Home Page'),
                'link' => $baseUrl
            ]
        );
        $breadcrumbsBlock->addCrumb(
            'blog',
            [
                'label' => $pageTitle,
                'title' => $pageTitle,
                'link' => ''
            ]
        );
    }

    public function setCollection($collection)
    {
        $this->_collection = $collection;
        return $this->_collection;
    }

    public function getCollection()
    {
        return $this->_collection;
    }
    public function getConfig($key, $default = '')
    {
        $result = $this->_blogHelper->getConfig($key);
        if (!$result) {
            return $default;
        }
        return $result;
    }

    protected function _prepareLayout()
    {
        $post = $this->getCurrentUrl();
        $pageTitle = $this->getConfig('general_settings/meta_keywords');
        $metaTitle = $this->getConfig('general_settings/meta_keywords');
        $metaDescription = $this->getConfig('general_settings/meta_description');
        $this->_addBreadcrumbs();
        $this->pageConfig->addBodyClass('blog-post-list');
        if ($pageTitle) {
            $this->pageConfig->getTitle()->set($pageTitle);
        }
        if ($metaTitle) {
            $this->pageConfig->setMetadata('title', $metaTitle);
        }
        if ($metaDescription) {
            $this->pageConfig->setDescription($metaDescription);
        }
        $collection = $this->getCollection();
        if ($collection) {
            /** @var \Magento\Theme\Block\Html\Pager $pager */
            $pager = $this->getLayout()->createBlock(
                \Magento\Theme\Block\Html\Pager::class,
                'blog.post.list.pager'
            );

             // ✅ Fetch allowed limits from Magento catalog config
                $allowedLimits = $this->_scopeConfig->getValue(
                    'catalog/frontend/grid_per_page_values',
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                );

                // convert CSV string like "12,24,36" into [12 => 12, 24 => 24, 36 => 36]
                $availableLimit = [];
                if ($allowedLimits) {
                    foreach (explode(',', $allowedLimits) as $value) {
                        $value = (int)trim($value);
                        if ($value > 0) {
                            $availableLimit[$value] = $value;
                        }
                    }
                }

                // Set available limits for pager
                $pager->setAvailableLimit($availableLimit ?: [12 => 12, 24 => 24, 36 => 36]);
            $pager->setShowPerPage(true);

            // Attach collection to pager (this applies ?limit & ?p from URL)
            $pager->setCollection($collection);
            $this->setChild('pager', $pager);

            // Load AFTER pager sets correct pageSize & currentPage
            $collection->load();
        }
        $this->pageConfig->addRemotePageAsset(
            $post,
            'canonical',
            ['attributes' => ['rel' => 'canonical']]
        );
        return parent::_prepareLayout();
    }
    public function getPostByStore($store, $post_id)
    {
        $table = $this->resource->getTable('mgs_blog_post_update');
        $connection = $this->resource->getConnection();
        $sql = "SELECT `field`, `value`
                 FROM `$table`
                 WHERE `scope_id`= $store
                 AND `post_id`= $post_id ";
        $post = $connection->fetchAssoc($sql);
        return $post;
    }
    
    public function getPostByCategory($post_id) {
        $table = $this->resource->getTable('mgs_blog_category_post');
        $connection = $this->resource->getConnection();

        $sql = "SELECT `category_id` FROM `$table` WHERE `post_id` = :post_id";
        $bind = ['post_id' => $post_id];

        $post = $connection->fetchAssoc($sql, $bind);

        if (!empty($post)) {
            // Get the first category_id
            $categoryId = key($post);  // or array_keys($post)[0]
            return $categoryId;
        }
        return null;
    }

    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    public function getCurrentUrl()
    {
        $url = $this->_storeManager->getStore()->getCurrentUrl();
        $newUrl = explode("?", $url);
        return $newUrl[0];
    }
}
