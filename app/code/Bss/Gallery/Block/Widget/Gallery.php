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
 * @copyright  Copyright (c) 2018-2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\Gallery\Block\Widget;

/**
 * Class Gallery
 *
 * @package Bss\Gallery\Block\Widget
 */
class Gallery extends \Bss\Gallery\Block\Base implements \Magento\Widget\Block\BlockInterface
{
    /**
     * @var string
     */
    protected $_template = 'widget/gallery.phtml';

    /**
     * @var array|mixed|null
     */
    protected $category;

    /**
     * Get category
     *
     * @return array|mixed|null
     */
    public function getCategory()
    {
        // Check if category has already been defined
        // makes our block nice and re-usable! We could
        // pass the 'category' data to this block, with a collection
        // that has been filtered differently!
        if (!$this->hasData('category')) {
            if ($this->getBssGalleryCategory()) {
                $category = $this->categoryFactory->create()->load($this->getBssGalleryCategory());
            } else {
                $category = $this->category;
            }
            $this->setData('category', $category);
        }
        return $this->getData('category');
    }

    /**
     * Get cate collection
     *
     * @return \Bss\Gallery\Model\ResourceModel\Item\Collection|false
     */
    public function getCollection()
    {
        $category = $this->getCategory();

        if ($category->getIsActive() &&
            $item_ids = explode(',', $category->getData('Item_ids') ?? '')
        ) {
            $itemCollection = $this->itemCollectionFactory->create();
            $itemCollection->addFieldToSelect('*')->addFieldToFilter(
                'item_id',
                ['in' => $item_ids]
            )->addFieldToFilter('is_active', ['eq' => 1]);
            $itemCollection->setOrder('sorting', 'ASC');
            return $itemCollection;
        }
        return false;
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
     * Get image url
     *
     * @param string $imageName
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getImageUrl($imageName)
    {
        $imageUrl = $this->getMediaUrl() . $this->itemDir . '/image' . $imageName;
        if ($imageName && $this->helper->hasImageSize($imageUrl)) {
            return $imageUrl;
        }
        return $this->getViewFileUrl('Bss_Gallery::images/default-image.jpg');
    }

    /**
     * Get relevant path to template
     *
     * @return string
     */
    public function getTemplate()
    {
        if (!$this->dataHelper->isEnabledInFrontend()) {
            return "";
        }
        return $this->_template;
    }
}
