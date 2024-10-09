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
namespace Bss\AdvancedHidePrice\Helper;

use Magento\Framework\Registry;
use Magento\Catalog\Model\ProductRepository;

class HelperInfo
{
    /**
     * @var ProductRepository
     */
    protected $productRepository;

    /**
     * @var \Bss\AdvancedHidePrice\Helper\Data
     */
    protected $helper;

    /**
     * @var \Bss\AdvancedHidePrice\Helper\GetType
     */
    protected $getType;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * HelperInfo constructor.
     * @param Registry $registry
     * @param ProductRepository $productRepository
     * @param Data $helper
     * @param GetType $getType
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param array $data
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(
        Registry $registry,
        ProductRepository $productRepository,
        \Bss\AdvancedHidePrice\Helper\Data $helper,
        \Bss\AdvancedHidePrice\Helper\GetType $getType,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\Filesystem\DirectoryList $directoryList,
        array $data = []
    ) {
        $this->getType = $getType;
        $this->productRepository = $productRepository;
        $this->helper = $helper;
        $this->messageManager = $messageManager;
        $this->coreRegistry = $registry;
        $this->directory = $directoryList;
    }

    public function returnMediaPath()
    {
        return $this->helper->getStoreObj()->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
    }


    /**
     * @return \Bss\AdvancedHidePrice\Model\Request
     */
    public function getFormRequest()
    {
        /** @var \Bss\AdvancedHidePrice\Model\Request $model */
        $model = $this->coreRegistry->registry('callforprice_request');
        return $model;
    }

    /**
     * @return \Magento\Catalog\Api\Data\ProductInterface|mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getProduct()
    {
        $productId = $this->getFormRequest()->getProductIds();
        try {
            $product = $this->productRepository->getById($productId);
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            $this->messageManager->addErrorMessage(__('Sorry,Product is not exist !'));
            $product = '';
        }
        return $product;
    }

    /**
     * @param string $label
     * @param string $type
     * @param string $value
     * @return string
     */
    public function fieldToHtmlBackend($label, $type, $value)
    {
        return $this->getType->fieldToHtmlBackend($label, $type, $value);
    }

    /**
     * @param $storeId
     * @return string
     */
    public function getStoreName($storeId)
    {
        return $this->helper->getStoreObj()->getStore($storeId)->getName();
    }
}
