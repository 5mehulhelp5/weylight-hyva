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
 * @copyright  Copyright (c) 2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\ConvertImageWebp\Plugin\Framework\Image;

use Magento\Framework\Image;

class AfterSave
{
    /**
     * @var \Bss\ConvertImageWebp\Model\Convert
     */
    protected $convert;

    /**
     * AfterSave constructor.
     *
     * @param \Bss\ConvertImageWebp\Model\Convert $convert
     */
    public function __construct(
        \Bss\ConvertImageWebp\Model\Convert $convert
    ) {
        $this->convert = $convert;
    }

    /**
     * Convert image webp when Admin upload image
     *
     * @param Image $subject
     * @param void $result
     * @param null|string $destination
     * @param null|string $newFileName
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterSave(Image $subject, $result, $destination = null, $newFileName = null)
    {
        try {
            $this->convert->convert((string)$destination);
        } catch (\Exception $e) {
            $this->convert->writeLog($e->getMessage());
        }
        return $result;
    }
}
