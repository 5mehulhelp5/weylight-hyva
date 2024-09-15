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
    'jquery',
    'mage/adminhtml/grid',
    'prototype',
    'mage/translate'
], function ($) {
    "use strict";
    return function (config) {
        $("#edit_form").append('<input type="hidden" id="item_thumb_id" name="item_thumb_id" value="' + config.itemtThumbId + '">');
        $('body').append('<div data-role="loader" class="loading-mask-gallery" style="display: none;"><div class="popup popup-loading"><div class="popup-inner">Please wait...</div></div></div>');
        $(document).ready(function () {
            $('#edit_form').on('click', 'table tbody tr td input:radio', function () {
                $('.loading-mask-gallery').show();
                var id = $(this).val();
                var url = config.urls;
                $.ajax({
                    url: url,
                    type: "POST",
                    data: "id=" + id,
                    dataType : "json",
                    success: function (data) {
                        if (data.status == 'true') {
                            $('input[name="item_thumb_id"]').val(id);
                        } else {
                            alert($.mage.__('Has an Error. Please refresh the page'));
                            location.reload();
                        }
                        $('.loading-mask-gallery').hide();
                    }
                });
            });
        });
    };
});
