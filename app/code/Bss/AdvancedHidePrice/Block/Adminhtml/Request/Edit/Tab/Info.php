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
namespace Bss\AdvancedHidePrice\Block\Adminhtml\Request\Edit\Tab;

use Bss\AdvancedHidePrice\Model\Request;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Phrase;
use Magento\Framework\Registry;

/**
 * Class Info
 *
 * @package Bss\AdvancedHidePrice\Block\Adminhtml\Request\Edit\Tab
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Info extends Generic implements TabInterface
{
    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;

    /**
     * @var string
     */
    protected $_template = 'info.phtml';

    /**
     * @var ProductRepository
     */
    protected $productRepository;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var \Bss\AdvancedHidePrice\Helper\HelperInfo
     */
    protected $helperInfo;

    /**
     * @var \Magento\Framework\Serialize\SerializerInterface
     */
    protected $serializer;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;

    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    protected $productHelper;

    /**
     * Info constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param ProductRepository $productRepository
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Bss\AdvancedHidePrice\Helper\HelperInfo $helperInfo
     * @param \Magento\Framework\Serialize\SerializerInterface $serializer
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Magento\Framework\Pricing\Helper\Data $productHelper
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        \Magento\Store\Model\System\Store $systemStore,
        ProductRepository $productRepository,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Bss\AdvancedHidePrice\Helper\HelperInfo $helperInfo,
        \Magento\Framework\Serialize\SerializerInterface $serializer,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Framework\Pricing\Helper\Data $productHelper,
        array $data = []
    ) {
        $this->jsonHelper = $jsonHelper;
        $this->_systemStore = $systemStore;
        $this->productRepository = $productRepository;
        $this->messageManager = $messageManager;
        $this->helperInfo = $helperInfo;
        $this->serializer = $serializer;
        $this->productHelper = $productHelper;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Get Media path
     *
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function getMediaPath()
    {
        return $this->helperInfo->returnMediaPath();
    }

    /**
     * Get Product id
     *
     * @return mixed
     */
    public function getProductId()
    {
        return $this->getFormRequest()->getProductIds();
    }

    /**
     * Get Customer Email
     *
     * @return mixed
     */
    public function getCustomerEmail()
    {
        return $this->getFormRequest()->getCustomerEmail();
    }

    /**
     * Get Form Request
     *
     * @return Request
     */
    public function getFormRequest()
    {
        return $this->helperInfo->getFormRequest();
    }

    /**
     * Get Product Info
     *
     * @return string|void
     * @throws NoSuchEntityException
     */
    public function getProductInfo()
    {
        $productId = $this->getFormRequest()->getProductIds();
        $product = $this->getProduct();
        if ($product && $product->getId()) {
            return __('Name') . ' : ' . '<a target="_blank" href="' .
                $this->getUrl('catalog/product/edit/', ['id' => $productId]) . '">' . $product->getName()
                . '</a>' . ' - ' . __('Sku') . ' : ' . $product->getSku();
        } else {
            return;
        }
    }

    /**
     * Get Product
     *
     * @return ProductInterface|mixed
     * @throws NoSuchEntityException
     */
    public function getProduct()
    {
        return $this->helperInfo->getProduct();
    }

    /**
     * Get Customer Info
     *
     * @return string
     */
    public function getCustomerInfo()
    {
        $request = $this->getFormRequest();

        return __('Name') . ' : ' . $request->getCustomerName()
            . ' - ' . __('Email') . ' : ' . $request->getCustomerEmail();
    }

    /**
     * Get Additional Info
     *
     * @return mixed
     */
    public function getAdditonalInfo()
    {
        $request = $this->getFormRequest();

        return $this->serializer->unserialize($request->getContent());
    }

    /**
     * Get Tab Label
     *
     * @return Phrase|string
     */
    public function getTabLabel()
    {
        return __('Request Info');
    }

    /**
     * Field To Html Backend
     *
     * @param string $label
     * @param string $type
     * @param string $value
     * @return string
     */
    public function fieldToHtmlBackend($label, $type, $value)
    {
        return $this->helperInfo->fieldToHtmlBackend($label, $type, $value);
    }

    /**
     * Get Tab Title
     *
     * @return Phrase|string
     */
    public function getTabTitle()
    {
        return __('Request Info');
    }

    /**
     * Can Show Tab
     *
     * @return bool
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Is Hidden
     *
     * @return bool
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Check Key
     *
     * @param string $key
     * @return bool|int
     */
    public function checkKey($key)
    {
        return strpos($key, 'form_key');
    }

    /**
     * Get Store Name
     *
     * @param string $storeId
     * @return string
     */
    public function getStoreName($storeId)
    {
        return $this->helperInfo->getStoreName($storeId);
    }

    /**
     * Get Custom Option
     *
     * @return mixed
     */
    public function getCustomOption()
    {
        $request = $this->getFormRequest();
        $customOption = $request->getRequiredOptions();
        if ($customOption) {
            return $this->serializer->unserialize($customOption);
        }

        return false;
    }

    /**
     * Set Price For Option
     *
     * @param float $price
     * @return float|string
     */
    public function setPriceForOption($price)
    {
        return $this->productHelper->currency($price, true, false);
    }
}
