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

use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Class Base
 *
 * @package Bss\Gallery\Block
 */
class Base extends \Magento\Framework\View\Element\Template
{
    /**
     * @var string
     */
    protected $itemDir = 'Bss/Gallery/Item';

    /**
     * @var string
     */
    protected $cateDir = 'Bss/Gallery/Category';

    /**
     * @var \Bss\Gallery\Helper\Data
     */
    protected $helper;

    /**
     * @var \Bss\Gallery\Helper\Category
     */
    protected $dataHelper;

    /**
     * @var \Bss\Gallery\Model\ResourceModel\Category\CollectionFactory
     */
    protected $categoryCollectionFactory;

    /**
     * @var \Bss\Gallery\Model\ResourceModel\Item\CollectionFactory
     */
    protected $itemCollectionFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlInterface;

    /**
     * @var \Bss\Gallery\Model\CategoryFactory
     */
    protected $categoryFactory;

    /**
     * Base constructor.
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Bss\Gallery\Helper\Data $helper
     * @param \Bss\Gallery\Helper\Category $dataHelper
     * @param \Bss\Gallery\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory
     * @param \Bss\Gallery\Model\ResourceModel\Item\CollectionFactory $itemCollectionFactory
     * @param \Bss\Gallery\Model\CategoryFactory $categoryFactory
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\UrlInterface $urlInterface
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Bss\Gallery\Helper\Data $helper,
        \Bss\Gallery\Helper\Category $dataHelper,
        \Bss\Gallery\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory,
        \Bss\Gallery\Model\ResourceModel\Item\CollectionFactory $itemCollectionFactory,
        \Bss\Gallery\Model\CategoryFactory $categoryFactory,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\UrlInterface $urlInterface,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->helper = $helper;
        $this->dataHelper = $dataHelper;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->itemCollectionFactory = $itemCollectionFactory;
        $this->categoryFactory = $categoryFactory;
        $this->coreRegistry = $coreRegistry;
        $this->urlInterface = $urlInterface;
    }

    /**
     * Resize image
     *
     * @param string $image
     * @param string $type
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getImageResize($image, $type)
    {
        if ($type == 'item') {
            $dir = $this->itemDir;
        }
        if ($type == 'category') {
            $dir = $this->cateDir;
        }
        if (!empty($dir) && $this->helper->hasImageSize($this->getMediaUrl() . $dir . '/image' . $image)) {
            if (!$this->helper->hasImageSize($this->getMediaUrl() . $dir . '/image/resized' . $image)) {
                $mediaDir = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA);
                $absPath = $mediaDir->getAbsolutePath($dir . '/image/') . $image;
                $imageResized = $mediaDir->getAbsolutePath($dir . '/image/resized') . $image;
                $imageResize = $this->helper->returnImageFactory()->create();
                $imageResize->open($absPath);
                $imageResize->constrainOnly(true);
                $imageResize->keepTransparency(true);
                $imageResize->keepFrame(false);
                $imageResize->keepAspectRatio(true);
                $imageResize->resize(350);
                $dest = $imageResized;
                $imageResize->save($dest);
                $resizedURL = $this->getMediaUrl() . $dir . '/image/resized' . $image;
                return $resizedURL;
            } else {
                return $this->getMediaUrl() . $dir . '/image/resized' . $image;
            }
        } else {
            return $this->getViewFileUrl('Bss_Gallery::images/default-image.jpg');
        }
    }

    /**
     * Auto load
     *
     * @return string
     */
    public function isAutoLoad()
    {
        return $this->dataHelper->isAutoLoad();
    }

    /**
     * Set page speed
     *
     * @return string
     */
    public function getPageSpeed()
    {
        return $this->dataHelper->getPageSpeed();
    }

    /**
     * Get position of title
     *
     * @return string
     */
    public function getTitlePosition()
    {
        return $this->dataHelper->getTitlePosition();
    }

    /**
     * Get transition effect
     *
     * @return string
     */
    public function getTransitionEffect()
    {
        return $this->dataHelper->getTransitionEffect();
    }

    /**
     * Disable fancybox
     *
     * @return string
     */
    public function disableFancybox()
    {
        return $this->helper->disableFancybox();
    }

    /**
     * Get media url
     *
     * @return string
     */
    public function getMediaUrl()
    {
        return $this->_urlBuilder->getBaseUrl(['_type' => \Magento\Framework\UrlInterface::URL_TYPE_MEDIA]);
    }
}
