<?php

namespace MGS\Blog\Controller\Adminhtml\Tag;


use Magento\Framework\Exception\LocalizedException;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use MGS\Blog\Model\TagFactory; 
use Magento\Store\Model\Store;
use Magento\Framework\App\Request\DataPersistorInterface;

class Save extends Action
{
    protected $tag;

    protected $tagFactory;

    protected $store;

    protected $dataPersistor;

    public function __construct(
        Context $context,
        \MGS\Blog\Model\Resource\Tag $tag,
        TagFactory $tagFactory,
        Store $store,
        DataPersistorInterface $dataPersistor
    )
    {
        parent::__construct($context);
        $this->tag = $tag; 
        $this->store = $store;
        $this->tagFactory = $tagFactory->create();
        $this->dataPersistor = $dataPersistor;

    }

    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $data = $this->getRequest()->getPostValue();
        if ($data) {
            try {
                if (isset($data['general']['tag_id'])) {
                    $id = $data['general']['tag_id'];
                } else {
                    $id = null;
                }
                
                $data['tag'] = $data['general']['tag'];
                //$data['tag_name'] = $data['general']['tag_name'];
                $data['short_description'] = $data['general']['short_description'];
                $data['long_description'] = $data['general']['long_description'];
                $data['status'] = $data['general']['status'];
                $data['meta_title'] = $data['meta']['meta_title'];
                $data['meta_description'] = $data['meta']['meta_description'];
                $data['store_id'] = $data['general']['store_id'];

                if (!$data['store_id']) {
                    $model = $this->tagFactory->load($id);
                   
                    if (!$id) {
                        $model->setData($data);
                        $model->save();
                    } else {
                        $model->addData($data);
                        $model->save();
                    }

                    $this->messageManager->addSuccess(__('You saved the tag.'));
                    $this->dataPersistor->set('tag', $data);
                    return $resultRedirect->setPath('*/*/edit', ['tag_id' => $model->getId()] );
                }
                else {
                    echo '<pre>'; print_r("Error Here");die((__FILE__).'-->'.(__FUNCTION__).'--Line('. (__LINE__).')');
                }
                $this->dataPersistor->set('tag', $data);
                return $resultRedirect->setPath('*/*/edit', ['tag_id' => $id,'store'=> $data['store_id']] );
            }
            catch (LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
                $id = (int)$this->getRequest()->getParam('tag_id');
                if (!empty($id)) {
                    $this->_redirect('blog/tag/edit', ['tag_id' => $id]);
                } else {
                    $this->_redirect('blog/tag/new');
                }
                return;
            } catch (\Exception $e) {
                $this->messageManager->addError(
                    __('Something went wrong while saving the tag data. Please review the error log.')
                );
                $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
                $this->_redirect('blog/tag/edit', ['tag_id' => $this->getRequest()->getParam('tag_id')]);
                return;
            }
            return $resultRedirect->setPath('*/*/');
        }
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('MGS_Blog::save_tag');
    }
}
