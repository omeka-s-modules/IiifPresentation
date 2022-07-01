<?php
namespace IiifPresentation\ControllerPlugin;

use Omeka\Api\Representation\AbstractResourceEntityRepresentation;
use Laminas\Mvc\Controller\Plugin\AbstractPlugin;

class IiifPresentation3 extends AbstractPlugin
{
    /**
     * Get a IIIF Presentation collection of Omeka items.
     *
     * @see https://iiif.io/api/presentation/3.0/#51-collection
     */
    public function getItemsCollection(array $itemIds, string $label)
    {
        $controller = $this->getController();
        $collection = [
            '@context' => 'http://iiif.io/api/presentation/3/context.json',
            'id' => $controller->url()->fromRoute(null, [], ['force_canonical' => true], true),
            'type' => 'Collection',
            'label' => [
                'none' => [$label],
            ],
        ];
        foreach ($itemIds as $itemId) {
            $item = $controller->api()->read('items', $itemId)->getContent();
            $collection['items'][] = [
                'id' => $controller->url()->fromRoute('iiif-presentation-3/item/manifest', ['item-id' => $item->id()], ['force_canonical' => true], true),
                'type' => 'Manifest',
                'label' => [
                    'none' => [$item->displayTitle()],
                ],
            ];
        }
        return $collection;
    }

     /**
     * Get a IIIF Presentation collection of Omeka item sets.
     *
     * @see https://iiif.io/api/presentation/3.0/#51-collection
     */
    public function getItemSetsCollection(array $itemSetIds)
    {
        $controller = $this->getController();
        $collection = [
            '@context' => 'http://iiif.io/api/presentation/3/context.json',
            'id' => $controller->url()->fromRoute(null, [], ['force_canonical' => true], true),
            'type' => 'Collection',
            'label' => [
                'none' => [$controller->translate('Item Sets Collection')],
            ],
        ];
        foreach ($itemSetIds as $itemSetId) {
            $itemSet = $controller->api()->read('item_sets', $itemSetId)->getContent();
            $collection['items'][] = [
                'id' => $controller->url()->fromRoute('iiif-presentation-3/item-set/collection', ['item-set-id' => $itemSet->id()], ['force_canonical' => true], true),
                'type' => 'Collection',
                'label' => [
                    'none' => [$itemSet->displayTitle()],
                ],
            ];
        }
        return $collection;
    }

    /**
     * Get a IIIF Presentation collection for an Omeka item set.
     *
     * @see https://iiif.io/api/presentation/3.0/#51-collection
     */
    public function getItemSetCollection(int $itemSetId)
    {
        $controller = $this->getController();
        $itemSet = $controller->api()->read('item_sets', $itemSetId)->getContent();
        $itemIds = $controller->api()->search('items', ['item_set_id' => $itemSetId], ['returnScalar' => 'id'])->getContent();
        return $this->getItemsCollection($itemIds, $itemSet->displayTitle());
    }

    /**
     * Get a IIIF Presentation manifest for an Omeka item.
     *
     * @see https://iiif.io/api/presentation/3.0/#52-manifest
     */
    public function getItemManifest(int $itemId)
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
                    'label' => ['none' => [$controller->translate('Item metadata')]],
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
                'metadata' => $this->getMetadata($media),
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
        return $manifest;
    }

    /**
     * Get the metadata of an Omeka resource, formatted for IIIF Presentation.
     *
     * @see https://iiif.io/api/presentation/3.0/#metadata
     */
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
        foreach ($allValues as $label => $valueData) {
            $metadata[] = [
                'label' => ['none' => [$label]],
                'value' => $valueData,
            ];
        }
        return $metadata;
    }

    /**
     * Get a IIIF Presentation API response.
     *
     * @see https://iiif.io/api/presentation/3.0/#63-responses
     */
    public function getResponse(array $content)
    {
        $controller = $this->getController();
        $response = $controller->getResponse();
        $response->getHeaders()->addHeaders([
            'Content-Type' => 'application/ld+json;profile="http://iiif.io/api/presentation/3/context.json"',
            'Access-Control-Allow-Origin' => '*',
        ]);
        $response->setContent(json_encode($content, JSON_PRETTY_PRINT));
        return $response;
    }
}
