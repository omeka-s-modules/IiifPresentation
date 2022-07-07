<?php
namespace IiifPresentation\CanvasType\v3;

use IiifPresentation\Controller\v3\ItemController;
use Omeka\Api\Representation\MediaRepresentation;

class File implements CanvasTypeInterface
{
    public function getCanvas(MediaRepresentation $media, ItemController $controller) : ?array
    {
        $mediaType = $media->mediaType();
        switch ( strtok($mediaType, '/')) {
            case 'image':
                return $this->getCanvasForImageFile($media, $controller);
            case 'video':
            default:
                return null;
        }
    }

    public function getCanvasForImageFile(MediaRepresentation $media, ItemController $controller)
    {
        [$width, $height] = getimagesize($media->originalUrl());
        return [
            'id' => $controller->url()->fromRoute('iiif-presentation-3/item/canvas', ['media-id' => $media->id()], ['force_canonical' => true], true),
            'type' => 'Canvas',
            'label' => [
                'none' => [
                    $media->displayTitle(),
                ],
            ],
            'width' => $width,
            'height' => $height,
            'thumbnail' => [
                [
                    'id' => $media->thumbnailUrl('medium'),
                    'type' => 'Image',
                ],
            ],
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
                                'width' => $width,
                                'height' => $height,
                            ],
                            'target' => $controller->url()->fromRoute('iiif-presentation-3/item/canvas', ['media-id' => $media->id()], ['force_canonical' => true], true),
                        ],
                    ],
                ],
            ],
        ];
    }
}
