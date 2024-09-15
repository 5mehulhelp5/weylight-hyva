<?php
/**
 *
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category  BSS
 * @package   Bss_ProductLabel
 * @author    Extension Team
 * @copyright Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license   http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\ProductLabel\Plugin;

use Bss\ProductLabel\Block\Label;

class SetLabelDataInSidebarCart
{
    /**
     * @var Label
     */
    private $label;

    /**
     * @var \Magento\Checkout\Model\SessionFactory
     */
    private $checkoutSessionFactory;

    /**
     * @var \Bss\ProductLabel\Helper\Data
     */
    private $helper;

    /**
     * SetLabelDataInSidebarCart constructor.
     * @param Label $label
     * @param CheckoutSessionFactory $checkoutSessionFactory
     * @param \Bss\ProductLabel\Helper\Data $helper
     */
    public function __construct(
        Label $label,
        \Magento\Checkout\Model\SessionFactory $checkoutSessionFactory,
        \Bss\ProductLabel\Helper\Data $helper
    ) {
        $this->label = $label;
        $this->checkoutSessionFactory = $checkoutSessionFactory;
        $this->helper = $helper;
    }

    /**
     * @param \Magento\Checkout\Model\DefaultConfigProvider $subject
     * @param array $result
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetConfig(
        \Magento\Checkout\Model\DefaultConfigProvider $subject,
        array $result
    ) {
        if (!$this->helper->isEnable()) {
            return $result;
        }

        $items = $result['totalsData']['items'];
        foreach ($items as $item) {
            $quoteItem = $this->checkoutSessionFactory->create()->getQuote()->getItemById($item['item_id']);
            $product = $quoteItem->getProduct();

            if ($product) {
                $result['imageData'][$item['item_id']]['label_data'] = $this->label->getLabelData($product);
            }
        }
        return $result;
    }
}
