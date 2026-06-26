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
 * @package   mirasvit/module-sorting
 * @version   1.3.15
 * @copyright Copyright (C) 2023 Mirasvit (https://mirasvit.com/)
 */


declare(strict_types=1);

namespace Mirasvit\Sorting\Controller\Adminhtml\RankingFactor;

use Mirasvit\Sorting\Api\Data\RankingFactorInterface;
use Mirasvit\Sorting\Controller\Adminhtml\RankingFactorAbstract;
use Mirasvit\Sorting\Factor\FormulaFactor;

class Save extends RankingFactorAbstract
{
    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();

        $id = (int)$this->getRequest()->getParam(RankingFactorInterface::ID);

        $model = $this->initModel();

        $data = $this->getRequest()->getParams();

        $data = $this->filter($data, $model);

        if ($data) {
            if (!$model->getId() && $id) {
                $this->messageManager->addErrorMessage((string)__('This factor no longer exists.'));

                return $resultRedirect->setPath('*/*/');
            }

            $model->setName((string)$data[RankingFactorInterface::NAME])
                ->setIsActive((bool)$data[RankingFactorInterface::IS_ACTIVE])
                ->setType((string)$data[RankingFactorInterface::TYPE])
                ->setIsGlobal((bool)$data[RankingFactorInterface::IS_GLOBAL])
                ->setWeight((int)$data[RankingFactorInterface::WEIGHT])
                ->setConfig($data[RankingFactorInterface::CONFIG]);

            if (
                ($model->getType() == 'alphanumeric' || $model->getType() == 'attribute')
                && !isset($model->getConfig()['attribute'])
            ) {
                $model->setIsActive(false);
            }

            if ($model->getType() === FormulaFactor::FORMULA) {
                $formula = $model->getConfigData(FormulaFactor::FORMULA);

                if ($formula) {
                    /** @var FormulaFactor $factor */
                    $factor = $this->rankingFactorRepository->getFactor(FormulaFactor::FORMULA);

                    try {
                        $factor->validateFormula($formula, $factor->parseVariables($formula));
                    } catch (\Exception $e) {
                        $this->messageManager->addErrorMessage($e->getMessage());

                        return $resultRedirect->setPath('*/*/edit', [RankingFactorInterface::ID => $model->getId()]);
                    }
                }
            }

            try {
                $this->rankingFactorRepository->save($model);

                $this->messageManager->addSuccessMessage((string)__('You saved the factor.'));

                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', [RankingFactorInterface::ID => $model->getId()]);
                }

                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());

                return $resultRedirect->setPath('*/*/edit', [RankingFactorInterface::ID => $model->getId()]);
            }
        } else {
            $resultRedirect->setPath('*/*/');
            $this->messageManager->addErrorMessage((string)__('No data to save.'));

            return $resultRedirect;
        }
    }

    /**
     * @param array                  $data
     * @param RankingFactorInterface $rankingFactor
     *
     * @return array
     */
    private function filter(array $data, RankingFactorInterface $rankingFactor)
    {
        if (!isset($data[RankingFactorInterface::CONFIG])) {
            $data[RankingFactorInterface::CONFIG] = [];
        }

        if (isset($data['rule'])) {
            $data[RankingFactorInterface::CONFIG]['rule'] = $data['rule'];
        }

        if (!isset($data[RankingFactorInterface::WEIGHT])) {
            $data[RankingFactorInterface::WEIGHT] = 0;
        }

        return $data;
    }
}
