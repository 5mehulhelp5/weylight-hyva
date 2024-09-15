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
 * @package    Bss_ChatGPT
 * @author     Extension Team
 * @copyright  Copyright (c) 2023-2023 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\ChatGPT\Block\ChatGPT;

use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Framework\Json\Helper\Data as JsonHelper;

class ChatGPT extends \Magento\Backend\Block\Template
{
    /**
     * @var \Magento\Catalog\Model\ProductRepository
     */
    protected $productRepository;

    /**
     * @var \Magento\Catalog\Helper\Data
     */
    protected $helperCore;

    /**
     * @var \Bss\ChatGPT\Model\ChatGPT
     */
    protected $chatGPT;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    protected $json;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * Construct.
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Catalog\Model\ProductRepository $productRepository
     * @param \Magento\Catalog\Helper\Data $helperCore
     * @param \Bss\ChatGPT\Model\ChatGPT $chatGPT
     * @param \Magento\Framework\Serialize\Serializer\Json $json
     * @param \Magento\Framework\App\Request\Http $request
     * @param array $data
     * @param JsonHelper|null $jsonHelper
     * @param DirectoryHelper|null $directoryHelper
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magento\Catalog\Helper\Data $helperCore,
        \Bss\ChatGPT\Model\ChatGPT $chatGPT,
        \Magento\Framework\Serialize\Serializer\Json $json,
        \Magento\Framework\App\Request\Http $request,
        array $data = [],
        ?JsonHelper $jsonHelper = null,
        ?DirectoryHelper $directoryHelper = null
    ) {
        $this->productRepository = $productRepository;
        $this->helperCore = $helperCore;
        $this->chatGPT = $chatGPT;
        $this->json = $json;
        $this->request = $request;
        parent::__construct($context, $data, $jsonHelper, $directoryHelper);
    }

    /**
     * Get all attributes of current product.
     *
     * @return array
     */
    public function getAllAttributes()
    {
        return $this->chatGPT->getAllAttributes($this->helperCore->getProduct());
    }

    /**
     * Get all mess default in general config
     *
     * @param int $storeId
     * @return bool|string
     */
    public function getAllMessDefault($storeId = null)
    {
        return $this->chatGPT->getAllMessDefault($storeId);
    }

    /**
     * Get url call API
     *
     * @return string
     */
    public function getUrlApi()
    {
        return $this->chatGPT->getUrlChatGPT();
    }

    /**
     * Get full action name in page.
     *
     * @return string
     */
    public function getFullActionName()
    {
        return $this->request->getFullActionName();
    }
}
