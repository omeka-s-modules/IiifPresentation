<?php
namespace IiifPresentation\Service\CanvasType\v3;

use IiifPresentation\CanvasType\v3\Manager;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;

class ManagerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $services, $requestedName, array $options = null)
    {
        $config = $services->get('Config');
        return new Manager($services, $config['iiif_presentation_canvas_types_v3']);
    }
}
