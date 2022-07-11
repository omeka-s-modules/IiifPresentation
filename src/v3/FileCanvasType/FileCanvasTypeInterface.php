<?php
namespace IiifPresentation\v3\FileCanvasType;

use IiifPresentation\v3\Controller\ItemController;
use Omeka\Api\Representation\MediaRepresentation;

interface FileCanvasTypeInterface
{
    public function getCanvas(MediaRepresentation $media, ItemController $controller) : ?array;
}
