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
 * @category  BSS
 * @package   Hyva_BssGallery
 * @author    Extension Team
 * @copyright Copyright (c) 2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license   http://bsscommerce.com/Bss-Commerce-License.txt
 */

declare(strict_types=1);

namespace Hyva\BssGallery\ViewModels;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Bss\Gallery\Helper\Category as BssGallery;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\App\RequestInterface;

/**
 * Class Helper
 * @package Hyva\BssFastOrder\ViewModels
 */
class Helper implements ArgumentInterface
{
    private BssGallery $helper;

    protected Json $jsonSerializer;

    protected $request;

    /**
     * @param BssGallery $data
     * @param Json $jsonSerializer
     * @param RequestInterface $request
     */
    public function __construct(
        BssGallery $data,
        Json $jsonSerializer,
        RequestInterface $request,
    ) {
        $this->helper = $data;
        $this->jsonSerializer = $jsonSerializer;
        $this->request = $request;
    }

    /**
     * @return BssGallery
     */
    public function getHelper(): BssGallery
    {
        return $this->helper;
    }

    /**
     * Json serialization
     *
     * @param array $array
     * @return string
     */
    public function serialize(array $array): string
    {
        return $this->jsonSerializer->serialize($array);
    }
}

