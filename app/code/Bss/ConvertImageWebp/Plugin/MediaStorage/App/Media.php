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
 * @copyright  Copyright (c) 2021-2023 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
declare(strict_types=1);

namespace Bss\ConvertImageWebp\Plugin\MediaStorage\App;

use Bss\ConvertImageWebp\Model\Convert;
use Magento\Catalog\Model\View\Asset\PlaceholderFactory;
use Magento\Framework\App\Area;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\State;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\MediaStorage\Model\File\Storage\Config;
use Magento\MediaStorage\Model\File\Storage\ConfigFactory;
use Magento\MediaStorage\Model\File\Storage\Response;
use Magento\MediaStorage\Model\File\Storage\SynchronizationFactory;
use Magento\MediaStorage\Service\ImageResize;

class Media
{
    public const HASH = 'hash';

    /**
     * @var \Bss\ConvertImageWebp\Model\Convert
     */
    protected $convert;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var \Bss\ConvertImageWebp\Model\Config
     */
    protected $config;

    /**
     * @var Filesystem\DirectoryList
     */
    protected $dir;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    protected $jsonSerializer;

    /**
     * @var string
     */
    private $mediaDirectoryPath;

    /**
     * Configuration cache file path
     *
     * @var string
     */
    private $configCacheFile;

    /**
     * Requested file name relative to working directory
     *
     * @var string
     */
    private $relativeFileName;

    /**
     * @var Response
     */
    private $response;

    /**
     * @var WriteInterface
     */
    private $directoryPub;

    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    private $directoryMedia;

    /**
     * @var ConfigFactory
     */
    private $configFactory;

    /**
     * @var SynchronizationFactory
     */
    private $syncFactory;

    /**
     * @var PlaceholderFactory
     */
    private $placeholderFactory;

    /**
     * @var State
     */
    private $appState;

    /**
     * @var ImageResize
     */
    private $imageResize;

    /**
     * @var string
     */
    private $mediaUrlFormat;

    /**
     * @var File
     */
    private $file;

    /**
     * @var string|null
     */
    protected $imgFolderWebp;
    /**
     * @var \Bss\ConvertImageWebp\Model\Plugin\Config\CatalogMediaConfig
     */
    private $catalogMediaConfig;

