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
namespace Bss\AdvancedHidePrice\Plugin;

class WishlistItem
{
    /**
     * @var \Bss\AdvancedHidePrice\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * WishlistItem constructor.
     * @param \Bss\AdvancedHidePrice\Helper\Data $helper
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $pr
     * @param \Magento\Framework\App\Request\Http $request
     */
    public function __construct(
        \Bss\AdvancedHidePrice\Helper\Data $helper,
        \Magento\Catalog\Api\ProductRepositoryInterface $pr,
        \Magento\Framework\App\Request\Http $request
    ) {
        $this->helper = $helper;
        $this->productRepository = $pr;
        $this->request = $request;
    }

    /**
     * @param \Magento\Wishlist\Model\Wishlist $subject
     * @param \Closure $proceed
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundGetItemCollection($subject, \Closure $proceed)
    {
        if ($this->request->getFullActionName() == 'wishlist_index_allcart') {
            $itemCollection = $proceed();
            foreach ($itemCollection as $item) {
                $productId = $item->getProductId();
                $product = $this->productRepository->getById($productId);
                if ($this->helper->activeCallHidePrice($product)) {
                    $itemCollection->removeItemByKey($item->getId());
                }
            }
            $result = $itemCollection;
        } else {
            $result = $proceed();
        }
        return $result;
    }
}
