<?php
namespace IiifPresentation\v2\CanvasType;

use Omeka\ServiceManager\AbstractPluginManager;

class Manager extends AbstractPluginManager
{
    protected $autoAddInvokableClass = false;

    protected $instanceOf = CanvasTypeInterface::class;

    public function get($name, $options = [], $usePeeringServiceManagers = true)
    {
        return parent::get($name, $options, $usePeeringServiceManagers);
    }
}
