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
namespace Bss\ConvertImageWebp\Plugin\Framework\File;

class Uploader
{
    /**
     * @var \Bss\ConvertImageWebp\Model\Convert
     */
    protected $convert;

    /**
     * Uploader constructor.
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
     * @param \Magento\Framework\File\Uploader $subject
     * @param void $result
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterSave($subject, $result)
    {
        try {
            if (isset($result["path"]) && isset($result["file"])) {
                $file = $result["path"] . "/". $result["file"];
                if (strpos($result["file"], "/") === 0) {
                    $file = $result["path"] . $result["file"];
                }
                $this->convert->convert($file);
            }
        } catch (\Exception $e) {
            $this->convert->writeLog($e->getMessage());
        }
        return $result;
    }
}
