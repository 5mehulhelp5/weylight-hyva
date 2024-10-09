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
namespace Bss\AdvancedHidePrice\Model\Observer;

use Magento\Catalog\Model\Product;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class AddCart
 *
 * @package Bss\AdvancedHidePrice\Model\Observer
 */
class AddCart implements ObserverInterface
{
    /**
     * @var \Bss\AdvancedHidePrice\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * AddCart constructor.
     * @param \Bss\AdvancedHidePrice\Helper\Data $helper
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $pr
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     */
    public function __construct(
        \Bss\AdvancedHidePrice\Helper\Data $helper,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Catalog\Api\ProductRepositoryInterface $pr,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
    ) {
        $this->helper = $helper;
        $this->request = $request;
        $this->productRepository = $pr;
        $this->productCollectionFactory = $productCollectionFactory;
    }

    /**
     * Validate product before add to cart
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $mainProduct = $observer->getEvent()->getProduct();
        $productType = $mainProduct->getTypeId();
        $requestInfo = $observer->getEvent()->getInfo();
        $this->validateCurrentProduct($mainProduct);
        if ($productType == 'grouped' &&
            isset($requestInfo['super_group']) &&
            is_array($requestInfo['super_group'])) {
            $itemGroupAddToCart = $requestInfo['super_group'];
            foreach ($itemGroupAddToCart as $key => $qty) {
                if ($qty > 0) {
                    $itemGroupAddToCartIds[] = $key;
                }
            }
            $collection = $this->getProductCollection($itemGroupAddToCartIds);
            $errorMessage = '';
            foreach ($collection as $item) {
                if ($this->helper->activeCallHidePrice($item)) {
                    $itemGroupAddToCart[$item->getId()] = 0;
                    if ($errorMessage != '') {
                        $errorMessage .= ', ';
                    }
                    $errorMessage .= $item->getName();
                }
            }

            if ($errorMessage != '') {
                throw new LocalizedException(
                    __('%1 cannot be added to your cart.', $errorMessage)
                );
            }
        }
        $this->validateRelatedProduct();
    }

    /**
     * Validate current product hide price
     *
     * @param Product $mainProduct
     * @throws LocalizedException
     */
    private function validateCurrentProduct($mainProduct)
    {
        if ($this->helper->activeCallHidePrice($mainProduct)) {
            $productName = $mainProduct->getName();
            throw new LocalizedException(
                __('%1 cannot be added to your cart.', $productName)
            );
        }
    }

    /**
     * Validate related product add
     *
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function validateRelatedProduct()
    {
        if ($this->request->getParam('related_product') !== null &&
            $this->request->getParam('related_product') != '') {
            $relatedProducts = explode(",", $this->request->getParam('related_product'));
            foreach ($relatedProducts as $relatedProduct) {
                $product = $this->productRepository->getById($relatedProduct, false);
                if ($this->helper->activeCallHidePrice($product)) {
                    $productName = $product->getName();
                    throw new LocalizedException(
                        __('Product %1 cannot be added to your cart with main product.', $productName)
                    );
                }
            }
        }
    }
    /**
     * Get products by ids
     *
     * @param array $ids
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection|\Magento\Framework\Data\Collection\AbstractDb
     */
    protected function getProductCollection($ids)
    {
        return $this->productCollectionFactory->create()->addAttributeToSelect('*')
            ->addFieldToFilter('entity_id', ['in' => $ids]);
    }
}
