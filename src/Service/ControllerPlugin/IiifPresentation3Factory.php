<?php
namespace IiifPresentation\Service\ControllerPlugin;

use Interop\Container\ContainerInterface;
use IiifPresentation\ControllerPlugin\IiifPresentation3;
use Zend\ServiceManager\Factory\FactoryInterface;

class IiifPresentation3Factory implements FactoryInterface
{
    public function __invoke(ContainerInterface $services, $requestedName, array $options = null)
    {
        return new IiifPresentation3(
            $services->get('IiifPresentation\CanvasType\v3\Manager'),
            $services->get('EventManager')
        );
    }
}
