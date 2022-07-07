<?php
namespace IiifPresentation\Service\ControllerPlugin;

use Interop\Container\ContainerInterface;
use IiifPresentation\ControllerPlugin\IiifPresentation2;
use Zend\ServiceManager\Factory\FactoryInterface;

class IiifPresentation2Factory implements FactoryInterface
{
    public function __invoke(ContainerInterface $services, $requestedName, array $options = null)
    {
        return new IiifPresentation3(
            $services->get('IiifPresentation\CanvasTypeManager2'),
            $services->get('EventManager')
        );
    }
}
