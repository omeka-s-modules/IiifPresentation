<?php
namespace IiifPresentation;

use Laminas\Mvc\MvcEvent;
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
}
