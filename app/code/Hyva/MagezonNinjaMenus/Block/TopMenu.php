<?php
/**
 * Hyvä Themes - https://hyva.io
 * Copyright © Hyvä Themes 2020-present. All rights reserved.
 * This product is licensed per Magento install
 * See https://hyva.io/license
 */

namespace Hyva\MagezonNinjaMenus\Block;

class TopMenu extends \Magezon\NinjaMenus\Block\TopMenu
{
    /**
     * @inheirtDoc
     */
    public function getMagentoTopMenuHtml()
    {
        $menuMobileBlock = $this->getLayout()->createBlock(\Magento\Framework\View\Element\Template::class);
        $menuMobileBlock->setTemplate('Magento_Theme::html/header/menu/mobile.phtml');
        $menuDesktopBlock = $this->getLayout()->createBlock(\Magento\Framework\View\Element\Template::class);
        $menuDesktopBlock->setTemplate('Magento_Theme::html/header/menu/desktop.phtml');
        return $menuMobileBlock->toHtml() . $menuDesktopBlock->toHtml();
    }
}
