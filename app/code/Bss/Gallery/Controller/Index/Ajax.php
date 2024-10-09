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
namespace Bss\Gallery\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Class Ajax
 *
 * @package Bss\Gallery\Controller\Index
 *
 * @SuppressWarnings(PHPMD.AllPurposeAction)
 */
class Ajax extends Action
{

    /**
     * @var \Bss\Gallery\Model\Category
     */
    protected $categoryFactory;

    /**
     * @var \Bss\Gallery\Model\ResourceModel\Item\CollectionFactory
     */
    protected $itemCollection;

    /**
     * @var \Bss\Gallery\Helper\Category
     */
    protected $dataHelper;

    /**
     * @var string
     */
    protected $subDir = 'Bss/Gallery/Item';

    /**
     * @var \Magento\Framework\Image\AdapterFactory
     */
    protected $imageFactory;

    /**
     * @var \Bss\Gallery\Helper\Data
     */
    protected $helper;

    /**
     * @var \Bss\Gallery\Model\Category
     */
    protected $category;

    /**
     * @var \Magento\Framework\View\Asset\Repository
     */
    protected $assetRepository;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * Ajax constructor.
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Bss\Gallery\Helper\Data $helper
     * @param \Bss\Gallery\Model\CategoryFactory $categoryFactory
     * @param \Bss\Gallery\Model\ResourceModel\Item\CollectionFactory $itemCollection
     * @param \Magento\Framework\View\Asset\Repository $assetRepository
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Bss\Gallery\Helper\Category $dataHelper
     * @param \Magento\Framework\Image\AdapterFactory $imageFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Bss\Gallery\Helper\Data $helper,
        \Bss\Gallery\Model\CategoryFactory $categoryFactory,
        \Bss\Gallery\Model\ResourceModel\Item\CollectionFactory $itemCollection,
        \Magento\Framework\View\Asset\Repository $assetRepository,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Bss\Gallery\Helper\Category $dataHelper,
        \Magento\Framework\Image\AdapterFactory $imageFactory
    ) {
        parent::__construct($context);
        $this->helper = $helper;
        $this->assetRepository = $assetRepository;
        $this->categoryFactory = $categoryFactory;
        $this->itemCollection = $itemCollection;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->dataHelper = $dataHelper;
        $this->imageFactory = $imageFactory;
    }

    /**
     * Execute get data by ajax
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Json|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $itemIds = $this->getRequest()->getPost('itemIds');
        $cateId = $this->getRequest()->getPost('cateId');
        $result = '';
        if ($cateId) {
            $limit = $this->dataHelper->getItemPerPage();
            $category = $this->categoryFactory->create()->load($cateId);
            if ($category) {
                $cateItemIds = explode(',', $category->getData('Item_ids') ?? '');
                $items = $this->itemCollection->create()
                    ->addFieldToFilter('item_id', ['in' => $cateItemIds]);
                if ($itemIds) {
                    $items->addFieldToFilter('item_id', ['nin' => $itemIds]);
                }
                $items->addFieldToFilter('is_active', 1)
                    ->setOrder('sorting', 'ASC')->setPageSize($limit);
                if ($items->getSize() > 0) {
                    $html = '';
                    foreach ($items as $item) {
                        $html .= '<li class="gallery-category-list-item" item-id="' . $item->getId() . '">';
                        $html .= '<div class="gallery-category-item">';
                        $itemDesc = $item->getDescription();
                        $imgResize = $this->getImageResize($item->getImage());
                        if ($item->getVideo() && $item->getVideo() != '') {
                            $itemVid = $item->getVideo();
                            $html .= '<a title="' . $itemDesc . '" href="' . $itemVid;
                            $html .= '" data-caption="' . $itemDesc . '"';
                            $html .= ' class="fancybox fancybox.iframe" rel="gallery-' . $cateId . '"';
                            $html .= ' data-fancybox="gallery" >';
                            $html .= '<img src="' . $imgResize . '" />';
                            $html .= '</a>';
                        } else {
                            $imgUrl = $this->getImageUrl($item->getImage());
                            $html .= '<a title="' . $itemDesc . '" href="' . $imgUrl;
                            $html .= '" data-caption="' . $itemDesc . '"';
                            $html .= 'class="fancybox" rel="gallery-' . $cateId . '" data-fancybox="gallery">';
                            $html .= '<img src="' . $imgResize . '" />';
                            $html .= '</a>';
                        }
                        $html .= '</div>';
                        $html .= '<h4 class="gallery-category-item-title">';
                        $html .= $item->getTitle();
                        $html .= '</h4>';
                        $html .= '</li>';
                    }
                    $result = $html;
                } elseif (!$itemIds) {
                    $result = '<p>' . __('This Album has no image !') . '</p>';
                }
            }
        }
        return $this->resultJsonFactory->create()->setData($result);
    }

    /**
     * Get image url
     *
     * @param string $imageName
     * @return string
     */
    private function getImageUrl($imageName)
    {
        $imgUrl = $this->helper->getMediaUrl() . 'Bss/Gallery/Item' . '/image' . $imageName;
        if ($imageName && $this->helper->hasImageSize($imgUrl)) {
            return $imgUrl;
        }
        return $this->getImageDefault();
    }

    /**
     * Get default image
     *
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getImageDefault()
    {
        $asset = $this->assetRepository->createAsset('Bss_Gallery::images/default-image.jpg');
        $url = $asset->getUrl();
        return $url;
    }

    /**
     * Resize image
     *
     * @param string $image
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getImageResize($image)
    {
        $mediaUrl = $this->helper->getMediaUrl();
        if ($this->helper->hasImageSize($mediaUrl . $this->subDir . '/image' . $image)) {
            if ($this->helper->hasImageSize($mediaUrl . $this->subDir . '/image/resized' . $image)) {
                if ($this->helper->hasImageSize($mediaUrl . $this->subDir . '/image/resized' . $image)) {
                    $mediaDir = $this->helper->returnFilesystem()->getDirectoryRead(DirectoryList::MEDIA);
                    $absPath = $mediaDir->getAbsolutePath($this->subDir . '/image/') . $image;
                    $imageResized = $mediaDir->getAbsolutePath($this->subDir . '/image/resized') . $image;
                    $imageResize = $this->imageFactory->create();
                    $imageResize->open($absPath);
                    $imageResize->constrainOnly(true);
                    $imageResize->keepTransparency(true);
                    $imageResize->keepFrame(false);
                    $imageResize->keepAspectRatio(true);
                    $imageResize->resize(350);
                    $dest = $imageResized;
                    $imageResize->save($dest);
                    $resizedURL = $mediaUrl . $this->subDir . '/image/resized' . $image;
                    return $resizedURL;
                } else {
                    return $mediaUrl . $this->subDir . '/image/resized' . $image;
                }
            } else {
                return $this->getImageDefault();
            }
        } else {
            return $this->getImageDefault();
        }
    }
}
