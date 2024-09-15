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
 * @package    Bss_Gallery
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\Gallery\Block\Adminhtml\Item\Grid;

use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Framework\DataObject;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class ImageRenderer
 *
 * @package Bss\Gallery\Block\Adminhtml\Item\Grid
 */
class ImageRenderer extends AbstractRenderer
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Bss\Gallery\Helper\Data
     */
    protected $helper;

    /**
     * ImageRenderer constructor.
     *
     * @param \Magento\Backend\Block\Context $context
     * @param \Bss\Gallery\Helper\Data $helper
     * @param StoreManagerInterface $storemanager
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Bss\Gallery\Helper\Data $helper,
        StoreManagerInterface $storemanager,
        array $data = []
    ) {
        $this->helper = $helper;
        $this->storeManager = $storemanager;
        parent::__construct($context, $data);
        $this->_authorization = $context->getAuthorization();
    }

    /**
     * Render image html
     *
     * @param DataObject $row
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function render(DataObject $row)
    {
        $subDir = 'Bss/Gallery/Item/image';
        $mediaDirectory = $this->storeManager->getStore()->getBaseUrl(
            \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
        );
        $imageUrl = $mediaDirectory . $subDir . $this->_getValue($row);
        if ($this->helper->hasImageSize($imageUrl)) {
            return '<img src="' . $imageUrl . '" width="75"/>';
        }
        return '<img src="' . $this->getViewFileUrl('Bss_Gallery::images/default-image.jpg') . '" width="75"/>';
    }
}
