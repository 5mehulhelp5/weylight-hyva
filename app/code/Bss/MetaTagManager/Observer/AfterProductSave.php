<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_MetaTagManager
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\MetaTagManager\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class AfterProductSave
 * @package Bss\MetaTagManager\Observer
 */
class AfterProductSave implements ObserverInterface
{
    /**
     * @var \Bss\MetaTagManager\Helper\Data
     */
    private $dataHelper;
    /**
     * @var \Bss\MetaTagManager\Model\MetaTemplateFactory
     */
    private $metaTemplateFactory;
    /**
     * @var \Bss\MetaTagManager\Model\RuleFactory
     */
    private $ruleFactory;
    /**
     * @var \Bss\MetaTagManager\Helper\ProcessMetaTemplate
     */
    private $processMetaTemplate;

    /**
     * AfterProductSave constructor.
     * @param \Bss\MetaTagManager\Helper\Data $dataHelper
     * @param \Bss\MetaTagManager\Model\MetaTemplateFactory $metaTemplateFactory
     * @param \Bss\MetaTagManager\Model\RuleFactory $ruleFactory
     * @param \Bss\MetaTagManager\Helper\ProcessMetaTemplate $processMetaTemplate
     */
    public function __construct(
        \Bss\MetaTagManager\Helper\Data $dataHelper,
        \Bss\MetaTagManager\Model\MetaTemplateFactory $metaTemplateFactory,
        \Bss\MetaTagManager\Model\RuleFactory $ruleFactory,
        \Bss\MetaTagManager\Helper\ProcessMetaTemplate $processMetaTemplate
    ) {
        $this->dataHelper = $dataHelper;
        $this->processMetaTemplate = $processMetaTemplate;
        $this->ruleFactory = $ruleFactory;
        $this->metaTemplateFactory = $metaTemplateFactory;
    }

    /**
     * @param EventObserver $observer
     * @return $this|void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(EventObserver $observer)
    {
        if ($this->dataHelper->isActiveBssMetaTag()){
            $productObject = $observer->getProduct();
            if (!$productObject->getId()) {
                return $this;
            }
            $collection = $this->metaTemplateFactory->create()
                ->getCollection()
                ->addFieldToFilter('meta_type', 'product')
                ->addFieldToFilter('status', '1');
            if ($collection) {
                $productCheckObject = [];
                foreach ($collection as $metaObject) {
                    $metaData = $metaObject->getData();
                    //HandleData
                    $priority = $metaData['priority'];
                    //Set Data
                    $modelRule = $this->ruleFactory->create();
                    $modelRule->setMetaData($metaData);
                    $statusValidate = $modelRule->validateProductConditions($productObject);
                    if ($statusValidate) {
                        $dataToAdd = [
                            'meta_object' => $metaObject,
                            'priority' => $priority,
                            'store' => $metaData['store']
                        ];
                        $productCheckObject[] = $dataToAdd;
                    }

                }
                //Check in ProductCheckObject
                if (!empty($productCheckObject)) {
                    if ($productObject->isObjectNew()) { // Create product
                        $this->saveTemplateAsStore($productObject, $productCheckObject);
                    }
                }
            }
            return $this;
        }
    }

    /**
     * Save template to product when this created.
     *
     * @param array $productObject
     * @param array $productCheckObject
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function saveTemplateAsStore($productObject, $productCheckObject)
    {
        $maxPriority = [];
        $metaObjectFinal = [];

        foreach ($productCheckObject as $item) {
            $priorityItem = $item['priority'];
            $max = $maxPriority[$item['store']] ?? 0;

            if ((int)$priorityItem >= $max) {
                $maxPriority[$item['store']] = (int)$priorityItem;
                $metaObjectFinal[$item['store']] = $item['meta_object'];
            }
        }

        /* All store view will take the template with the highest priority */
        foreach ($maxPriority as $storeId => $val) {
            if ($val == max($maxPriority)) {
                $metaObjectFinal[\Magento\Store\Model\Store::DEFAULT_STORE_ID] = $metaObjectFinal[$storeId];
                break;
            }
        }

        /* Save template with store view */
        foreach ($metaObjectFinal as $storeId => $metaObject) {
            $productObject->setStoreId($storeId);
            $this->generateProduct($productObject, $metaObject);
        }
    }

    /**
     * @param object $product
     * @param object $metaTemplate
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Exception
     */
    public function generateProduct($product, $metaTemplate)
    {
        $currentStoreId = $product->getStoreId();
        //Check Product Template

        $storeViewTemplate = $metaTemplate->getStore() ? $metaTemplate->getStore() : '';
        $storeViewTemplateArray = explode(',', $storeViewTemplate);

        if ($currentStoreId && !in_array($currentStoreId, $storeViewTemplateArray)) {
            return $this;
        }
        //Check Store view Template with Current StoreView
        $productExcludeTemplateStore = $product->getData('excluded_meta_template');
        if ($productExcludeTemplateStore !== '1') {
            $this->processMetaTemplate->processProductMeta($product, $metaTemplate);
        }
        return $this;
    }

}
