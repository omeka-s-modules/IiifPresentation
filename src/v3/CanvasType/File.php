<?php
namespace IiifPresentation\v3\CanvasType;

use IiifPresentation\v3\Controller\ItemController;
use Omeka\Api\Representation\MediaRepresentation;

class File implements CanvasTypeInterface
{
    protected $fileCanvasTypeManager;

    public function __construct($fileCanvasTypeManager)
    {
        $this->fileCanvasTypeManager = $fileCanvasTypeManager;
    }

    /**
     * Get the canvas array.
     *
     * Note that we get the file canvas type based on a priority heuristic:
     * first, by media (MIME) type, then by extension, then by the type part of
     * the media type.
     */
    public function getCanvas(MediaRepresentation $media, ItemController $controller) : ?array
    {
        $mediaType = $media->mediaType();
        $extension = $media->extension();
        $type = strtok($mediaType, '/');
        if ($this->fileCanvasTypeManager->has($mediaType)) {
            $fileCanvasType = $mediaType;
        } elseif ($this->fileCanvasTypeManager->has($extension)) {
            $fileCanvasType = $extension;
        } elseif ($this->fileCanvasTypeManager->has($type)) {
            $fileCanvasType = $type;
        } else {
            // There is no corresponding file canvas type.
            return null;
        }
        $fileCanvasType = $this->fileCanvasTypeManager->get($fileCanvasType);
        return $fileCanvasType->getCanvas($media, $controller);
    }
}
