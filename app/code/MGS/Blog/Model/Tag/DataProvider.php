<?php

namespace MGS\Blog\Model\Tag;

use Magento\Framework\App\Request\DataPersistorInterface;
use MGS\Blog\Model\Resource\Tag\CollectionFactory;


class DataProvider extends \Magento\Ui\DataProvider\ModifierPoolDataProvider {

    protected $collection;

    protected $loadedData;

    protected $store;

    protected $resource;

    protected $dataPersistor;

    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $tagFactory,
        DataPersistorInterface $dataPersistor,
        \Magento\Framework\App\Request\Http $request,
		array $meta = [],
        array $data = []
    )
    {
        $this->collection = $tagFactory->create();
        $this->dataPersistor = $dataPersistor;
        $this->store= $request;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }
        $items = $this->collection->getItems();
        foreach ($items as $tag) {
            $tag->setStoreId($this->store->getParam('store'));
            $data = $tag->getData();
            $convert = $this->convertArray($data);
            $this->loadedData[$tag->getId()] = $convert;
        }
        return $this->loadedData;
    }

    public function convertArray($tag) {

        foreach ($tag as $key => $value) {
            if ($key == 'meta_title' || $key == 'meta_description'){
                $data['meta'][$key]=$value;
            } else {
                $data['general'][$key]= $value;
            }
        }
        return $data;
    }

    /* public function getMeta() {
        $meta = parent::getMeta();
        $store =  $this->store->getParam('store');
        $post_id = $this->store->getParam('post_id');
        $ob = ['title','status','content','short_content','tags','meta_keywords','meta_description'];
        $post = $this->getFieldUpdate($post_id,$store);
        if ($store) {
            foreach($post as $key => $value) {
                $id = array_search($key, $ob);
                unset($ob[$id]);
                if($key == 'meta_keywords' || $key == 'meta_description') {
                    $meta['meta']['children'][$key]['arguments']['data']['config']['service']['template'] = 'ui/form/element/helper/service';
                    $meta['meta']['children'][$key]['arguments']['data']['config']['visiable'] = 0;
                }
                else {
                    $meta['general']['children'][$key]['arguments']['data']['config']['service']['template'] = 'ui/form/element/helper/service';
                    $meta['general']['children'][$key]['arguments']['data']['config']['visiable'] = 0;
                }
            }
            foreach ($ob as $key => $value) {
                if($value == 'meta_keywords' || $value == 'meta_description') {
                    $meta['meta']['children'][$value]['arguments']['data']['config']['service']['template'] = 'ui/form/element/helper/service';
                    $meta['meta']['children'][$value]['arguments']['data']['config']['disabled'] = 1;
                }
                else {
                    $meta['general']['children'][$value]['arguments']['data']['config']['service']['template'] = 'ui/form/element/helper/service';
                    $meta['general']['children'][$value]['arguments']['data']['config']['disabled'] = 1;
                }
            }
        }
        return $meta;
    }

    public function getFieldUpdate($category_id,$store) {
        $connection = $this->resource->getConnection();
        $select = $connection->select()->from(
            $this->resource->getTable('mgs_blog_post_update'),
            'field',
        )->where(
            'post_id = ?',
            (int)$category_id
        )->where(
                'scope_id = ?',
                $store
        );
        return $connection->fetchAssoc($select);
    } */
}
