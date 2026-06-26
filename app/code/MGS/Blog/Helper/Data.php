<?php

namespace MGS\Blog\Helper;

use Magento\Framework\UrlInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
	protected $_storeManager;
	
	protected $_date;
	
	protected $_url;
	
	protected $_filesystem;
	
	protected $_request;
	
	protected $_acceptToUsePanel = false;
	
	protected $_useBuilder = false;
	
	protected $_customer;
	
	/**
	 * @var \Magento\Framework\Xml\Parser
	 */
	private $_parser;
	
	/**
     * Asset service
     *
     * @var \Magento\Framework\View\Asset\Repository
     */
    protected $_assetRepo;
	
    protected $filterManager;
	
	/**
     * Block factory
     *
     * @var \Magento\Cms\Model\BlockFactory
     */
    protected $_blockFactory;
	/**
     * Page factory
     *
     * @var \Magento\Cms\Model\PageFactory
     */
    protected $_pageFactory;
	
	protected $_file;
	
	/**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;
	
    protected $_fullActionName;
	
    protected $_currentCategory;
	
    protected $_currentProduct;
	
    protected $_category;
	
    protected $scopeConfig;
	
	protected $_ioFile;
	
	protected $_moduleManager;
	
	protected $_registry;

	public function __construct(
		\Magento\Store\Model\StoreManagerInterface $storeManager,
		\Magento\Framework\Stdlib\DateTime\DateTime $date,
		\Magento\Framework\ObjectManagerInterface $objectManager,
		\Magento\Framework\Url $url,
		\Magento\Framework\Filesystem $filesystem,
		\Magento\Framework\App\Request\Http $request,
		\Magento\Framework\View\Element\Context $context,
		\Magento\Cms\Model\BlockFactory $blockFactory,
		\Magento\Catalog\Model\Category $category,
		\Magento\Cms\Model\PageFactory $pageFactory,
		\Magento\Framework\Filesystem\Driver\File $file,
		\Magento\Framework\Filesystem\Io\File $ioFile,
		\Magento\Framework\Xml\Parser $parser,
		\Magento\Framework\Module\Manager $moduleManager,
		\Magento\Framework\Registry $registry
	) {
		$this->scopeConfig = $context->getScopeConfig();
		$this->_storeManager = $storeManager;
		$this->_date = $date;
		$this->_url = $url;
		$this->_filesystem = $filesystem;
		$this->_objectManager = $objectManager;
		$this->_category = $category;
		$this->_request = $request;
		$this->filterManager = $context->getFilterManager();
		$this->_assetRepo = $context->getAssetRepository();
		$this->_blockFactory = $blockFactory;
		$this->_pageFactory = $pageFactory;
		$this->_file = $file;
		$this->_ioFile = $ioFile;
		$this->_moduleManager = $moduleManager;
		$this->_parser = $parser;
		$this->_registry = $registry;
		$this->_fullActionName = $this->_request->getFullActionName();
		
		if($this->_fullActionName == 'catalog_category_view'){
			$this->_currentCategory = $this->getCurrentCategory();
		}
		
		if($this->_fullActionName == 'catalog_product_view'){
			$this->_currentProduct = $this->getCurrentProduct();
		}
	}
	/* Get system store config */
	public function getStoreConfig($node, $storeId = NULL){
		if($storeId != NULL){
			return $this->scopeConfig->getValue($node, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
		}
		return $this->scopeConfig->getValue($node, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->getStore()->getId());
	}

	public function getStore(){
		return $this->_storeManager->getStore();
	}
    public function getConfig($key, $store = null)
    {
		return $this->getStoreConfig('blog/' . $key);
	}

    public function getBaseMediaUrl()
    {
        return $this->_storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);
    }

    public function getRoute()
    {
        $route = $this->getConfig('general_settings/route');
        if ($this->getConfig('general_settings/route') == '') {
            $route = 'blog';
        };
        return $this->_storeManager->getStore()->getBaseUrl() . $route;
    }

    public function getTagUrl($tag)
    {
        $route = $this->getConfig('general_settings/route');
        if ($this->getConfig('general_settings/route') == '') {
            $route = 'blog';
        }
		/* strat extra added */
		$parts = explode(' ', $tag); // Split the string by spaces
		$lowercaseParts = array_map('strtolower', $parts); // Convert each part to lowercase
		$tag = implode('-', $lowercaseParts); // Join the parts with hyphens
		/* end extra added */
        return $this->_storeManager->getStore()->getBaseUrl() . $route . '/tag/' . urlencode($tag);
    }

    public function convertSlashes($tag, $direction = 'back')
    {
        if ($direction == 'forward') {
            $tag = preg_replace("#/#is", "&#47;", $tag);
            $tag = preg_replace("#\\\#is", "&#92;", $tag);
            return $tag;
        }
        $tag = str_replace("&#47;", "/", $tag);
        $tag = str_replace("&#92;", "\\", $tag);
        return $tag;
    }

    public function checkLoggedIn()
    {
        return $this->_objectManager->get('Magento\Customer\Model\Session')->isLoggedIn();
    }

    public function getThumbnailPost($post)
    {
		$html = "";
		if($post->getVideoThumbId() != ""){
			if($post->getVideoThumbType() == "youtube"){
				$video_url = 'https://www.youtube.com/embed/'.$post->getVideoThumbId();
			}else {
				$video_url = 'https://player.vimeo.com/video/'.$post->getVideoThumbId();
			}
			$html .= '<div class="video-responsive">';
			$html .= '<iframe width="1024" height="768" src="'.$video_url.'" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>';
			$html .= '</div>';
		}else {
			$fileName=  $post->getThumbnail();
			$temp =  strpos($fileName,'mgs_blog');
			if($temp !== false)
				$fileName = substr($fileName,$temp + 8);
			$image = [];
			if($post->getImageUrl() == ""){
				$html = "";
			}else {
				$html .= '<img class="img-responsive" alt="'.$post->getTitle().'" src="'.$this->convertUrl($post->getImageUrl()).'mgs_blog/'.$fileName.'">';
			}
		}
        return $html;
    }

	public function getPostUrl($post) {
		$store = $this->_storeManager->getStore()->getCode();

		if($store){
			$url = $post->getPostUrlWithNoCategory() . '?___store=' . $store;
		}else{
			$url = $post->getPostUrlWithNoCategory();
		}

		return $url;
	}

    public function getImagePost($post)
    {
		$html = "";
		if($post->getImageType() == "video"){
			if($post->getVideoBigId() != ""){
				if($post->getVideoBigType() == "youtube"){
					$video_url = 'https://www.youtube.com/embed/'.$post->getVideoBigId();
				}else {
					$video_url = 'https://player.vimeo.com/video/'.$post->getVideoBigId();
				}
				$html .= '<div class="video-responsive">';
				$html .= '<iframe width="1024" height="768" src="'.$video_url.'" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>';
				$html .= '</div>';
			}
		}else {
			$fileName=  $post->getImage();
			$temp =  strpos($fileName,'mgs_blog');
			if($temp !== false)
				$fileName = substr($fileName,$temp + 8);
			$image = [];
			if($post->getImageUrl() == ""){
				$html = "";
			}else {
				$imgSrc = $this->convertUrl($post->getImageUrl()).'mgs_blog/'.$fileName;
				$imgAlt = $post->getTitle();
				$html .= '<div class="relative">'
					. '<div class="img_wrp w-full h-0 z-0 bg-gray-200 pb-[33%] relative rounded-[10px] overflow-hidden">'
					. '<figure class="absolute inset-0">'
					. '<picture>'
					. '<source srcset="'.$imgSrc.'" loading="lazy" type="image/jpg">'
					. '<img class="w-full h-full object-cover object-top overflow-hidden" src="'.$imgSrc.'" alt="'.$imgAlt.'" loading="lazy">'
					. '</picture>'
					. '</figure>'
					. '</div>'
					. '</div>';
			}
		}
        return $html;
    }

	public function getImageUrlPost($post)
	{
		$fileName=  $post->getImage();
		$temp =  strpos($fileName,'mgs_blog');
		if($temp !== false)
			$fileName = substr($fileName,$temp + 8);
		return $this->convertUrl($post->getImageUrl()).'mgs_blog/'.$fileName;
	}

	private function convertUrl($name) {
        $temp = strpos($name,'media');
        $name = substr($name,0,$temp + 6);
        return $name;
    }

	public function getThumbnailImgVideoPost($post)
    {
		if($post->getThumbType() == "video"){
			if($post->getVideoThumbId() != ""){
				if($post->getVideoThumbType() == "youtube"){
					return 'http://img.youtube.com/vi/'.$post->getVideoThumbId().'/hqdefault.jpg';
				}else {
					$info = 'thumbnail_medium';
					$ch = curl_init();
					curl_setopt($ch, CURLOPT_URL, 'vimeo.com/api/v2/video/'.$post->getVideoThumbId().'.php');
					curl_setopt($ch, CURLOPT_HEADER, 0);
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
					curl_setopt($ch, CURLOPT_TIMEOUT, 10);
					$output = unserialize(curl_exec($ch));
					$output = $output[0][$info];
					curl_close($ch);
					return $output;
				}
			}

		}
		return;
    }

    public function convertPerRowtoCol($perRow){
		$result = '';
		switch ($perRow) {
            case 1:
                $result = 12;
                break;
            case 2:
                $result = 6;
                break;
            case 3:
                $result = 4;
                break;
            case 4:
                $result = 3;
                break;
            case 5:
                $result = 'custom-5';
                break;
            case 6:
                $result = 2;
				break;
			case 7:
                $result = 'custom-7';
				break;
			case 8:
                $result = 'custom-8';
                break;
        }
		
		return $result;
	}

	public function convertColClass($col, $type){
		if(($type=='row') && ($col=='custom-5' || $col=='custom-7' || $col=='custom-8')){
			return 'row-'.$col;
		}
		if($type=='col'){
			if(($col=='custom-5' || $col=='custom-7' || $col=='custom-8')){
				return 'col-md-'.$col. ' col-sm-3 col-xs-6';
			}else{
				$class = 'col-lg-'.$col.' col-md-'.$col;
				if($col==12){
					$class .= ' col-sm-12 col-xs-12';
				}
				if($col==6){
					$class .= ' col-sm-6 col-xs-6';
				}
				if(($col==4) || ($col==3)){
					$class .= ' col-sm-4 col-xs-6';
				}
				if($col==2){
					$class .= ' col-sm-3 col-xs-6';
				}
				
				return $class;
			}
		}
	}

	public function decodeHtmlTag($content){
		$result = str_replace("&lt;","<",$content);
		$result = str_replace("&gt;",">",$result);
		$result = str_replace('&#34;','"',$result);
		$result = str_replace("&#39;","'",$result);
		return $result;
	}

	public function getCategoryCollection($categoryId)
	{
		$postModel = $this->_objectManager->get('MGS\Blog\Model\Post');
		$categoryPostCollection = $postModel->getCollection()
            ->addFieldToFilter('status', 1)
            ->addCategoryFilter($categoryId)
            ->addStoreFilter($this->_storeManager->getStore()->getId())
            ->setOrder('created_at', $this->getConfig('general_settings/default_sort'));

		return $categoryPostCollection;
	}

	public function getAllCategoryCollection()
	{
		$postModel = $this->_objectManager->get('MGS\Blog\Model\Post');
		$categoryPostCollection = $postModel->getCollection()
            ->addFieldToFilter('status', 1)
            //->addCategoryFilter($categoryId)
            ->addStoreFilter($this->_storeManager->getStore()->getId())
            // ->setOrder('created_at', $this->getConfig('general_settings/default_sort'));
            ->setOrder('published_at', 'desc');

		// ✅ Add pagination here
		$page     = (int) $this->_request->getParam('p', 1);
    	$pageSize = (int) $this->_request->getParam('limit', 12 ); // default 10
		
		
		$categoryPostCollection->setPageSize($pageSize);
		$categoryPostCollection->setCurPage($page);
			

		return $categoryPostCollection;
	}

	public function getBlogPostsForHomePage()
	{
		$postModel = $this->_objectManager->get('MGS\Blog\Model\Post');
		$homePostCollection = $postModel->getCollection()
            ->addFieldToFilter('status', 1)
            ->addStoreFilter($this->_storeManager->getStore()->getId())
            ->setOrder('published_at', 'DESC');
		$homePostCollection->getSelect()->limit(5);

		return $homePostCollection;
	}

	public function getBreadcrumbTitle()
	{
		$post = $this->_registry->registry('current_blog_post');
		if ($post && $post->getId()) {
			return $post->getTitle();
		}
		return '';
	}

}
