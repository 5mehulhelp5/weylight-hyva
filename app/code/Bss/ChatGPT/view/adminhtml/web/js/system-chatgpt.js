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
 * @package    Bss_ChatGPT
 * @author     Extension Team
 * @copyright  Copyright (c) 2023-2023 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
define(['jquery'], function ($) {
    return function (config, element) {
        var changedApiKey = 0;
        // Default tokens ChatGPT 3.5
        var modelTokens = {
            'gpt-3.5-turbo': '4096',
            'gpt-4': '8192'
        };
        var selectorApiUrl = $("#chat_gpt_config_chatgpt_api_url"),
            selectorApiKey = $("#chat_gpt_config_chatgpt_api_key"),
            selectorModelType = $("#chat_gpt_config_chatgpt_model"),
            selectorTemperature = $("#chat_gpt_config_chatgpt_temperature"),
            selectorMaxTokens = $("#chat_gpt_config_chatgpt_max_tokens"),
            selectorIsActive = $("#chat_gpt_config_chatgpt_is_active"),
            selectorSystemRole = $("#chat_gpt_default_message_group_product_system_role"),
            selectorMessageProduct = $("#chat_gpt_default_message_group_product_product");

        changeMessAlert(true);

        function changeMessAlert(firstLoad = false, alert = "")
        {
            var messChatGPT = $("#system-chatgpt-messages");
            if (firstLoad) {
                messChatGPT.append('<div class="messages"><div class="message message-warning"></div></div>');
            }

            var val = selectorIsActive.val();
            var ele = messChatGPT.children().find('.message');
            if (parseInt(val)) {
                ele.removeClass('message-warning');
                ele.addClass('message-success');
                ele.text($.mage.__('ChatGPT configuration is ready.'));
            } else {
                ele.removeClass('message-success');
                ele.addClass('message-warning');
                ele.text(alert ? alert : $.mage.__('You should use "Call ChatGPT" button to check the configurations again after editing them.'));
            }
        }

        selectorApiKey.on('input', function () {
            changedApiKey = 1;

            selectorIsActive.val(0);
            changeMessAlert();
        });

        selectorModelType.change(function () {
            var model = selectorModelType.val();
            if (modelTokens[model]) {
                selectorMaxTokens.val(modelTokens[model]);
            }
            selectorIsActive.val(0);
            changeMessAlert();
        });

        selectorMaxTokens.change(function () {
            selectorIsActive.val(0);
            changeMessAlert();
        });

        selectorTemperature.change(function () {
            var temperature = selectorTemperature.val();
            if (!isNaN(temperature) && temperature > 2) {
                selectorTemperature.val(2);
            }
            selectorIsActive.val(0);
            changeMessAlert();
        });

        $(element).click(function () {
            if (!Number.isInteger(parseInt(selectorMaxTokens.val())) || selectorMaxTokens.val() <= 0) {
                message = $.mage.__("Please enter a valid integer and greater than 0 in Max Tokens field.");
                changeMessAlert(false, message);
                return;
            } else if (Number.isNaN(parseNumber(selectorTemperature.val())) || selectorTemperature.val() < 0) {
                message = $.mage.__("Please enter a valid number greater than or equal to 0 in Temperature field.");
                changeMessAlert(false, message);
                return;
            }

            $.ajax({
                type: "POST",
                url: config.url + "?isAjax=true",
                data: {
                    "form_key": config.form_key,
                    "search": selectorMessageProduct.val(),
                    "test_key": true,
                    "changed_key": changedApiKey,
                    "api_key": selectorApiKey.val() ?? "",
                    "model": selectorModelType.val() ?? "",
                    "temperature": selectorTemperature.val() ?? "",
                    "max_tokens": selectorMaxTokens.val() ?? "",
                    "system_role": selectorSystemRole.val() ?? ""
                },
                beforeSend: function () {
                    $('body').trigger('processStart');
                },
                success: function (response) {
                    var message = '';
                    if (response.data.success) {
                        selectorIsActive.val(1);
                    } else {
                        selectorIsActive.val(0);
                        if (response.data.error) {
                            message = $.mage.__(response.data.error);
                        } else {
                            message = $.mage.__('ChatGPT has an unexpected problem!');
                        }
                    }
                    changeMessAlert(false, message);
                },
                complete: function (response) {
                    $('body').trigger('processStop');
                },
                error: function (response) {
                    selectorIsActive.val(0);
                    var message = $.mage.__('ChatGPT has an unexpected problem!');
                    changeMessAlert(false, message);
                }
            });
        });
    };
});
