<?php

namespace Bss\SeoReport\Model\Config\Source;

use Magento\Framework\UrlInterface;

class CommentGoogleAuthUris implements \Magento\Config\Model\Config\CommentInterface
{
    /**
     * @var UrlInterface
     */
    protected $urlInterface;

    /**
     * @param UrlInterface $urlInterface
     */
    public function __construct(
        UrlInterface $urlInterface
    ) {
        $this->urlInterface = $urlInterface;
    }

    /**
     * Get comment text
     *
     * @param $elementValue
     * @return string
     */
    public function getCommentText($elementValue)
    {
        $url = "https://wiki.bsscommerce.com/docs/magento-2-seo-extensions/magento-2-seo-extension/user-guide/#post-738-_3a4f12ho4g9h";
        $comment = "To the authorization code, first paste this URL https://domain/seoreport/auth/GoogleApiUris(with [domain] being your store's domain) into the Authorized redirect URIs in your Google API Console.";
        return  $comment . '<a href="' . $url . '"target="_blank">Learn more.</a>';
    }
}
