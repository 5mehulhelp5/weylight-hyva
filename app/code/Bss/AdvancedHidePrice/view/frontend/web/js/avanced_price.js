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
 * @package    Bss_AdvancedHidePrice
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
define([
    'jquery'
], function ($) {
    "use strict";
    return function (config, element) {
        var selector = config.selector;
        if ($(element).parents('.product-item').find("#callforprice.callforprice-container").length) {
            $(element).parents('.product-item').find("#callforprice.callforprice-container").html($(element));
        }
        var area = $(element).parents('.product-item').find(selector).parent();
        $(element).appendTo(area);
        $(element).removeClass('bss-hide');
    }
});
