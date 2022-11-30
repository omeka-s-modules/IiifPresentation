<?php
namespace IiifPresentation\v3\ControllerPlugin;

use IiifPresentation\v3\CanvasType\Manager as CanvasTypeManager;
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
        // Allow modules to modify the collection.
        $args = $this->eventManager->prepareArgs([
            'collection' => $collection,
            'item_ids' => $itemIds,
        ]);
        $event = new Event('iiif_presentation.3.items.collection', $controller, $args);
        $this->eventManager->triggerEvent($event);
        return $args['collection'];
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
        // Manifest homepages (this item is assigned to these sites).
        foreach ($item->sites() as $site) {
            $manifest['homepage'][] = [
                'id' => $controller->url()->fromRoute('site/resource-id', ['site-slug' => $site->slug(), 'controller' => 'item', 'action' => 'show', 'id' => $item->id()], ['force_canonical' => true]),
                'type' => 'Text',
                'label' => [
                    'none' => [sprintf('Item in site: %s', $site->title())],
                ],
                'format' => 'text/html',
            ];
        }
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
                'iiif_presentation.3.media.canvas',
                [
                    'canvas' => $canvas,
                    'canvas_type' => $canvasType,
                    'media_id' => $media->id(),
                ]
            );
            // Set the canvas to the manifest.
            $manifest['items'][] = $params['canvas'];
        }
        // Allow modules to modify the manifest.
        $args = $this->eventManager->prepareArgs([
            'manifest' => $manifest,
            'item' => $item,
        ]);
        $event = new Event('iiif_presentation.3.item.manifest', $controller, $args);
        $this->eventManager->triggerEvent($event);
        return $args['manifest'];
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
                $allValues[$label][$lang][] = $value;
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
