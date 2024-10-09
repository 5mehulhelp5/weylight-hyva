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
 * @package    Bss_AdvancedHidePrice
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\AdvancedHidePrice\Plugin\Catalog\Model\Product\Type;

use Bss\AdvancedHidePrice\Helper\Data as HidePriceHelper;
use Magento\Bundle\Model\Product\Type;
use Magento\Bundle\Model\ResourceModel\Selection\CollectionFactory as SelectionCollectionFactory;
use Psr\Log\LoggerInterface;

/**
 * Class CheckHidePriceProduct
 *
 * @package Bss\AdvancedHidePrice\Plugin\Catalog\Model\Product\Type
 */
class CheckHidePriceProduct
{
    /**
     * CheckHidePriceProduct constructor.
     *
     * @param LoggerInterface $logger
     * @param HidePriceHelper $hidePriceHelper
     * @param SelectionCollectionFactory $selectionCollectionFactory
     */
    public function __construct(
        LoggerInterface $logger,
        HidePriceHelper $hidePriceHelper,
        SelectionCollectionFactory $selectionCollectionFactory
    ) {
        $this->logger = $logger;
        $this->hidePriceHelper = $hidePriceHelper;
        $this->selectionCollectionFactory = $selectionCollectionFactory;
    }

    /**
     * @param Type $cartItemOptionsProcessor
     * @param $optionsCollection
     * @param $product
     * @return mixed
     */
    public function afterGetOptionsCollection(
        Type $cartItemOptionsProcessor,
        $optionsCollection,
        $product
    ) {
        /**
         * If option has one or more products to select
         * And those products are all hide/call price enabled
         * Then force check require option
         */
        $optionIds = $this->getOptionIds($optionsCollection);
        $selectionData = $this->getSelectionData($optionIds);

        foreach ($selectionData as $opId => $selectionDatum) {
            if (!empty($selectionDatum)) {
                $forceOption = true;
                foreach ($selectionDatum as $pid) {
                    $product = $this->hidePriceHelper->getProductById($pid);
                    $activeHidePrice = $this->hidePriceHelper->activeCallHidePrice($product);
                    if (!$activeHidePrice) {
                        $forceOption = false;
                        break;
                    }
                }
                foreach ($optionsCollection->getItems() as $option) {
                    if ($option->getId() == $opId && $forceOption) {
                        $option->setData('bssClasses', 'bss-item-hidden');
                    }
                }
            }
        }
        return $optionsCollection;
    }

    /**
     * @param $optionsCollection
     * @return array
     */
    protected function getOptionIds($optionsCollection)
    {
        $optionIds = [];
        foreach ($optionsCollection->getItems() as $option) {
            if (!in_array($option->getId(), $optionIds)) {
                $optionIds[] = $option->getId();
            }
        }
        return $optionIds;
    }

    /**
     * @param $optionIds
     * @return array
     */
    protected function getSelectionData($optionIds)
    {
        $selectionData = [];
        $selectionCollection = $this->selectionCollectionFactory->create();
        $selectionCollection->setOptionIdsFilter($optionIds);
        foreach ($selectionCollection->getItems() as $selection) {
            $selectionData[$selection->getOptionId()][] = $selection->getProductId();
        }
        return $selectionData;
    }
}
