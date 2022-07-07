<?php
namespace IiifPresentation\CanvasType\v3;

use Omeka\ServiceManager\AbstractPluginManager;
use Laminas\ServiceManager\Exception\ServiceNotFoundException;

class Manager extends AbstractPluginManager
{
    protected $autoAddInvokableClass = false;

    protected $instanceOf = CanvasTypeInterface::class;

    public function get($name, $options = [], $usePeeringServiceManagers = true)
    {
        return parent::get($name, $options, $usePeeringServiceManagers);
    }
}
