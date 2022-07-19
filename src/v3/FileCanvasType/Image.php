<?php
namespace IiifPresentation\v3\FileCanvasType;

use IiifPresentation\v3\Controller\ItemController;
use Omeka\Api\Representation\MediaRepresentation;

class Image implements FileCanvasTypeInterface
{
    public function getCanvas(MediaRepresentation $media, ItemController $controller) : ?array
    {
        // Attempt to get the dimensions via getimagesize(). If the function
        // is unsuccessful, set arbitrary dimensions so the canvas is valid.
        [$width, $height] = @getimagesize($media->originalUrl());
        $width = $width ?: 1000;
        $height = $height ?: 1000;
        return [
            'id' => $controller->url()->fromRoute('iiif-presentation-3/item/canvas', ['media-id' => $media->id()], ['force_canonical' => true], true),
            'type' => 'Canvas',
            'label' => [
                'none' => [
                    $media->displayTitle(),
                ],
            ],
            'thumbnail' => [
                [
                    'id' => $media->thumbnailUrl('medium'),
                    'type' => 'Image',
                ],
            ],
            'width' => $width,
            'height' => $height,
            'metadata' => $controller->iiifPresentation3()->getMetadata($media),
            'items' => [
                [
                    'id' => $controller->url()->fromRoute('iiif-presentation-3/item/annotation-page', ['media-id' => $media->id()], ['force_canonical' => true], true),
                    'type' => 'AnnotationPage',
                    'items' => [
                        [
                            'id' => $controller->url()->fromRoute('iiif-presentation-3/item/annotation', ['media-id' => $media->id()], ['force_canonical' => true], true),
                            'type' => 'Annotation',
                            'motivation' => 'painting',
                            'body' => [
                                'id' => $media->originalUrl(),
                                'type' => 'Image',
                                'format' => $media->mediaType(),
                            ],
                            'target' => $controller->url()->fromRoute('iiif-presentation-3/item/canvas', ['media-id' => $media->id()], ['force_canonical' => true], true),
                        ],
                    ],
                ],
            ],
        ];
    }
}
