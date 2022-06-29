<?php
namespace IiifPresentation\ControllerPlugin;

use Omeka\Api\Representation\AbstractResourceEntityRepresentation;
use Laminas\Mvc\Controller\Plugin\AbstractPlugin;

class IiifPresentation extends AbstractPlugin
{
    /**
     * Get a IIIF Presentation API response
     *
     * @see https://iiif.io/api/presentation/2.1/#responses
     * @see https://iiif.io/api/presentation/3.0/#63-responses
     */
    public function getResponse(int $version, array $content)
    {
        $controller = $this->getController();
        switch ($version) {
            case 2:
                $contentType = 'application/ld+json';
                break;
            case 3:
                $contentType = 'application/ld+json;profile="http://iiif.io/api/presentation/3/context.json"';
                break;
            default:
                throw new \Exception('Invalid or unsupported IIIF Presentation API version');
        }
        $response = $controller->getResponse();
        $response->getHeaders()->addHeaders([
            'Content-Type' =>  $contentType,
            'Access-Control-Allow-Origin' => '*',
        ]);
        $response->setContent(json_encode($content, JSON_PRETTY_PRINT));
        return $response;
    }

    public function getCollection(int $version, array $itemIds)
    {
        switch ($version) {
            case 2:
                return $this->getCollectionV2($itemIds);
            case 3:
                return $this->getCollectionV3($itemIds);
            default:
                throw new \Exception('Invalid or unsupported IIIF Presentation API version');
        }
    }

    public function getManifest(int $version, int $itemId)
    {
        switch ($version) {
            case 2:
                return $this->getManifestV2($itemId);
            case 3:
                return $this->getManifestV3($itemId);
            default:
                throw new \Exception('Invalid or unsupported IIIF Presentation API version');
        }
    }

    public function getCollectionV2(array $itemIds)
    {
        return [];
    }

    public function getCollectionV3(array $itemIds)
    {
        $controller = $this->getController();
        $collection = [
            '@context' => 'http://iiif.io/api/presentation/3/context.json',
            'id' => $controller->url()->fromRoute(null, [], ['force_canonical' => true], true),
            'type' => 'Collection',
            'label' => [
                'none' => [$controller->translate('Items collection')],
            ],
        ];
        foreach ($itemIds as $itemId) {
            $item = $controller->api()->read('items', $itemId)->getContent();
            $collection['items'][] = [
                'id' => $controller->url()->fromRoute('iiif-presentation/item/manifest', ['item-id' => $item->id()], ['force_canonical' => true], true),
                'type' => 'Manifest',
                'label' => [
                    'none' => [$item->displayTitle()],
                ],
            ];
        }
        return $collection;
    }

    public function getManifestV2(int $itemId)
    {
        return [];
    }

    public function getManifestV3(int $itemId)
    {
        $controller = $this->getController();
        $item = $controller->api()->read('items', $itemId)->getContent();
        $manifest = [
            '@context' => 'http://iiif.io/api/presentation/3/context.json',
            'id' => $controller->url()->fromRoute(null, [], ['force_canonical' => true], true),
            'type' => 'Manifest',
            'behavior' => ['individuals', 'no-auto-advance'], // Default behaviors
            'viewingDirection' => 'left-to-right', // Default viewing direction
            'label' => [
                'none' => [$item->displayTitle()],
            ],
            'summary' => [
                'none' => [$item->displayDescription()],
            ],
            'provider' => [
                [
                    'id' => $controller->url()->fromRoute('top', [], ['force_canonical' => true]),
                    'type' => 'Agent',
                    'label' => ['none' => [$controller->settings()->get('installation_title')]],
                ],
            ],
            'seeAlso' => [
                [
                    'id' => $controller->url()->fromRoute('api/default', ['resource' => 'items', 'id' => $item->id()], ['force_canonical' => true, 'query' => ['pretty_print' => true]]),
                    'type' => 'Dataset',
                    'label' => ['none' => ['Item metadata']],
                    'format' => 'application/ld+json',
                    'profile' => 'https://www.w3.org/TR/json-ld/',
                ],
            ],
            'metadata' => $this->getMetadata($item),
        ];
        // Manifest thumbnail.
        $primaryMedia = $item->primaryMedia();
        if ($primaryMedia) {
            $manifest['thumbnail'] = [
                [
                    'id' => $primaryMedia->thumbnailUrl('medium'),
                    'type' => 'Image',
                ],
            ];
        }
        foreach ($item->media() as $media) {
            $mediaType = $media->mediaType();
            if ('image' !== strtok($mediaType, '/')) {
                continue;
            }
            [$width, $height] = getimagesize($media->originalUrl());
            $manifest['items'][] = [
                'id' => $controller->url()->fromRoute('iiif-presentation/item/canvas', ['media-id' => $media->id()], ['force_canonical' => true], true),
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
                'metadata' => $this->getMetadata($media),
                'items' => [
                    [
                        'id' => $controller->url()->fromRoute('iiif-presentation/item/annotation-page', ['media-id' => $media->id()], ['force_canonical' => true], true),
                        'type' => 'AnnotationPage',
                        'items' => [
                            [
                                'id' => $controller->url()->fromRoute('iiif-presentation/item/annotation', ['media-id' => $media->id()], ['force_canonical' => true], true),
                                'type' => 'Annotation',
                                'motivation' => 'painting',
                                'body' => [
                                    'id' => $media->originalUrl(),
                                    'type' => 'Image',
                                    'format' => $media->mediaType(),
                                    'width' => $width,
                                    'height' => $height,
                                ],
                                'target' => $controller->url()->fromRoute('iiif-presentation/item/canvas', ['media-id' => $media->id()], ['force_canonical' => true], true),
                            ],
                        ],
                    ],
                ],
            ];
        }
        return $manifest;
    }

    public function getMetadata(AbstractResourceEntityRepresentation $resource)
    {
        $allValues = [];
        foreach ($resource->values() as $term => $propertyValues) {
            $label = $propertyValues['alternate_label'] ?? $propertyValues['property']->label();
            foreach ($propertyValues['values'] as $valueRep) {
                $value = $valueRep->value();
                if (!is_string($value)) {
                    continue;
                }
                $lang = $valueRep->lang();
                if (!$lang) {
                    $lang = 'none';
                }
                $allValues[$label][$lang][] =  $value;
            }
        }
        $metadata = [];
        foreach ($allValues as $label => $values) {
            $metadata[] = [
                'label' => ['none' => [$label]],
                'value' => $values,
            ];
        }
        return $metadata;
    }
}
