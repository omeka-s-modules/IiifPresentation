<?php
namespace IiifPresentation\v2\ControllerPlugin;

use IiifPresentation\v2\CanvasType\Manager as CanvasTypeManager;
use Omeka\Api\Representation\AbstractResourceEntityRepresentation;
use Laminas\EventManager\Event;
use Laminas\EventManager\EventManagerInterface;
use Laminas\Mvc\Controller\Plugin\AbstractPlugin;

class IiifPresentation extends AbstractPlugin
{
    protected $canvasTypeManager;
    protected $eventManager;

    public function __construct(CanvasTypeManager $canvasTypeManager, EventManagerInterface $eventManager)
    {
        $this->canvasTypeManager = $canvasTypeManager;
        $this->eventManager = $eventManager;
    }

    /**
     * Get a IIIF Presentation collection of Omeka items.
     *
     * @see https://iiif.io/api/presentation/2.1/#collection
     */
    public function getItemsCollection(array $itemIds, string $label)
    {
        $controller = $this->getController();
        $collection = [
            '@context' => 'http://iiif.io/api/presentation/2/context.json',
            '@id' => $controller->url()->fromRoute(null, [], ['force_canonical' => true], true),
            '@type' => 'sc:Collection',
            'label' => $label,
        ];
        foreach ($itemIds as $itemId) {
            $item = $controller->api()->read('items', $itemId)->getContent();
            $collection['manifests'][] = [
                '@id' => $controller->url()->fromRoute('iiif-presentation-2/item/manifest', ['item-id' => $item->id()], ['force_canonical' => true], true),
                '@type' => 'sc:Manifest',
                'label' => $item->displayTitle(),
            ];
        }
        return $collection;
    }

    /**
     * Get a IIIF Presentation collection of Omeka item sets.
     *
     * @see https://iiif.io/api/presentation/2.1/#collection
     */
    public function getItemSetsCollection(array $itemSetIds)
    {
        $controller = $this->getController();
        $collection = [
            '@context' => 'http://iiif.io/api/presentation/2/context.json',
            '@id' => $controller->url()->fromRoute(null, [], ['force_canonical' => true], true),
            '@type' => 'sc:Collection',
            'label' => $controller->translate('Item Sets Collection'),
        ];
        foreach ($itemSetIds as $itemSetId) {
            $itemSet = $controller->api()->read('item_sets', $itemSetId)->getContent();
            $collection['collections'][] = [
                '@id' => $controller->url()->fromRoute('iiif-presentation-3/item-set/collection', ['item-set-id' => $itemSet->id()], ['force_canonical' => true], true),
                '@type' => 'sc:Collection',
                'label' => $itemSet->displayTitle(),
            ];
        }
        return $collection;
    }

    /**
     * Get a IIIF Presentation collection for an Omeka item set.
     *
     * @see https://iiif.io/api/presentation/2.1/#collection
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
            $renderer = $media->renderer();
            if (!$this->canvasTypeManager->has($renderer)) {
                // There is no canvas type for this renderer.
                continue;
            }
            $canvasType = $this->canvasTypeManager->get($renderer);
            $canvas = $canvasType->getCanvas($media, $controller);
            if (!$canvas) {
                // A canvas could not be created.
                continue;
            }
            // Allow modules to modify the canvas.
            $params = $this->triggerEvent(
                'iiif_presentation.2.media.canvas',
                [
                    'canvas' => $canvas,
                    'canvas_type' => $canvasType,
                    'media_id' => $media->id(),
                ]
            );
            // Set the canvas to the manifest.
            $manifest['sequences'][0]['canvases'][] = $params['canvas'];
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
                $allValues[$label][$lang][] = $value;
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
     * Trigger an event.
     */
    public function triggerEvent(string $name, array $params)
    {
        $params = $this->eventManager->prepareArgs($params);
        $event = new Event($name, $this->getController(), $params);
        $this->eventManager->triggerEvent($event);
        return $params;
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
