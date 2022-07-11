<?php
namespace IiifPresentation\v2\CanvasType;

use IiifPresentation\v2\Controller\ItemController;
use Omeka\Api\Representation\MediaRepresentation;

class File implements CanvasTypeInterface
{
    public function getCanvas(MediaRepresentation $media, ItemController $controller) : ?array
    {
        $mediaType = $media->mediaType();
        if ('image' !== strtok($mediaType, '/')) {
            // IIIF Presentation API 2 does not support non-image files as first-class
            // resource content. While non-image files can be provided via external
            // annotation lists referenced in the otherContent property, clients
            // are inconsistent or nonexistent in their support.
            return null;
        }
        // Attempt to get the dimensions via getimagesize(). If the function
        // is unsuccessful, set arbitrary dimensions so the canvas is valid.
        [$width, $height] = @getimagesize($media->originalUrl());
        $width = $width ?: 1000;
        $height = $height ?: 1000;
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
