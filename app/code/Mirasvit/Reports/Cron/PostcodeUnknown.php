<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-reports
 * @version   1.6.0
 * @copyright Copyright (C) 2024 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Reports\Cron;

use Magento\Sales\Model\ResourceModel\Order\Address\CollectionFactory as AddressCollectionFactory;
use Mirasvit\Reports\Model\PostcodeFactory;

class PostcodeUnknown
{
    /**
     * @var PostcodeFactory
     */
    protected $postcodeFactory;

    /**
     * @var AddressCollectionFactory
     */
    protected $addressCollectionFactory;

    /**
     * @param PostcodeFactory          $postcodeFactory
     * @param AddressCollectionFactory $addressCollectionFactory
     */
    public function __construct(
        PostcodeFactory $postcodeFactory,
        AddressCollectionFactory $addressCollectionFactory
    ) {
        $this->postcodeFactory          = $postcodeFactory;
        $this->addressCollectionFactory = $addressCollectionFactory;
    }

    /**
     * @param bool $verbose
     *
     * @return void
     */
    public function execute($verbose = false)
    {
        $collection = $this->addressCollectionFactory->create();

        $collection->addFieldToSelect(['country_id', 'postcode']);

        $resource = $collection->getResource();

        $collection->getSelect()->joinLeft(
            ['postcode' => $resource->getTable('mst_reports_postcode')],
            'postcode.postcode = REPLACE(REPLACE(main_table.postcode, " ", ""), "-","")
                AND postcode.country_id = main_table.country_id',
            []
        )->where(
            'postcode_id IS NULL or postcode.updated=0'
        )->where(
            'main_table.postcode IS NOT NULL'
        );

        $collection->setPageSize(100);

        $pages = $collection->getLastPageNumber();
        $page  = 1;

        $maxPages = 1000; // max 100000 records per cron

        do {
            $collection->setCurPage($page);
            $collection->load();

            foreach ($collection as $row) {
                $countryId = $row->getCountryId();
                $postcode  = $row->getPostcode();

                if (trim((string)$postcode) == '' || trim((string)$countryId) == '') {
                    continue;
                }

                $model = $this->postcodeFactory->create();
                if (!$model->loadByCode($countryId, $postcode)) {
                    $model->setCountryId($countryId)
                        ->setPostcode($postcode)
                        ->save();
                }
            }

            ++$page;
            $collection->clear();

            if ($page > $maxPages) {
                break;
            }

        } while ($page <= $pages);
    }
}
