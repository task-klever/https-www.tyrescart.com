<?php

namespace Hdweb\Vehicles\Controller\Adminhtml\Dataimport;

use Magento\Backend\App\Action;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Magento\Framework\Image\AdapterFactory;
use Magento\Store\Model\ScopeInterface;


class Save extends \Magento\Backend\App\Action
{

    protected $fileSystem;

    protected $uploaderFactory;

    protected $request;

    protected $adapterFactory;
    protected $scopeConfig;


    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Filesystem $fileSystem,
        \Magento\MediaStorage\Model\File\UploaderFactory $uploaderFactory,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        AdapterFactory $adapterFactory

    ) {
        parent::__construct($context);
        $this->fileSystem = $fileSystem;
        $this->request = $request;
        $this->scopeConfig = $scopeConfig;
        $this->adapterFactory = $adapterFactory;
        $this->uploaderFactory = $uploaderFactory;
    }

    public function execute()
    {

        if ((isset($_FILES['importdata']['name'])) && ($_FILES['importdata']['name'] != '')) {
            try {
                $uploaderFactory = $this->uploaderFactory->create(['fileId' => 'importdata']);
                $uploaderFactory->setAllowedExtensions(['csv', 'xls']);
                $uploaderFactory->setAllowRenameFiles(true);
                $uploaderFactory->setFilesDispersion(true);

                $mediaDirectory = $this->fileSystem->getDirectoryRead(DirectoryList::MEDIA);
                $destinationPath = $mediaDirectory->getAbsolutePath('hdweb_vehicles_IMPORTDATA');

                $result = $uploaderFactory->save($destinationPath);

                if (!$result) {
                    throw new LocalizedException(
                        __('File cannot be saved to path: $1', $destinationPath)
                    );
                } else {
                    $imagePath = 'hdweb_vehicles_IMPORTDATA' . $result['file'];

                    $mediaDirectory = $this->fileSystem->getDirectoryRead(DirectoryList::MEDIA);

                    $destinationfilePath = $mediaDirectory->getAbsolutePath($imagePath);

                    // Read CSV data
                    $csvFilePath = $destinationPath . '/' . $result['file'];
                    $csvHandle = fopen($csvFilePath, 'r');
                    if ($csvHandle !== false) {
                        // Read and process each row
                        $header = null;
                        /* $count = 1; */
                        while (($data = fgetcsv($csvHandle)) !== false) {
                            /* $count++; */
                            // If it's the header row, save the header names
                            if ($header === null) {
                                $header = $data;
                                continue;
                            }

                            $vehiclesModel = $this->_objectManager->create('Hdweb\Vehicles\Model\Vehicles');

                            if (isset($data[0]) && $data[0] != '') {
                                $vehiclesModel->load($data[0]);
                            }

                            if (isset($data[1]) && $data[1] != '') {
                                $vehiclesModel->setData('store_id', $data[1]);
                            }

                            if (isset($data[2]) && $data[2] != '') {
                                $vehiclesModel->setData('make', $data[2]);
                            }

                            if (isset($data[3]) && $data[3] != '') {
                                $vehiclesModel->setData('model', $data[3]);
                            }

                            if (isset($data[4]) && $data[4] != '') {
                                if ($data[4] == '__EMPTY__VALUE__') {
                                    $vehiclesModel->setData('make_paragraph1', null);
                                } else {
                                    $vehiclesModel->setData('make_paragraph1', $data[4]);
                                }
                            }

                            if (isset($data[5]) && $data[5] != '') {
                                $vehiclesModel->setData('make_paragraph2', $data[5]);
                            }

                            if (isset($data[6]) && $data[6] != '') {
                                $vehiclesModel->setData('model_paragraph1', $data[6]);
                            }

                            if (isset($data[7]) && $data[7] != '') {
                                $vehiclesModel->setData('model_paragraph2', $data[7]);
                            }

                            if (isset($data[8]) && $data[8] != '') {
                                $vehiclesModel->setData('model_paragraph3', $data[8]);
                            }

                            if (isset($data[9]) && $data[9] != '') {
                                $vehiclesModel->setData('meta_title', $data[9]);
                            }

                            if (isset($data[10]) && $data[10] != '') {
                                $vehiclesModel->setData('meta_keywords', $data[10]);
                            }

                            if (isset($data[11]) && $data[11] != '') {
                                $vehiclesModel->setData('meta_description', $data[11]);
                            }

                            if (isset($data[12]) && $data[12] != '') {
                                $vehiclesModel->setData('status', $data[12]);
                            }

                            if (isset($data[13]) && $data[13] != '') {
                                $vehiclesModel->setData('created_by', $data[13]);
                            }

                            if (isset($data[14]) && $data[14] != '') {
                                $vehiclesModel->setData('updated_by', $data[14]);
                            }

                            $vehiclesModel->save();
                        }
                        fclose($csvHandle);
                        //$this->messageManager->addSuccess(__('A total of %1 record(s) have been Added/Updated.', $count));
                        $this->messageManager->addSuccess(__('Records have been Added/Updated.'));
                        $this->_redirect('hdweb_vehicles/items/index');
                    } else {
                        throw new \Exception('Failed to open CSV file for reading.');
                    }
                }
            } catch (\Exception $e) {
                $this->messageManager->addError(__($e->getMessage()));
                $this->_redirect('hdweb_vehicles/dataimport/importdata');
            }
        } else {
            $this->messageManager->addError(__("Please try again."));
            $this->_redirect('hdweb_vehicles/dataimport/importdata');
        }
    }
}
