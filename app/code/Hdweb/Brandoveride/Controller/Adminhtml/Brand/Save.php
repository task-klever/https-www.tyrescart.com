<?php
namespace Hdweb\Brandoveride\Controller\Adminhtml\Brand;

use MGS\Brand\Controller\Adminhtml\Brand\Save as BrandSave;

class Save extends BrandSave
{
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();

        if ($data) {
            $model = $this->_objectManager->create(\MGS\Brand\Model\Brand::class);
            $id = $this->getRequest()->getParam('brand_id');

            if ($id) {
                $model->load($id);
            }

            // ✅ Handle your custom fields
            // if (isset($data['tab1_title'])) {
            //     $model->setTab1Title($data['tab1_title']);
            // }

            // ✅ Handle image field (same code I shared earlier)
              /* custom image code start */
               /* if (isset($_FILES['topbanner_image']['name']) && $_FILES['topbanner_image']['name'] != '') {

                    
                    $uploader = $this->_objectManager->create(
                        'Magento\MediaStorage\Model\File\Uploader',
                        ['fileId' => 'topbanner_image']
                    );
                    $uploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png', 'svg']);
                    $uploader->setAllowRenameFiles(true);
                    $uploader->setAllowCreateFolders(true);
                    $uploader->setFilesDispersion(true);
                    $ext = pathinfo($_FILES['topbanner_image']['name'], PATHINFO_EXTENSION);
                    if ($uploader->checkAllowedExtension($ext)) {
                        $path = $this->_objectManager->get('Magento\Framework\Filesystem')->getDirectoryRead(DirectoryList::MEDIA)
                            ->getAbsolutePath('mgs_brand/');
                        $uploader->save($path);
                        $fileName = $uploader->getUploadedFileName();
                        if ($fileName) {
                            $data['brand']['topbanner_image'] = 'mgs_brand' . $fileName;
                        }
                    } else {
                        $this->messageManager->addError(__('Disallowed file type.'));
                        return $this->redirectToEdit($model, $data);
                    }
                } else {
                    if (isset($data['topbanner_image']['delete']) && $data['topbanner_image']['delete'] == 1) {
                        $data['brand']['topbanner_image'] = '';
                    } else {
                        unset($data['topbanner_image']);
                    }
                }
                
                if (isset($_FILES['bottombanner_image']['name']) && $_FILES['bottombanner_image']['name'] != '') {
                    $uploader = $this->_objectManager->create(
                        'Magento\MediaStorage\Model\File\Uploader',
                        ['fileId' => 'bottombanner_image']
                    );
                    $uploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png', 'svg']);
                    $uploader->setAllowRenameFiles(true);
                    $uploader->setAllowCreateFolders(true);
                    $uploader->setFilesDispersion(true);
                    $ext = pathinfo($_FILES['bottombanner_image']['name'], PATHINFO_EXTENSION);
                    if ($uploader->checkAllowedExtension($ext)) {
                        $path = $this->_objectManager->get('Magento\Framework\Filesystem')->getDirectoryRead(DirectoryList::MEDIA)
                            ->getAbsolutePath('mgs_brand/');
                        $uploader->save($path);
                        $fileName = $uploader->getUploadedFileName();
                        if ($fileName) {
                            $data['brand']['bottombanner_image'] = 'mgs_brand' . $fileName;
                        }
                    } else {
                        $this->messageManager->addError(__('Disallowed file type.'));
                        return $this->redirectToEdit($model, $data);
                    }
                } else {
                    if (isset($data['bottombanner_image']['delete']) && $data['bottombanner_image']['delete'] == 1) {
                        $data['brand']['bottombanner_image'] = '';
                    } else {
                        unset($data['bottombanner_image']);
                    }
                }*/

            $model->setData($data);

            try {
                $model->save();
                $this->messageManager->addSuccessMessage(__('Brand saved successfully.'));
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            }
        }

        return $this->_redirect('*/*/index');
    }
}
