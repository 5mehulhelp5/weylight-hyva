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
namespace Bss\Gallery\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Class Data
 *
 * @package Bss\Gallery\Helper
 */
class Data extends AbstractHelper
{
    const FANCYBOX = 'bss_gallery/general/fancybox';

    /**
     * @var \Magento\Framework\Image\AdapterFactory
     */
    protected $imageFactory;

    /**
     * @var \Magento\Framework\Filter\FilterManager
     */
    protected $filter;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var Filesystem\Io\File
     */
    protected $file;

    /**
     * Data constructor.
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\Image\AdapterFactory $imageFactory
     * @param \Magento\Framework\Filter\FilterManager $filter
     * @param Filesystem $filesystem
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param Filesystem\Io\File $file
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Image\AdapterFactory $imageFactory,
        \Magento\Framework\Filter\FilterManager $filter,
        Filesystem $filesystem,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Filesystem\Io\File $file
    ) {
        $this->imageFactory = $imageFactory;
        $this->filter = $filter;
        $this->filesystem = $filesystem;
        $this->storeManager = $storeManager;
        $this->file = $file;
        parent::__construct($context);
    }

    /**
     * Get media Url
     *
     * @return string
     */
    public function getMediaUrl()
    {
        return $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
    }

    /**
     * Get file system
     *
     * @return Filesystem
     */
    public function returnFilesystem()
    {
        return $this->filesystem;
    }

    /**
     * Get image factory
     *
     * @return \Magento\Framework\Image\AdapterFactory
     */
    public function returnImageFactory()
    {
        return $this->imageFactory;
    }

    /**
     * Has file
     *
     * @param string $path
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function hasImageSize($path)
    {
        $baseUrl  =  $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB);
        $pathOrigin = str_replace($baseUrl, '', $path);
        $path1 = str_replace('/pub/', '', $pathOrigin);
        $path1 = $this->filesystem->getDirectoryRead(DirectoryList::ROOT)->getAbsolutePath() .'/pub/'. $path1;

        $path2 = str_replace($baseUrl, '', $path);
        $path2 = $this->filesystem->getDirectoryRead(DirectoryList::ROOT)->getAbsolutePath() . $path2;

        $fileExist1 = $this->file->fileExists($path1);
        $fileExist2 = $this->file->fileExists($path2);
        return $fileExist1 || $fileExist2;
    }

    /**
     * Disable fancy box
     *
     * @return string
     */
    public function disableFancybox()
    {
        $values = $this->scopeConfig->getValue(
            self::FANCYBOX,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        return $values;
    }

    /**
     * Format url key
     *
     * @param string $str
     * @return string
     */
    public function formatUrlKey($str)
    {
        return $this->filter->translitUrl($str);
    }

    /**
     * Get random string
     *
     * @return string
     * @throws \Exception
     */
    public function randomStr()
    {
        $length = 3;
        $keyspace = 'abcdefghijklmnopqrstuvwxyz';
        $pieces = [];
        $max = mb_strlen($keyspace, '8bit') - 1;
        for ($i = 0; $i < $length; ++$i) {
            $pieces []= $keyspace[random_int(0, $max)];
        }
        return implode('', $pieces);
    }
}
