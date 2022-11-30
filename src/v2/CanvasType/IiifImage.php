<?php
namespace IiifPresentation\v2\CanvasType;

use IiifPresentation\v2\Controller\ItemController;
use Omeka\Api\Representation\MediaRepresentation;

class IiifImage implements CanvasTypeInterface
{
    public function getCanvas(MediaRepresentation $media, ItemController $controller) : ?array
    {
        $imageInfo = $media->mediaData();
        return [
            '@id' => $controller->url()->fromRoute('iiif-presentation-2/item/canvas', ['media-id' => $media->id()], ['force_canonical' => true], true),
            '@type' => 'sc:Canvas',
            'label' => $media->displayTitle(),
            'width' => $imageInfo['width'],
            'height' => $imageInfo['height'],
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
                        'service' => $imageInfo,
                    ],
                    'on' => $controller->url()->fromRoute('iiif-presentation-2/item/canvas', ['media-id' => $media->id()], ['force_canonical' => true], true),
                ],
            ],
        ];
    }
}