    /**
     * Construct.
     *
     * @param Convert $convert
     * @param RequestInterface $request
     * @param \Bss\ConvertImageWebp\Model\Config $config
     * @param Filesystem\DirectoryList $dir
     * @param Json $jsonSerializer
     * @param ConfigFactory $configFactory
     * @param SynchronizationFactory $syncFactory
     * @param Response $response
     * @param Filesystem $filesystem
     * @param PlaceholderFactory $placeholderFactory
     * @param State $state
     * @param ImageResize $imageResize
     * @param File $file
     * @param \Bss\ConvertImageWebp\Model\Plugin\Config\CatalogMediaConfig $catalogMediaConfig
     * @throws FileSystemException
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Bss\ConvertImageWebp\Model\Convert $convert,
        \Magento\Framework\App\RequestInterface $request,
        \Bss\ConvertImageWebp\Model\Config $config,
        \Magento\Framework\Filesystem\DirectoryList $dir,
        \Magento\Framework\Serialize\Serializer\Json $jsonSerializer,
        ConfigFactory $configFactory,
        SynchronizationFactory $syncFactory,
        Response $response,
        Filesystem $filesystem,
        PlaceholderFactory $placeholderFactory,
        State $state,
        ImageResize $imageResize,
        File $file,
        \Bss\ConvertImageWebp\Model\Plugin\Config\CatalogMediaConfig $catalogMediaConfig
    ) {
        $this->convert = $convert;
        $this->config = $config;
        $this->request = $request;

        $this->dir = $dir;
        $this->jsonSerializer = $jsonSerializer;
        $this->response = $response;
        $this->directoryPub = $filesystem->getDirectoryWrite(
            DirectoryList::PUB,
            Filesystem\DriverPool::FILE
        );
        $this->directoryMedia = $filesystem->getDirectoryWrite(
            DirectoryList::MEDIA,
            Filesystem\DriverPool::FILE
        );
        $this->file = $file;
        $this->configFactory = $configFactory;
        $this->syncFactory = $syncFactory;
        $this->placeholderFactory = $placeholderFactory;
        $this->appState = $state;
        $this->imageResize = $imageResize;
        $this->catalogMediaConfig = $catalogMediaConfig;
    }

    /**
     * Overwrite convert webp when generate cache images.
     *
     * @param \Magento\MediaStorage\App\Media $subject
     * @param mixed $proceed
     * @return Response|mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function aroundLaunch(\Magento\MediaStorage\App\Media $subject, $proceed)
    {
        if (!$this->config->isEnableModule() || !$this->config->getAutoGenerateWebpImage()) {
            return $proceed();
        }

        $this->appState->setAreaCode(Area::AREA_GLOBAL);

        //Get mediaDirectoryPath
        if (!$this->mediaDirectoryPath) {
            $bp = $this->dir->getRoot();
            $this->configCacheFile = $bp . '/var/resource_config.json';
            $config = $this->jsonSerializer->unserialize(file_get_contents($this->configCacheFile));
            $mediaDirectory = $config['media_directory'];
            $this->mediaDirectoryPath = str_replace('\\', '/', $this->file->getRealPath($mediaDirectory));
        }

        //Convert path request to path core(jpg, png, ...).
        $pathRequest = $this->request->getPathInfo();
        $this->relativeFileName = $this->convert->replaceUrlImageDefault($pathRequest);
        $this->imgFolderWebp = $this->convert->replaceUrlImageDefault($pathRequest, 'webp');

        if ($this->checkMediaDirectoryChanged()) {
            // Path to media directory changed or absent - update the config
            /** @var Config $config */
            $config = $this->configFactory->create(['cacheFile' => $this->configCacheFile]);
            $config->save();
            $this->mediaDirectoryPath = $config->getMediaDirectory();
            $allowedResources = $config->getAllowedResources();
            $fileAbsolutePath = $this->directoryPub->getAbsolutePath($this->relativeFileName);
            $fileRelativePath = str_replace(rtrim($this->mediaDirectoryPath, '/') . '/', '', $fileAbsolutePath);
            if (!$this->isAllowed($fileRelativePath, $allowedResources)) {
                throw new \LogicException('The path is not allowed: ' . $this->relativeFileName);
            }
        }

        try {
            $this->createLocalCopy();

            //Generate image to webp.
            $destinationImageFilename = str_replace("media/media/", "media/", $this->mediaDirectoryPath . $pathRequest);
            if (strripos($destinationImageFilename, ".webp")) {
                $sourceImageFilename = str_replace("media/media/", "media/", $this->mediaDirectoryPath . $this->relativeFileName);
                $this->convert->convertWebp($sourceImageFilename, $destinationImageFilename);
            }

            if ($this->directoryPub->isReadable($this->relativeFileName)) {
                //Set response path image.
                $this->response->setFilePath($destinationImageFilename);
            } else {
                $this->setPlaceholderImage();
            }
        } catch (\Exception $e) {
            $this->setPlaceholderImage();
        }

        return $this->response;
    }

    /**
     * Create local copy of file and perform resizing if necessary.
     *
     * @throws NotFoundException
     */
    private function createLocalCopy()
    {
        $synchronizer = $this->syncFactory->create(['directory' => $this->directoryPub]);
        $synchronizer->synchronize($this->relativeFileName);

        if ($this->directoryPub->isReadable($this->relativeFileName)) {
            return;
        }
        if ($this->catalogMediaConfig->isMoreThanM242()) {
            $catalogMediaConfig = $this->catalogMediaConfig->getCatalogMediaConfig()->create();
            if ($catalogMediaConfig->getMediaUrlFormat() === self::HASH) {
                $this->imageResize->resizeFromImageName($this->getOriginalImage($this->relativeFileName));
                if (!$this->directoryPub->isReadable($this->relativeFileName)) {
                    $synchronizer->synchronize($this->relativeFileName);
                }
            }
        } else {
            $sync = $this->syncFactory->create(['directory' => $this->directoryPub]);
            $sync->synchronize($this->relativeFileName);
            $this->imageResize->resizeFromImageName($this->getOriginalImage($this->relativeFileName));
        }
    }

    /**
     * Check if media directory changed
     *
     * @return bool
     */
    private function checkMediaDirectoryChanged()
    {
        $mediaDirectoryPath = $this->mediaDirectoryPath ? rtrim($this->mediaDirectoryPath, '/') : '';
        $directoryMediaAbsolutePath = $this->directoryMedia->getAbsolutePath();
        $directoryMediaAbsolutePath = $directoryMediaAbsolutePath ? rtrim($directoryMediaAbsolutePath, '/') : '';
        return $mediaDirectoryPath !== $directoryMediaAbsolutePath;
    }

    /**
     * Set placeholder image into response
     *
     * @return void
     */
    private function setPlaceholderImage()
    {
        $placeholder = $this->placeholderFactory->create(['type' => 'image']);
        $this->response->setFilePath($placeholder->getPath());
    }

    /**
     * Find the path to the original image of the cache path
     *
     * @param string $resizedImagePath
     * @return string
     */
    private function getOriginalImage(string $resizedImagePath)
    {
        return preg_replace('|^.*((?:/[^/]+){3})$|', '$1', $resizedImagePath);
    }

    /**
     * Check isAllowed core.
     *
     * @param string|mixed $resource
     * @param array|mixed $allowedResources
     * @return bool
     */
    public function isAllowed($resource, $allowedResources)
    {
        foreach ($allowedResources as $allowedResource) {
            if (0 === stripos($resource, $allowedResource)) {
                return true;
            }
        }
        return false;
    }
}
