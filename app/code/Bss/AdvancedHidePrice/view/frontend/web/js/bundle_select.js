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
    'use strict';

    $.widget('mage.bundleSelect', {
        options: {
            classes: {
                optionClass: "bundle-option-select"
            }
        },

        _init: function () {
            this._EventListener();
            // $('.page-product-bundle .callforprice-container').css({"margin-left":"24px", "margin-top": "0px"});
        },

        _EventListener: function () {
            var $widget = this,
                options = this.options.classes;

            $('.' + options.optionClass).each(function () {
                $widget._showButton(this);

            });

            $('.' + options.optionClass).on('change', function () {
                $widget._showButton(this);
            });
        },

        _showButton: function (element) {
            var $widget = this,
                option_id = "",
                callHidePriceIds = this.options.callHidePriceIds;

            option_id = $(element).find(":selected").data("option");

            if (undefined === option_id ||
                option_id === '' ||
                option_id === null ||
                isNaN(parseInt(option_id))) {
                $(element).closest('.control').find('#callforprice_text').css('display', 'none');
                $(element).closest('.control').find('#callforprice_text').css('display', 'none');
                $(element).closest('.control').find('#callforprice').css('display', 'none');
            } else {
                if (callHidePriceIds.hasOwnProperty(option_id)) {
                    if (callHidePriceIds[option_id]['type_callprice'] == 'callforprice') {
                        $(element).closest('.control').find('.callforprice-container').css('display', 'block');
                        $(element).closest('.control').find('.callforprice-container [name="product"]').attr('value', option_id);
                        $(element).closest('.control').find('.callforprice-container [name="product_name"]').attr('value', callHidePriceIds[option_id]['product_name']);
                        $(element).closest('.control').find('.callforprice-container .callforprice_clickme').html(callHidePriceIds[option_id]['notify_text']);
                        $(element).closest('.control').find('#callforprice_text').css('display', 'none');
                    } else {
                        $(element).closest('.control').find('#callforprice_text').css('display', 'block');
                        $(element).closest('.control').find('#callforprice_text').html(callHidePriceIds[option_id]['notify_text']);
                        $(element).closest('.control').find('#callforprice').css('display', 'none');
                    }
                }
            }
        }
    });

    return $.mage.bundleSelect;
});
