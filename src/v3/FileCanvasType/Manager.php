<?php
namespace IiifPresentation\v3\FileCanvasType;

use Omeka\ServiceManager\AbstractPluginManager;

class Manager extends AbstractPluginManager
{
    protected $autoAddInvokableClass = false;

    protected $instanceOf = FileCanvasTypeInterface::class;

    public function get($name, $options = [], $usePeeringServiceManagers = true)
    {
        return parent::get($name, $options, $usePeeringServiceManagers);
    }
}
