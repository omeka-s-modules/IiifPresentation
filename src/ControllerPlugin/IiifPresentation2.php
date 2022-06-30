<?php
namespace IiifPresentation\ControllerPlugin;

use Omeka\Api\Representation\AbstractResourceEntityRepresentation;
use Laminas\Mvc\Controller\Plugin\AbstractPlugin;

class IiifPresentation2 extends AbstractPlugin
{
    /**
     * Get a IIIF Presentation collection of Omeka items.
     *
     * @see https://iiif.io/api/presentation/2.1/#collection
     */
    public function getItemsCollection(array $itemIds, string $label)
    {
    }

    /**
     * Get a IIIF Presentation collection for an Omeka item set.
     *
     * @see https://iiif.io/api/presentation/2.1/#collection
     */
    public function getItemSetCollection(int $itemSetId)
    {
    }

    /**
     * Get a IIIF Presentation manifest for an Omeka item.
     *
     * @see https://iiif.io/api/presentation/2.1/#manifest
     */
    public function getItemManifest(int $itemId)
    {
        $controller = $this->getController();
        $item = $controller->api()->read('items', $itemId)->getContent();
        $manifest = [
            '@context' => 'http://iiif.io/api/presentation/2/context.json',
            '@id' => $controller->url()->fromRoute(null, [], ['force_canonical' => true], true),
            '@type' => 'sc:Manifest',
            'viewingHint' => 'individuals', // Default viewing hint
            'viewingDirection' => 'left-to-right', // Default viewing direction
            'label' => $item->displayTitle(),
            'description' => $item->displayDescription(),
            'attribution' => $controller->settings()->get('installation_title'),
            'seeAlso' => [
                '@id' => $controller->url()->fromRoute('api/default', ['resource' => 'items', 'id' => $item->id()], ['force_canonical' => true, 'query' => ['pretty_print' => true]]),
                'label' => $controller->translate('Item metadata'),
                'format' => 'application/ld+json',
                'profile' => 'https://www.w3.org/TR/json-ld/',
            ],
            'metadata' => $this->getMetadata($item),
            'sequences' => [
                [
                    '@id' => $controller->url()->fromRoute('iiif-presentation-2/item/sequence', [], ['force_canonical' => true], true),
                    '@type' => 'sc:Sequence',
                ],
            ],
        ];
        foreach ($item->media() as $media) {
            $mediaType = $media->mediaType();
            if ('image' !== strtok($mediaType, '/')) {
                continue;
            }
            [$width, $height] = getimagesize($media->originalUrl());
            $manifest['sequences'][0]['canvases'][] = [
                '@id' => $controller->url()->fromRoute('iiif-presentation-2/item/canvas', ['media-id' => $media->id()], ['force_canonical' => true], true),
                '@type' => 'sc:Canvas',
                'label' => $media->displayTitle(),
                'width' => $width,
                'height' => $height,
                'thumbnail' => [
                    '@id' => $media->thumbnailUrl('medium'),
                    '@type' => 'dctypes:Image',
                ],
                'images' => [
                    [
                        '@type' => 'oa:Annotation',
                        'motivation' => 'sc:painting',
                        'resource' => [
                            '@id' => $media->originalUrl(),
                            '@type' => 'dctypes:Image',
                            'format' =>  $media->mediaType(),
                            'width' => $width,
                            'height' => $height,
                        ],
                        'on' => $controller->url()->fromRoute('iiif-presentation-2/item/canvas', ['media-id' => $media->id()], ['force_canonical' => true], true),
                    ],
                ],
            ];
        }
        return $manifest;
    }

    /**
     * Get the metadata of an Omeka resource, formatted for IIIF Presentation.
     *
     * @see https://iiif.io/api/presentation/2.1/#metadata
     * @see https://iiif.io/api/presentation/2.1/#language-of-property-values
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
            $thisMetadata = [
                'label' => $label,
                'value' => [],
            ];
            foreach ($valueData as $lang => $values) {
                foreach ($values as $value) {
                    if ('none' === $lang) {
                        $thisMetadata['value'][] = $value;
                    } else {
                        $thisMetadata['value'][] = ['@language' => $lang, '@value' => $value];
                    }
                }
            }
            $metadata[] = $thisMetadata;
        }
        return $metadata;
    }

    /**
     * Get a IIIF Presentation API response.
     *
     * @see https://iiif.io/api/presentation/2.1/#responses
     */
    public function getResponse(array $content)
    {
        $controller = $this->getController();
        $response = $controller->getResponse();
        $response->getHeaders()->addHeaders([
            'Content-Type' => 'application/ld+json',
            'Access-Control-Allow-Origin' => '*',
        ]);
        $response->setContent(json_encode($content, JSON_PRETTY_PRINT));
        return $response;
    }
}
