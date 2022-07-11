<?php
namespace IiifPresentation\v3\Service\CanvasType;

use IiifPresentation\v3\CanvasType\File;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;

class FileFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $services, $requestedName, array $options = null)
    {
        $fileConvasTypeManager = $services->get('IiifPresentation\v3\FileCanvasTypeManager');
        return new File($fileConvasTypeManager);
    }
}
