<?php
namespace IiifPresentation\CanvasType\v3;

use IiifPresentation\Controller\v3\ItemController;
use Omeka\Api\Representation\MediaRepresentation;;

interface CanvasTypeInterface
{
    public function getCanvas(MediaRepresentation $media,  ItemController $controller) : ?array;
}
