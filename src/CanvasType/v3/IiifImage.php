<?php
namespace IiifPresentation\CanvasType\v3;

use IiifPresentation\Controller\v3\ItemController;
use Omeka\Api\Representation\MediaRepresentation;

class IiifImage implements CanvasTypeInterface
{
    public function getCanvas(MediaRepresentation $media, ItemController $controller) : ?array
    {
        $imageInfo = $media->mediaData();
        return [
            'id' => $controller->url()->fromRoute('iiif-presentation-3/item/canvas', ['media-id' => $media->id()], ['force_canonical' => true], true),
            'type' => 'Canvas',
            'label' => [
                'none' => [
                    $media->displayTitle(),
                ],
            ],
            'width' => $imageInfo['width'],
            'height' => $imageInfo['height'],
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
                                'service' => $media->mediaData(),
                            ],
                            'target' => $controller->url()->fromRoute('iiif-presentation-3/item/canvas', ['media-id' => $media->id()], ['force_canonical' => true], true),
                        ],
                    ],
                ],
            ],
        ];

    }
}
