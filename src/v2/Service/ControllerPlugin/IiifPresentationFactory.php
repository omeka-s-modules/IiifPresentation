<?php
namespace IiifPresentation\v2\Service\ControllerPlugin;

use Interop\Container\ContainerInterface;
use IiifPresentation\v2\ControllerPlugin\IiifPresentation;
use Zend\ServiceManager\Factory\FactoryInterface;

class IiifPresentationFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $services, $requestedName, array $options = null)
    {
        return new IiifPresentation(
            $services->get('IiifPresentation\v2\CanvasTypeManager'),
            $services->get('EventManager')
        );
    }
}
