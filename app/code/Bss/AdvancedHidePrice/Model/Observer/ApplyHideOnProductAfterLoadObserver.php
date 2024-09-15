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

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

class ApplyHideOnProductAfterLoadObserver implements ObserverInterface
{
    /**
     * @var \Bss\AdvancedHidePrice\Helper\Data
     */
    protected $helper;

    /**
     * ApplyHideOnProductAfterLoadObserver constructor.
     * @param \Bss\AdvancedHidePrice\Helper\Data $helper
     */
    public function __construct(
        \Bss\AdvancedHidePrice\Helper\Data $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * @param EventObserver $observer
     * @return $this|void
     */
    public function execute(EventObserver $observer)
    {
        $product = $observer->getEvent()->getProduct();
        if ($this->helper->activeCallHidePrice($product)) {
            if ($product->getTypeId() != 'bundle') {
                $product->setCanShowPrice(false);
            }

            $product->setActiveCallHidePrice($this->helper->activeCallHidePrice($product));
        }

        return $this;
    }
}
