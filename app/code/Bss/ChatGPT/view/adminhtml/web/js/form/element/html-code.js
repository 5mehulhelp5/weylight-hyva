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
/* global MediabrowserUtility, widgetTools, MagentovariablePlugin */
define([
    'Magento_Ui/js/form/element/textarea',
    'Magento_Ui/js/modal/modal',
    'jquery',
    'chat_gpt-modal',
    'mage/adminhtml/wysiwyg/widget',
    'mage/loader'
], function (Textarea, modal, $, modalChatGPT) {
    'use strict';

    var HTML_ID_PLACEHOLDER = 'HTML_ID_PLACEHOLDER';
    var currentUrl = $(location).attr("href");

    return Textarea.extend({
        clickChatGPTContent: function () {
            var data = [];
            data['id'] = 'page_builder-code';
            data['url'] = this.url_api;
            data['form_key'] = this.form_key;
            var messDefault = this.checkPage(currentUrl);
            data['role_default'] = messDefault.system_role;
            data['mess_default'] = messDefault.prompt;

            modalChatGPT($('#' + this.uid), data);
        },

        checkPage: function (currentUrl) {
            var mess_default = '';
            var storeId = currentUrl.match(/store\/(\d+)/) ? currentUrl.match(/store\/(\d+)/)[1] : 1;

            if (currentUrl.indexOf('catalog/product/edit') !== -1) {
                mess_default = this.message_default.product[storeId];
            } else if (currentUrl.indexOf('catalog/category/') !== -1) {
                mess_default = this.message_default.category[storeId];
            } else if (currentUrl.indexOf('cms/page/edit') !== -1) {
                mess_default = this.message_default.cms[storeId];
            }

            return mess_default;
        },

        isEnableChatGPT: function () {
            return this.url_api ? true : false;
        },

        /* Logic core Magento 2 */
        defaults: {
            elementTmpl: 'Magento_PageBuilder/form/element/html-code'
        },

        /**
         * Click event for Insert Widget Button
         */
        clickInsertWidget: function () {
            return widgetTools.openDialog(
                this.widgetUrl.replace(HTML_ID_PLACEHOLDER, this.uid)
            );
        },

        /**
         * Click event for Insert Image Button
         */
        clickInsertImage: function () {
            return MediabrowserUtility.openDialog(
                this.imageUrl.replace(HTML_ID_PLACEHOLDER, this.uid)
            );
        },

        /**
         * Click event for Insert Variable Button
         */
        clickInsertVariable: function () {
            return MagentovariablePlugin.loadChooser(
                this.variableUrl,
                this.uid
            );
        },
    });
});
