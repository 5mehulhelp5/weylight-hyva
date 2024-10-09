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
 * @package    Bss_ConvertImageWebp
 * @author     Extension Team
 * @copyright  Copyright (c) 2022-2023 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\ConvertImageWebp\Model;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Store\Model\StoreManagerInterface;
use WebPConvert\Convert\Exceptions\ConversionFailedException;
use WebPConvert\WebPConvert;

class Convert
{
    public const FOLDER_IMAGE = "bss/webp/";
    public const FOLDER_IMAGE_PRODUCT = "media/catalog/product/";

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var \Magento\Framework\Filesystem\Io\File
     */
    protected $ioFile;

    /**
     * @var string
     */
    protected $urlMediaWeb = "";

    /**
     * @var string
     */
    protected $mediaFolder = "";

    /**
     * @var string
     */
    protected $rootFolder = "";

    /**
     * @var string
     */
    protected $configQualityWebp = "";

    /**
     * @var \Magento\Framework\Filesystem\DirectoryList
     */
    protected $directoryList;

    /**
     * @var \Magento\Framework\Filesystem\Driver\File
     */
    protected $driverFile;

    /**
     * @var string
     */
    protected $urlBaseWeb;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlInterface;

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $filesystem;

    /**
     * Convert constructor.
     * @param \Psr\Log\LoggerInterface $logger
     * @param Config $config
     * @param \Magento\Framework\Filesystem\Io\File $ioFile
     * @param \Magento\Framework\Filesystem\Driver\File $driverFile
     * @param StoreManagerInterface $storeManager
     * @param \Magento\Framework\UrlInterface $urlInterface
     * @param \Magento\Framework\Filesystem $filesystem
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Bss\ConvertImageWebp\Model\Config $config,
        \Magento\Framework\Filesystem\Io\File     $ioFile,
        \Magento\Framework\Filesystem\Driver\File $driverFile,
        StoreManagerInterface                     $storeManager,
        \Magento\Framework\UrlInterface           $urlInterface,
        \Magento\Framework\Filesystem             $filesystem
    ) {
        $this->logger = $logger;
        $this->config = $config;
        $this->ioFile = $ioFile;
        $this->driverFile = $driverFile;
        $this->storeManager = $storeManager;
        $this->urlInterface = $urlInterface;
        $this->filesystem = $filesystem;
    }

    /**
     * Create a file image webp from other image
     *
     * @param string $sourceImageFilename
     * @param string $destinationImageFilename
     * @param int|null $quality
     * @throws ConversionFailedException
     */
    public function convertWebp(string $sourceImageFilename, string $destinationImageFilename, $quality = null): void
    {
        if (!$this->checkExistsFile($destinationImageFilename)) { // If exist img webp => Skip convert(performance optimization).
            $qualityWebp = $quality ?: $this->getQualityImageWebp();
            WebPConvert::convert($sourceImageFilename, $destinationImageFilename, $this->getOptions($qualityWebp));
        }
    }

    /**
     * Options when convert
     *
     * @param int $qualityWebp
     * @return array
     */
    public function getOptions($qualityWebp = null)
    {
        return [
            'quality' => 'auto',
            'max-quality' => $qualityWebp,
            'converters' => ['cwebp', 'gd', 'imagick', 'wpc', 'ewww'],
        ];
    }

    /**
     * Get quality image webp
     *
     * @return int
     */
    public function getQualityImageWebp()
    {
        if (!$this->configQualityWebp) {
            $this->configQualityWebp = (int)$this->config->getQuaLityImageWebp();
        }
        return $this->configQualityWebp;
    }

    /**
     * Convert image webp
     *
     * @param string $sourceImageUri
     * @param int|null $quality
     * @return bool
     * @throws \Exception
     */
    public function convert(string $sourceImageUri, $quality = null)
    {
        if ($this->isImageFile($sourceImageUri)) {
            $destinationImageUri = $this->getDestinationImageUri($sourceImageUri);

            try {
                $this->convertWebp($sourceImageUri, $destinationImageUri, $quality);
            } catch (\Exception $e) {
                throw new \Exception($destinationImageUri . ': ' . $e->getMessage());
            }
        }

        return true;
    }

    /**
     * Convert image webp
     *
     * @param string $sourceImageUri
     * @return bool
     */
    public function convertFrontend(string $sourceImageUri)
    {
        if ($this->isImageFile($sourceImageUri)) {
            if (!$this->checkExistsFile($sourceImageUri)) {
                return false;
            }
            $destinationImageUri = $this->getDestinationImageUri($sourceImageUri);

            try {
                $this->convertWebp($sourceImageUri, $destinationImageUri);
            } catch (\Exception $e) {
                return false;
            }
        }

        return true;
    }

