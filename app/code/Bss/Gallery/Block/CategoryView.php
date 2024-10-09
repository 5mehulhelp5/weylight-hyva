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
namespace Bss\Gallery\Block;

/**
 * Class CategoryView
 *
 * @package Bss\Gallery\Block
 */
class CategoryView extends \Bss\Gallery\Block\Base implements
    \Magento\Framework\DataObject\IdentityInterface
{
    /**
     * Create layout
     *
     * @return $this|\Magento\Framework\View\Element\Template
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareLayout()
    {
        if (!$this->getCollection()) {
            $category = $this->getCategory();
            if ($category == null) {
                return $this;
            }
            $item_ids = explode(',', $category->getData('Item_ids') ?? '');
            if ($item_ids != '') {
                $itemCollection = $this->itemCollectionFactory->create();
                $itemCollection->addFieldToSelect('*')->addFieldToFilter(
                    'item_id',
                    ['in' => $item_ids]
                )->addFieldToFilter('is_active', ['eq' => 1]);
                $itemCollection->setOrder('sorting', 'ASC');
                $this->setCollection($itemCollection);
            }
        }
        parent::_prepareLayout();
        /** @var \Magento\Theme\Block\Html\Pager */
        if ($this->getItemLayoutType() == 'standard') {
            $pager = $this->getLayout()->createBlock(
                \Magento\Theme\Block\Html\Pager::class,
                'gallery.items.list.pager'
            );
            $itemPerPage = $this->dataHelper->getItemPerPage();
            if ($itemPerPage) {
                $pager->setLimit($itemPerPage)
                    ->setCollection($this->getCollection());
            } else {
                $pager->setLimit(20)
                    ->setCollection($this->getCollection());
            }
            $this->setChild('pager', $pager);
        }
        $this->getCollection()->load();

        return $this;
    }

    /**
     * Get html of page
     *
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    /**
     * Get category
     *
     * @return \Bss\Galery\Model\Category
     */
    public function getCategory()
    {
        return $this->coreRegistry->registry('category');
    }

    /**
     * Return identifiers for produced content
     *
     * @return array
     */
    public function getIdentities()
    {
        return [\Bss\Gallery\Model\Category::CACHE_TAG . '_' . $this->getCategory()->getId()];
    }

    /**
     * Get url of image
     *
     * @param string $imageName
     * @return string
     */
    public function getImageUrl($imageName)
    {
        $imageUrl = $this->getMediaUrl() . $this->itemDir . '/image' . $imageName;
        if ($imageName && $this->helper->hasImageSize($imageUrl)) {
            return $imageUrl;
        }
        return $this->getViewFileUrl('Bss_Gallery::images/default-image.jpg');
    }
}
