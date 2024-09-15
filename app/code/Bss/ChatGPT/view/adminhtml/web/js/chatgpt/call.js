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
define([
    'jquery'
], function ($) {
    'use strict';

    return function(request, element, reClick = null) {
        var selectorAlert = $("#chatgpt-alert");
        if (!request['search'] || !request['url'] || !request['form_key']) {
            var message = $.mage.__('Error call API ChatGPT!');
            selectorAlert.text(message);
            selectorAlert.css("color", "#00CC33");
            selectorAlert.insertAfter(element).show();

            return '';
        }
        $.ajax({
            type: "POST",
            url: request['url'] + '?isAjax=true',
            data: {
                "form_key": request['form_key'],
                "search": request['search'],
                "system_role": request['system_role']
            },
            beforeSend: function () {
                $('body').trigger('processStart');
            },
            success: function(response) {
                var message;
                if (!response.data.success) {
                    if (response.data.error) {
                        message = $.mage.__(response.data.error);
                    } else {
                        message = $.mage.__('ChatGPT has an unexpected problem!');
                    }

                    selectorAlert.text(message);
                    selectorAlert.css("color", "red");
                    selectorAlert.insertAfter(element).show();

                    return;
                }

                if (reClick) { // Click element show HTML code
                    reClick.click();
                }

                var content = element.val() + response.data.success;
                element.val(content);

                if (reClick) { // Re-click element
                    reClick.click();
                }

                element.trigger('change');

                message = $.mage.__('ChatGPT has created the content!');
                selectorAlert.text(message);
                selectorAlert.css("color", "#00CC33");
                selectorAlert.insertAfter(element).show();
            },
            complete: function (response) {
                var statusText = response.statusText ? response.statusText : "";
                var statusCode = response.status ? response.status : "";
                var message = 'API ChatGPT: ' + statusCode + ' - ' + statusText;
                console.log(message);
                $('body').trigger('processStop');
            },
            error: function (response) {
                var message = $.mage.__('Error call API ChatGPT!');
                selectorAlert.text(message);

                selectorAlert.css("color", "red");
                selectorAlert.insertAfter(element).show();
            }
        });
    }
});
