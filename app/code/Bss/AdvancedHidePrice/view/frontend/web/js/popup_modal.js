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
        'Magento_Ui/js/modal/modal',
    ], function ($, modal) {
        return function (config) {
            var requiredOptions = config.customOptions;
            var optionType = config.optionType;
            var options = {
                type: 'popup',
                responsive: true,
                innerScroll: true,
                title: $.mage.__('Call For Price Form'),
                wrapperClass: 'modals-wrapper callforprice-modals-wrapper',
                buttons: [{
                    text: $.mage.__('Submit'),
                    class: 'callforprice_submit action primary',
                    attr: {
                        'data-action': 'confirm'
                    },
                    click: function () {
                        var dataForm = $('#callforprice_form');
                        //can be replaced with any event


dataForm.validation('isValid'); //validates form and returns boolean
                        if (dataForm.validation('isValid')) {
                            if ($('#product_addtocart_form').length) {
                                $('#product_addtocart_form').append($('.callforprice_request').clone());
                                $('#product_addtocart_form .callforprice_request').hide();
                                $('#product_addtocart_form').attr('action', $('#callforprice_form').attr('action'));
                                $("#product_addtocart_form").validate().settings.ignore = "*";
                                $('#product_addtocart_form').submit();
                            } else {
                                $('#callforprice_form').submit();
                            }

                        }
                    }
                }],
            };

            $(document).on('click', '.callforprice_clickme', function () {
                var productForm = $(this).parents('form').serializeArray();
                var customOption = [];

                if (requiredOptions instanceof Object) {
                    $.each(productForm, function (key, value) {
                        if (value['name'].indexOf('options[') !== -1) {
                            value['name'] = value['name'].replace('options[', '').replace(/]/gi, '').replace('[', '');
                            $.each(requiredOptions, function (key, val) {
                                if (key === value['name']) {
                                    value['name'] = val;
                                }
                            });
                            $.each(optionType, function (id, val) {
                                if (id === value['value']) {
                                    value['value'] = val;
                                }
                            });
                            customOption.push(value);
                        }
                    });
                    var customOptions = JSON.stringify(customOption);

                    $('.callforprice_request .required_options').val(customOptions);
                }


                var parentId = $(this).parent().find('input[name="parent_id"]').val();
                var product = 0;

                if ($(this).parent().find('input[name="productGroup"]').length > 0) {
                    product = $(this).parent().find('input[name="productGroup"]').val();
                } else {
                    product = $(this).parent().find('input[name="product"]').val();
                }
                var product_name = $(this).parent().find('input[name="product_name"]').val();

                $('.callforprice_request .parent_id').val(parentId);
                $('.callforprice_request .product_ids').val(product);
                $('.callforprice_request .product_name').val(product_name);
                $('#callforprice_form')[0].reset();
                var popup = modal(options, $('#callforprice_modal'));

                $('#callforprice_modal').modal('openModal');
            });
        };
    }
);
