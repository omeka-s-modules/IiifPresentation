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
            'label' => $item->displayTitle(),
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
