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
    'jquery',
    'underscore',
], function ($) {
    'use strict';
    return function (widget, _) {

        $.widget('mage.configurable', widget, {
            options: {
                priceHolderSelector: jQuery('.bss-call-for-price').length ? '.bss-price-box' : '.price-box'
            },

            /**
             * Configure an option, initializing it's state and enabling related options, which
             * populates the related option's selection and resets child option selections.
             * @private
             * @param {*} element - The element associated with a configurable option.
             */
            _configureElement: function (element) {
                this._super(element);
                this._UpdateAdvancedHidePrice();
            },
            _fillSelect: function (element) {
                if ($('#check-version-bss-callprice').length) {
                    this._super(element);
                } else {
                    var attributeId = element.id.replace(/[a-z]*/, ''),
                        options = this._getAttributeOptions(attributeId),
                        prevConfig,
                        index = 1,
                        allowedProducts,
                        i,
                        j,
                        basePrice = parseFloat(this.options.spConfig.prices.basePrice.amount),
                        optionFinalPrice,
                        optionPriceDiff,
                        optionPrices = this.options.spConfig.optionPrices,
                        allowedProductMinPrice;

                    this._clearSelect(element);
                    element.options[0] = new Option('', '');
                    element.options[0].innerHTML = this.options.spConfig.chooseText;
                    prevConfig = false;

                    if (element.prevSetting) {
                        prevConfig = element.prevSetting.options[element.prevSetting.selectedIndex];
                    }

                    if (options) {
                        for (i = 0; i < options.length; i++) {
                            allowedProducts = [];
                            optionPriceDiff = 0;

                            /* eslint-disable max-depth */
                            if (prevConfig) {
                                for (j = 0; j < options[i].products.length; j++) {
                                    // prevConfig.config can be undefined
                                    if (prevConfig.config &&
                                        prevConfig.config.allowedProducts &&
                                        prevConfig.config.allowedProducts.indexOf(options[i].products[j]) > -1) {
                                        allowedProducts.push(options[i].products[j]);
                                    }
                                }
                            } else {
                                allowedProducts = options[i].products.slice(0);

                                if (typeof allowedProducts[0] !== 'undefined' &&
                                    typeof optionPrices[allowedProducts[0]] !== 'undefined') {
                                    allowedProductMinPrice = this._getAllowedProductWithMinPrice(allowedProducts);
                                    optionFinalPrice = parseFloat(optionPrices[allowedProductMinPrice].finalPrice.amount);
                                    optionPriceDiff = optionFinalPrice - basePrice;
                                    if (!jQuery('#advancedhideprice').length) {
                                        if (optionPriceDiff !== 0) {
                                            options[i].label = options[i].label + ' ' + priceUtils.formatPrice(
                                                optionPriceDiff,
                                                this.options.priceFormat,
                                                true
                                            );
                                        }
                                    }

                                }
                            }

                            if (allowedProducts.length > 0) {
                                options[i].allowedProducts = allowedProducts;
                                element.options[index] = new Option(this._getOptionLabel(options[i]), options[i].id);

                                if (typeof options[i].price !== 'undefined') {
                                    element.options[index].setAttribute('price', options[i].price);
                                }

                                element.options[index].config = options[i];
                                index++;
                            }

                            /* eslint-enable max-depth */
                        }
                    }
                }
            },

           _UpdateAdvancedHidePrice: function () {
                var $widget = this,
                    index = '',
                    currentEl = 'currentEl',
                    elPrice,
                    childProductData = this.options.spConfig.advancedHidePrice,
                    $useAdvacedPrice,
                    $content;

                $(".super-attribute-select").each(function () {
                    var option_id = $(this).attr("option-selected");

                    if (typeof option_id === "undefined" && $(this).val() !== "") {
                        option_id = $(this).val();
                    }
                    if (option_id !== null && $(this).val() !== "") {
                        index += option_id + '_';
                    }
                });
                if (childProductData instanceof Object) {
                    if(jQuery('#advancedhideprice').length) { //product page

                        if(!childProductData['child'].hasOwnProperty(currentEl)) {
                            childProductData['child'][currentEl] = jQuery('#advancedhideprice').html();
                        }

                        if (!childProductData['child'].hasOwnProperty(index)) {
                            $widget._ResetAdvancedHidePrice(childProductData['child'][currentEl]);
                            return false;
                        }
                        $useAdvacedPrice = childProductData['child'][index]['call_hide_price'];

                        $content = childProductData['child'][index]['call_hide_price_content'];
                        if (!$useAdvacedPrice) {
                            jQuery('.price-box.price-final_price').css('display', 'block');
                            jQuery('#advancedhideprice').html(childProductData['child'][currentEl]);
                            jQuery('#advancedhideprice').find('#product-addtocart-button').attr('disabled', false);
                            jQuery('#hiddenbuttoncartbss').remove();
                        } else {
                            jQuery('.price-box.price-final_price').css('display', 'none');
                            jQuery('#advancedhideprice').html($content);
                        }
                    }else { //category page
                        if (!childProductData.hasOwnProperty('parent_id')) {
                            return false;
                        }

                        var selector = childProductData['selector'];
                        var element = '#advancedhideprice_price'+childProductData['parent_id'];

                        if (!childProductData['child'].hasOwnProperty(index)) {
                            $widget._ResetAdvancedHidePriceCategory(childProductData['child'][currentEl], element, selector);
                            return false;
                        }

                        $useAdvacedPrice = childProductData['child'][index]['call_hide_price'];
                        $content = childProductData['child'][index]['call_hide_price_content'];

                        if (!$useAdvacedPrice) {
                            jQuery(element).parent().find('.action.tocart').show();
                            jQuery(element).parent().find(selector).show();
                            jQuery(element).parents(".product-item-details").find('.action.tocart').show();
                            jQuery(element).parents(".product-item-details").find(selector).show();
                            jQuery('#advancedhideprice_price'+childProductData['parent_id']).show();
                            jQuery('#advancedhideprice_'+childProductData['parent_id']).html('');
                        } else {
                            jQuery(element).parent().find('.action.tocart').hide();
                            jQuery(element).parent().find(selector).hide();
                            jQuery(element).parents(".product-item-details").find('.action.tocart').hide();
                            jQuery(element).parents(".product-item-details").find(selector).hide();
                            jQuery('#advancedhideprice_price'+childProductData['parent_id']).hide();
                            jQuery('#advancedhideprice_'+childProductData['parent_id']).html($content);
                        }
                    }
                }
            },

            _ResetAdvancedHidePrice: function (currentEl) {
                jQuery('.price-box.price-final_price').css('display', 'block');
                jQuery('#advancedhideprice').html(currentEl);
            },

            _ResetAdvancedHidePriceCategory: function (elm, selector) {
                jQuery(elm).show();
                jQuery(elm).parent().find('.action.tocart').show();
                jQuery(elm).parent().find(selector).show();
                jQuery(elm).parents(".product-item-details").find('.action.tocart').show();
                jQuery(elm).parents(".product-item-details").find(selector).show();
                jQuery(elm).prev().html('');
            },
        });

        return $.mage.configurable;
    };
});
