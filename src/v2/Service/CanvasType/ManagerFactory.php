<?php
namespace IiifPresentation\v2\Service\CanvasType;

use IiifPresentation\v2\CanvasType\Manager;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;

class ManagerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $services, $requestedName, array $options = null)
    {
        $config = $services->get('Config');
        return new Manager($services, $config['iiif_presentation_v2_canvas_types']);
    }
}
