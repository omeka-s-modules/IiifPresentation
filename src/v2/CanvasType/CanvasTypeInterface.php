<?php
namespace IiifPresentation\v2\CanvasType;

use IiifPresentation\v2\Controller\ItemController;
use Omeka\Api\Representation\MediaRepresentation;

interface CanvasTypeInterface
{
    public function getCanvas(MediaRepresentation $media, ItemController $controller) : ?array;
}