    /**
     * Replace type image jpg, jpeg, png into webp
     *
     * @param string $url
     * @return string
     */
    public function replaceTypeImage($url)
    {
        $typeImages = [".jpg", ".jpeg", ".png"];
        $positionArray = [];
        foreach ($typeImages as $typeImage) {
            $positionArray[] = strripos($url, $typeImage) ? strripos($url, $typeImage) : 0;
        }
        $position = max($positionArray);
        if ($position) {
            return substr($url, 0, $position) . ".webp";
        }
        return $url;
    }

    /**
     * Write log
     *
     * @param string $message
     */
    public function writeLog($message)
    {
        $this->logger->critical($message);
    }

    /**
     * Convert url to folder
     *
     * @param string $urlImage
     * @return string
     * @throws FileSystemException|\Magento\Framework\Exception\NoSuchEntityException
     */
    public function convertUrlFolder($urlImage)
    {
        $baseDomain = $this->getBaseDomain($urlImage);

        if ($pathImgMedia = substr($urlImage, strlen($baseDomain))) {
            $webpFolder = $this->getMediaFolder() . self::FOLDER_IMAGE . $pathImgMedia;
            $webpFolder = $this->replaceTypeImage($webpFolder);
            $webpFolder = preg_replace('/\/static\/version([0-9]+\/)/', '/static/', $webpFolder);
            $webpFolder = str_replace("/webp/pub/media/", "/webp/media/", $webpFolder);
            if ($this->driverFile->isExists($webpFolder)) {
                return $this->getUrlMediaWeb() . substr($webpFolder, strlen($this->getMediaFolder()));
            } else {
                if ($this->config->getAutoGenerateWebpImage()) {
                    $imageFolder = $this->getMediaFolder() . substr($urlImage, strlen($this->getUrlMediaWeb()));
                    $imageFolder = preg_replace('/\/static\/version([0-9]+\/)/', '/static/', $imageFolder);
                    $imageFolder = str_replace("/webp/pub/media/", "/webp/media/", $imageFolder);

                    if (!$this->checkExistsFile($imageFolder) && strpos($urlImage, $baseDomain) !== false) {
                        return $this->replaceWebpConvertAutoFe($imageFolder);
                    }
                    $rendered = $this->convertFrontend($imageFolder);
                    if ($rendered && $this->driverFile->isExists($webpFolder)) {
                        return $this->getUrlMediaWeb() . substr($webpFolder, strlen($this->getMediaFolder()));
                    }
                }
            }
        }

        return $urlImage;
    }

    /**
     * Get url base web
     *
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getUrlBaseWeb()
    {
        if (!$this->urlBaseWeb) {
            $this->urlBaseWeb = $this->storeManager->getStore()
                ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB);
        }
        return $this->urlBaseWeb;
    }

    /**
     * Get base domain. Compatible with CDN
     *
     * @param string $urlImage
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getBaseDomain($urlImage)
    {
        $cdnUrl = $this->config->getUrlCDN();
        if ($cdnUrl && strpos($urlImage, $cdnUrl) === 0) {
            return $cdnUrl;
        }

        return $this->getUrlBaseWeb();
    }

    /**
     * Get url media webp. (This function is already compatible with CDN)
     *
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getUrlMediaWeb()
    {
        if (!$this->urlMediaWeb) {
            $this->urlMediaWeb = $this->storeManager->getStore()
                ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        }
        return $this->urlMediaWeb;
    }

    /**
     * Get root folder
     *
     * @return string
     */
    public function getRootFolder()
    {
        if (!$this->rootFolder) {
            $this->rootFolder = $this->filesystem->getDirectoryRead(DirectoryList::ROOT)->getAbsolutePath();
        }
        return $this->rootFolder;
    }

    /**
     * Get media folder
     *
     * @return string
     */
    public function getMediaFolder()
    {
        if (!$this->mediaFolder) {
            $this->mediaFolder = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath();
        }
        return $this->mediaFolder;
    }

