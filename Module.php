<?php
namespace IiifPresentation;

use Composer\Semver\Comparator;
use Laminas\Mvc\MvcEvent;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Omeka\Module\AbstractModule;

class Module extends AbstractModule
{
    public function getConfig()
    {
        return include sprintf('%s/config/module.config.php', __DIR__);
    }

    public function onBootstrap(MvcEvent $event)
    {
        parent::onBootstrap($event);

        $acl = $this->getServiceLocator()->get('Omeka\Acl');
        $acl->allow(
            null,
            [
                'IiifPresentation\v2\Controller\Item',
                'IiifPresentation\v2\Controller\ItemSet',
                'IiifPresentation\v3\Controller\Item',
                'IiifPresentation\v3\Controller\ItemSet',
            ]
        );
    }

    public function install(ServiceLocatorInterface $services)
    {
        $sql = <<<'SQL'
CREATE TABLE iiif_presentation_image_size (id INT UNSIGNED NOT NULL, width BIGINT DEFAULT NULL, height BIGINT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;
SQL;
        $conn = $services->get('Omeka\Connection');
        $conn->exec('SET FOREIGN_KEY_CHECKS=0;');
        $conn->exec($sql);
        $conn->exec('SET FOREIGN_KEY_CHECKS=1;');
    }

    public function uninstall(ServiceLocatorInterface $services)
    {
        $conn = $services->get('Omeka\Connection');
        $conn->exec('SET FOREIGN_KEY_CHECKS=0;');
        $conn->exec('DROP TABLE IF EXISTS iiif_presentation_image_size;');
        $conn->exec('SET FOREIGN_KEY_CHECKS=1;');
    }

    public function upgrade($oldVersion, $newVersion, ServiceLocatorInterface $services)
    {
        $conn = $services->get('Omeka\Connection');
        if (Comparator::lessThan($oldVersion, '1.1.0-alpha')) {
            $conn->exec('CREATE TABLE iiif_presentation_image_size (id INT UNSIGNED NOT NULL, width BIGINT DEFAULT NULL, height BIGINT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;');
        }
    }
}
