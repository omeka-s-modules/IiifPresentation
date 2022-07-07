<?php
namespace IiifPresentation\v2\CanvasType;

use IiifPresentation\v2\Controller\ItemController;
use Omeka\Api\Representation\MediaRepresentation;

class File implements CanvasTypeInterface
{
    public function getCanvas(MediaRepresentation $media, ItemController $controller) : ?array
    {
        $mediaType = $media->mediaType();
        switch (strtok($mediaType, '/')) {
            case 'image':
                return $this->getCanvasForImageFile($media, $controller);
            case 'video':
                // IIIF Presentation API 2 does not support video files as
                // first-class resource content, as it does for image files.
                // While video can be provided via external annotation lists
                // referenced in the otherContent property, clients are
                // inconsistent in their support.
            default:
                return null;
        }
    }

    public function getCanvasForImageFile(MediaRepresentation $media, ItemController $controller)
    {
        [$width, $height] = getimagesize($media->originalUrl());
        return [
            '@id' => $controller->url()->fromRoute('iiif-presentation-2/item/canvas', ['media-id' => $media->id()], ['force_canonical' => true], true),
            '@type' => 'sc:Canvas',
            'label' => $media->displayTitle(),
            'width' => $width,
            'height' => $height,
            'thumbnail' => [
                '@id' => $media->thumbnailUrl('medium'),
                '@type' => 'dctypes:Image',
            ],
            'metadata' => $controller->iiifPresentation2()->getMetadata($media),
            'images' => [
                [
                    '@type' => 'oa:Annotation',
                    'motivation' => 'sc:painting',
                    'resource' => [
                        '@id' => $media->originalUrl(),
                        '@type' => 'dctypes:Image',
                        'format' => $media->mediaType(),
                        'width' => $width,
                        'height' => $height,
                    ],
                    'on' => $controller->url()->fromRoute('iiif-presentation-2/item/canvas', ['media-id' => $media->id()], ['force_canonical' => true], true),
                ],
            ],
        ];
    }
}
