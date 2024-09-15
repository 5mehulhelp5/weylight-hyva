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
    'chat_gpt-modal'
], function ($, modal, modalChatGPT) {
    'use strict';

    return function (config, element) {
        var firstClickedSeo = false,
            firstClickedContent = false;

        var currentUrl = $(location).attr("href");
        var inProductPage = currentUrl.indexOf('catalog/product/edit') === -1 ? 0 : 1;
        var inCmsPage = currentUrl.indexOf('cms/page/edit') === -1 ? 0 : 1;

        /* Get config */
        var urlApi = config.url,
            formKey = config.form_key,
            messDefaultProduct = config.message_default.product,
            messDefaultCategory = config.message_default.category,
            messDefaultCms = config.message_default.cms,
            messDefaultTitle = config.message_default.seo_title,
            messDefaultKeyword = config.message_default.seo_keyword,
            messDefaultDescription = config.message_default.seo_description;

        /* Config global */
        if (inProductPage) {
            window.roleDefaultPageBuilderText = messDefaultProduct.role;
            window.messDefaultPageBuilderText = messDefaultProduct.prompt;
        } else if (inCmsPage) {
            window.roleDefaultPageBuilderText = messDefaultCms.role;
            window.messDefaultPageBuilderText = messDefaultCms.prompt;
        } else { // In category page
            window.roleDefaultPageBuilderText = messDefaultCategory.role;
            window.messDefaultPageBuilderText = messDefaultCategory.prompt;
        }
        window.formKey = formKey;
        window.urlApi = urlApi;

        /* Selector elements (wait ajax load) */
        var selectorProductShortDescription,
            selectorProductDescription,
            selectorCategoryDescription,
            selectorCmsPageDescription,
            selectorSeoTitle,
            selectorSeoKeyword,
            selectorSeoDescription,
            selectorSeoGroup;
        /* Selector elements */
        var btnTitle = $("#chatgpt-seo_title"),
            btnKeyword = $("#chatgpt-seo_keyword"),
            btnDescription = $("#chatgpt-seo_description");

        $(document).ajaxStop(function () {
            if (inProductPage) {
                selectorSeoGroup = $("div[data-index='search-engine-optimization']");
            } else {
                if (inCmsPage) {
                    selectorSeoGroup = $("div[data-index='search_engine_optimisation']");
                } else {
                    selectorSeoGroup = $("div[data-index='search_engine_optimization']");
                }
            }
            $("div[data-index='content']").click(function () {
                if (firstClickedContent) {
                    return;
                }

                setTimeout(function () {
                    selectorProductShortDescription = $("#chatgpt-product_short_description");
                    selectorProductShortDescription.click(function () {
                        opendModal($('#product_form_short_description'), messDefaultProduct, 'product_short_description');
                    });

                    selectorProductDescription = $("#chatgpt-product_description");
                    selectorProductDescription.click(function () {
                        opendModal($('#product_form_description'), messDefaultProduct, 'product_description');
                    });

                    selectorCategoryDescription = $("#chatgpt-category_description");
                    selectorCategoryDescription.click(function () {
                        opendModal($('#category_form_description'), messDefaultCategory, 'category_description');
                    });

                    selectorCmsPageDescription = $("#chatgpt-cms_page_description");
                    selectorCmsPageDescription.click(function () {
                        opendModal($('#cms_page_form_content'), messDefaultCms, 'cms_page_description');
                    });
                }, 800);

                firstClickedContent = true;
            });

            selectorSeoGroup.click(function () {
                if (firstClickedSeo) {
                    return;
                }

                setTimeout(function () {
                    if (inProductPage) {
                        selectorSeoTitle = $("input[name='product[meta_title]']");
                        selectorSeoKeyword = $("textarea[name='product[meta_keyword]']");
                        selectorSeoDescription = $("textarea[name='product[meta_description]']");
                    } else {
                        selectorSeoTitle = $("input[name='meta_title']");
                        selectorSeoKeyword = $("textarea[name='meta_keywords']");
                        selectorSeoDescription = $("textarea[name='meta_description']");
                    }

                    btnTitle.insertAfter(selectorSeoTitle).show();
                    btnKeyword.insertAfter(selectorSeoKeyword).show();
                    btnDescription.insertAfter(selectorSeoDescription).show();
                }, 800); // Time ajax SEO loading

                firstClickedSeo = true;
            });
        });

        btnTitle.click(function () {
            opendModal(selectorSeoTitle, messDefaultTitle, 'seo_title');
        });

        btnKeyword.click(function () {
            opendModal(selectorSeoKeyword, messDefaultKeyword, 'seo_keyword');
        });

        btnDescription.click(function () {
            opendModal(selectorSeoDescription, messDefaultDescription, 'seo_description');
        });

        $("#all-attr").change(function () {
            if (this.checked) {
                $("#chatgpt-attributes").val().each(function() {
                    $(this).prop('selected', true);
                });
            } else {
                $("#chatgpt-attributes").val().each(function() {
                    $(this).prop('selectedIndex', -1);
                });
            }
        });

        /**
         * Opend modal
         *
         * @param element
         * @param messDefault
         * @param dataId
         */
        function opendModal(element, messDefault, dataId) {
            var data = [];
            data['id'] = dataId;
            data['url'] = urlApi;
            data['form_key'] = formKey;
            data['role_default'] = messDefault.role;
            data['mess_default'] = messDefault.prompt;

            modalChatGPT(element, data);
        }
    };
});
