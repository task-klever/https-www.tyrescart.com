<?php
namespace Hdweb\Specialoffers\Controller\Adminhtml\Entity\Image;

use Magento\Framework\Controller\ResultFactory;

/**
 * Class Upload
 */
class Offerupload extends \Magento\Backend\App\Action
{
    /**
     * Image uploader
     *
     * @var \[Namespace]\[Module]\Model\ImageUploader
     */
    protected $imageofferUploader;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \[Namespace]\[Module]\Model\ImageUploader $imageofferUploader
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Hdweb\Specialoffers\Model\ImageofferUploader $imageofferUploader
    ) {
        parent::__construct($context);
        $this->imageofferUploader = $imageofferUploader;
    }

    /**
     * Check admin permissions for this controller
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Hdweb_Specialoffers::entity');
    }

    /**
     * Upload file controller action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        try {
            $result = $this->imageofferUploader->saveFileToTmpDir('offer_image');

            $result['cookie'] = [
                'name' => $this->_getSession()->getName(),
                'value' => $this->_getSession()->getSessionId(),
                'lifetime' => $this->_getSession()->getCookieLifetime(),
                'path' => $this->_getSession()->getCookiePath(),
                'domain' => $this->_getSession()->getCookieDomain(),
            ];
        } catch (\Exception $e) {
            $result = ['error' => $e->getMessage(), 'errorcode' => $e->getCode()];
        }
        return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setData($result);
    }
}
?>