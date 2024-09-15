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

define([
    'jquery'
], function ($) {
    $(document).on("change", "#file-category", function () {
        if ($('#file-category').val().length > 0) {
            $('#import-category').removeAttr('disabled');
        } else {
            $('#import-category').attr('disabled', 'disabled');
        }
    });
    $(document).on("change", "#file-item", function () {
        if ($('#file-item').val().length > 0) {
            $('#import-item').removeAttr('disabled');
        } else {
            $('#import-item').attr('disabled', 'disabled');
        }
    });
});
