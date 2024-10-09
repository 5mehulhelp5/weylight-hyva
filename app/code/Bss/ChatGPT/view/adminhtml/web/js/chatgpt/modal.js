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
    'jquery',
    'Magento_Ui/js/modal/modal',
    'chat_gpt-call'
], function ($, modal, callChatGPT) {
    'use strict';

    window.lastVariables = [];
    window.lastMessage = [];
    window.lastRole = [];
    window.lastUseAllAttr = [];

    var currentUrl = $(location).attr("href");
    var inProductPage = currentUrl.indexOf('catalog/product/edit') === -1 ? 0 : 1;
    var inCategoryPage = currentUrl.indexOf('catalog/category/') === -1 ? 0 : 1;
    var inCmsPage = currentUrl.indexOf('cms/page/edit') === -1 ? 0 : 1;

    var listVariables,
        roleChatGPT = $("#system_role"),
        promptChatGPT = $("#prompt");

    return function(element, data) {
        if (inProductPage) {
            listVariables = $("#chatgpt-attributes");
            listVariables.attr('data-id', data['id']);

            /* Set default checkbox use all attributes */
            var selectorUseAll = $("#all-attr");
            var isUseAllAttr = window.lastUseAllAttr[data['id']] ?? false;
            selectorUseAll.change(function () {
                var isChecked = selectorUseAll.is(":checked");
                listVariables.attr('disabled', isChecked);

                var messPrompt = replaceMessPrompt(listVariables, promptChatGPT.val(), isChecked);
                promptChatGPT.val(messPrompt);
            });

            selectorUseAll.prop('checked', isUseAllAttr);
            selectorUseAll.trigger('change');
        } else {
            listVariables = $("#chatgpt-variables");
            listVariables.attr('data-id', data['id']);
        }

        var defaultVariables = [];
        if (inProductPage) {
            defaultVariables = 'product[name]';
        } else if (inCategoryPage) {
            defaultVariables = $("input[name='name']").val();
        } else if (inCmsPage) {
            defaultVariables = $("input[name='title']").val();
        }

        /* Set default variables */
        listVariables.val(window.lastVariables[data['id']] ?? defaultVariables);
        /* Set default prompt */
        promptChatGPT.val(window.lastMessage[data['id']] ?? '');
        roleChatGPT.val(window.lastRole[data['id']] ?? data['role_default']);

        $(".reload-icon").click(function () {
            roleChatGPT.val(data['role_default']);
        });

        listVariables.change(function () {
            window.lastVariables[$(this).attr("data-id")] = $(this).val();
            var messPrompt = replaceMessPrompt(this, promptChatGPT.val(), inProductPage ? selectorUseAll.is(":checked") : false);
            promptChatGPT.val(messPrompt);
        });

        if (inProductPage) {
            replaceSelectBox();
        }

        var modalChatGPT = {
            type: 'popup',
            modalClass: 'chatgpt-modal-popup',
            responsive: true,
            clickableOverlay: true,
            buttons: [
                {
                    text: $.mage.__('Get Default Prompt'),
                    class: 'btn-get-mess-default',
                    click: function () {
                        var messPrompt = replaceMessPrompt(listVariables, data['mess_default'], inProductPage ? selectorUseAll.is(":checked") : false);
                        promptChatGPT.val(messPrompt);
                    }
                },
                {
                    text: $.mage.__('Send to ChatGPT'),
                    class: 'action-primary btn-send-prompt',
                    click: function () {
                        var request = [];
                        if (!promptChatGPT.val()) {
                            $('.btn-get-mess-default').click();
                        }

                        request['system_role'] = roleChatGPT.val();
                        request['search'] = promptChatGPT.val();
                        request['url'] = data['url'];
                        request['form_key'] = data['form_key'];

                        this.closeModal();

                        var reClick = null; // Click btn show HTML code in Text editor.
                        if (element.attr('aria-hidden') === 'true') {
                            switch (data['id']) {
                                case 'product_short_description':
                                    reClick = $('#toggleproduct_form_short_description');
                                    break;
                                case 'product_description':
                                    reClick = $('#toggleproduct_form_description');
                                    break;
                                case 'category_description':
                                    reClick = $('#togglecategory_form_description');
                                    break;
                                case 'cms_page_description':
                                    reClick = $('#togglecms_page_form_content');
                                    break;
                                case 'page_builder-text':
                                    reClick = $('#togglepagebuilder_text_form_content');
                                    break;
                            }
                        }

                        /* Call ChatGPT API */
                        callChatGPT(request, element, reClick);
                    }
                }
            ]
        };

        modal(modalChatGPT, $('.modal-chat_gpt'));
        $('.modal-chat_gpt').modal('openModal');

        /* Save default prompt and attribute selected */
        $(".modal-chat_gpt").on('modalclosed', function() {
            var id = $(this).find('.chatgpt-variables').attr('data-id');
            window.lastMessage[id] = $(this).find('#prompt').val();
            window.lastRole[id] = $(this).find('#system_role').val();
            window.lastUseAllAttr[id] = $(this).find("#all-attr").is(":checked");
        });

        promptChatGPT.change(function () {
            var isChecked = inProductPage ? selectorUseAll.is(":checked") : false;
            var messPrompt = replaceMessPrompt(listVariables, promptChatGPT.val(), isChecked);
            promptChatGPT.val(messPrompt);
        });
    }

    function replaceSelectBox() {
        $("input[name^='product[']").each(function() {
            var value = $(this).val();
            if (value) {
                if ($(this).attr('type') === 'checkbox') {
                    if (value == '0') {
                        value = $.mage.__('No');
                    } else if (value == '1') {
                        value = $.mage.__('Yes');
                    }
                }
                var name = $(this).attr('name');

                $("option[value='" + name + "']").attr('data-value', value);
            }
        });
        $("select[name^='product[']").each(function() {
            var select = $(this);

            var value = $(this).val();
            if (!$.isArray(value)) {
                value = [value];
            }
            var dataTitle = '';
            value.forEach(function(val) {
                var option = select.find("option[value='" + val + "']");
                var title = option.attr('data-title');
                if (title) {
                    dataTitle += "," + title;
                }
            });
            if (dataTitle) {
                dataTitle = dataTitle.substring(1);
                var name = $(this).attr('name');
                $("option[value='" + name + "']").attr('data-value', dataTitle);
            }
        });
    }

    function replaceMessPrompt(ele, defaultMess = '', isUseAllAtrr = false) {
        var title,
            value,
            result = '',
            variable = [];

        if (inProductPage) {
            if (!isUseAllAtrr) {
                variable = $(ele).val();
            } else {
                $(ele).find('option').each(function(kiz) {
                    variable.push($(this).val());
                });
            }
            variable.each(function(selector) {
                var optionSelector = $(ele).children("option[value='" + selector + "']");
                value = optionSelector.attr('data-value');
                if (value) {
                    title = optionSelector.attr('data-title');
                    result += title + ': ' + value + ';';
                }
            })
        } else {
            variable = $(ele).val().split("\n");
            result = variable.join(";");
        }

        defaultMess = defaultMess ? defaultMess : promptChatGPT.val();
        return defaultMess.replace(/{{[\s\S]*?}}/g, "{{" + result + "}}");
    }
});
