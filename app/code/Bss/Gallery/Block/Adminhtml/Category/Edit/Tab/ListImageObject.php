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
namespace Bss\Gallery\Block\Adminhtml\Category\Edit\Tab;

/**
 * Class ListImage
 *
 * @package Bss\Gallery\Block\Adminhtml\Category\Edit\Tab
 */
class ListImageObject extends \Bss\Gallery\Block\Adminhtml\Category\Edit\Tab\ListImage
{
    /**
     * @var string
     */
    protected $_template = 'Bss_Gallery::category/listimage.phtml';

    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    protected $productMetadata;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Bss\Gallery\Model\ResourceModel\Item\CollectionFactory $collectionFactory,
        \Bss\Gallery\Model\CategoryFactory $bssCategoryFactory,
        \Bss\Gallery\Model\ItemFactory $bssItemFactory,
        \Magento\Catalog\Model\Session $catalogSession,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        array $data = []
    ) {
        $this->productMetadata = $productMetadata;
        parent::__construct(
            $context,
            $backendHelper,
            $collectionFactory,
            $bssCategoryFactory,
            $bssItemFactory,
            $catalogSession,
            $data
        );
    }

    /**
     * Get magento version
     *
     * @return string
     */
    public function magentoVersion()
    {
        return $this->productMetadata->getVersion();
    }
}