    /**
     * Convert Image command
     *
     * @param string $path
     * @param int|null $quality
     * @return void
     * @throws FileSystemException
     */
    public function convertImageCommand($path, $quality = null)
    {
        $isFolder = $this->filesystem->getDirectoryWrite(DirectoryList::ROOT)->isDirectory($path);
        if ($isFolder) {
            $paths = $this->getAllFiles($path);
            $qualityWebp = $quality ? $quality : $this->getQualityImageWebp();
            foreach ($paths as $path) {
                if ($this->isImageFile($path)) {
                    $destinationImageUri = $this->getDestinationImageUriCommand($path);
                    try {
                        WebPConvert::convert($path, $destinationImageUri, $this->getOptions($qualityWebp));
                    } catch (\Exception $e) {
                        $this->logger->critical($e->getMessage());
                    }
                }
            }
        } elseif ($this->driverFile->isExists($path)) {
            try {
                $this->convert($path, $quality);
            } catch (\Exception $exception) {
                $this->logger->critical($exception->getMessage());
            }
        }
    }

    /**
     * Get all files form path folder
     *
     * @param string $path
     * @return array|string[]
     */
    public function getAllFiles($path)
    {
        $paths = [];
        try {
            //read just that single directory
            $paths = $this->driverFile->readDirectory($path);
            //read all folders
            $paths = $this->driverFile->readDirectoryRecursively($path);
        } catch (FileSystemException $e) {
            $this->logger->critical($e->getMessage());
        }

        return $paths;
    }

    /**
     * Check file is image
     *
     * @param string $file
     * @return bool
     */
    public function isImageFile($file)
    {
        $info = $this->ioFile->getPathInfo($file);
        return isset($info['extension']) && in_array(strtolower($info['extension']), ["jpg", "jpeg", "png"]);
    }

    /**
     * Check url is image convert FE(func replaceWebpConvertAutoFe()) when cache img not exist.
     *
     * @param string $url
     * @return bool
     */
    public function isImageFisrtLoadCache($url)
    {
        if (!$url || strpos($url, ".webp") === false) {
            return false;
        }

        $dataCheckUrl = ["jpg", "jpeg", "png"];
        foreach ($dataCheckUrl as $item) {
            if (strpos($url, "/" . $item . "/")  !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get uri of image webp
     *
     * @param string $path
     * @return string
     */
    public function getDestinationImageUriCommand($path)
    {
        $destinationImageUri = $this->replaceTypeImage($path);
        $destinationImageUri = str_replace('pub/', '', $destinationImageUri);
        return $this->getMediaFolder() . self::FOLDER_IMAGE . $destinationImageUri;
    }

    /**
     * Get Source Image Uri.
     *
     * @param string $urlImage
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getSourceImageUri($urlImage)
    {
        $baseDomain = $this->getBaseDomain($urlImage);
        $webpFolder = $this->getMediaFolder() . substr($urlImage, strlen($baseDomain));

        return str_replace("media/media/", "media/", $webpFolder);
    }

    /**
     * Get uri of image webp
     *
     * @param string $path
     * @return string
     */
    public function getDestinationImageUri($path)
    {
        $destinationImageUri = $this->replaceTypeImage($path);
        $pathImage = str_replace('pub/', '', (string)substr($destinationImageUri, strlen($this->getRootFolder())));
        return $this->getMediaFolder() . self::FOLDER_IMAGE . $pathImage;
    }

    /**
     * Check exists file
     *
     * @param string $path
     * @return bool
     */
    public function checkExistsFile($path)
    {
        try {
            return $this->driverFile->isExists($path);
        } catch (\Exception $exception) {
            return false;
        }
    }

    /**
     * Replace webp convert auto in frontend.
     *
     * @param string $file
     * @return string
     */
    public function replaceWebpConvertAutoFe($file)
    {
        if ($file && strpos($file, self::FOLDER_IMAGE_PRODUCT) !== false) {
            $info = $this->ioFile->getPathInfo($file);
            $webpFolder = $info["dirname"] . "/" . $info["extension"] . "/" . $info["filename"] . ".webp";
            return $this->getUrlMediaWeb() . substr($webpFolder, strlen($this->getMediaFolder()));
        }
        return $file;
    }

    /**
     * Replace type image jpg, jpeg, png into webp
     *
     * @param string $url
     * @param string|null $type
     * @return string|null
     */
    public function replaceUrlImageDefault($url, $type = null)
    {
        $position = $url ? strripos($url, ".webp") : 0;
        if (!$position) {
            return $url;
        }

        $lenUrl = strlen($url);
        $position1 =  strripos($url, "/", $position - $lenUrl);
        $position2 = strripos($url, "/", $position1 - $lenUrl - 1);
        $typeImage = substr($url, $position2 + 1, $position1 - $position2 - 1);
        $imgFolder = substr($url, 0, $position2 + 1) . substr($url, $position1 + 1, $position - $position1);

        if ($type) {
            return $imgFolder . $type;
        }
        return $imgFolder . $typeImage;
    }
}
