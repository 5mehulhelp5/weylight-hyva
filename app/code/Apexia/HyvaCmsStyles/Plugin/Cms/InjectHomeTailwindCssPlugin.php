<?php
namespace Apexia\HyvaCmsStyles\Plugin\Cms;

use Magento\Cms\Block\Page as CmsPage;
use Magento\Framework\View\DesignInterface;
use Magento\Framework\App\ResourceConnection;

class InjectHomeTailwindCssPlugin
{
    protected $resource;
    protected $design;

    public function __construct(
        ResourceConnection $resource,
        DesignInterface $design
    ) {
        $this->resource = $resource;
        $this->design = $design;
    }

    public function afterToHtml(CmsPage $subject, $result)
    {
        $cmsPage = $subject->getPage();

        if (!$cmsPage || !$cmsPage->getIdentifier() || $cmsPage->getIdentifier() !== 'home') {
            return $result;
        }

        $entityId = (int) $cmsPage->getId();
        $themePath = $this->design->getDesignTheme()->getThemePath();

        $connection = $this->resource->getConnection();
        $tableName = $connection->getTableName('hyva_cms_page_tailwindcss');

        $select = $connection->select()
            ->from($tableName, ['css'])
            ->where('entity_id = ?', $entityId)
            ->where('theme = ?', 'frontend/' . $themePath)
            ->limit(1);

        $css = $connection->fetchOne($select);

        if (!$css) {
            return $result;
        }

        $styleTag = "<style>\n" . $css . "\n</style>\n";

        return $styleTag . $result;
    }
}
