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
            // content resources. While non-image files can be provided via external
            // annotation lists referenced in the otherContent property, clients
            // are inconsistent or nonexistent in their support.
            return null;
        }
        $canvas = [
            '@id' => $controller->url()->fromRoute('iiif-presentation-2/item/canvas', ['media-id' => $media->id()], ['force_canonical' => true], true),
            '@type' => 'sc:Canvas',
            'label' => $media->displayTitle(),
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
                    ],
                    'on' => $controller->url()->fromRoute('iiif-presentation-2/item/canvas', ['media-id' => $media->id()], ['force_canonical' => true], true),
                ],
            ],
        ];
        [$width, $height] = @getimagesize($media->originalUrl());
        if ($width && $height) {
            $canvas['width'] = $width;
            $canvas['height'] = $height;
        }
        return $canvas;
    }
}
