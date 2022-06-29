<?php
namespace IiifPresentation\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;

class ItemController extends AbstractActionController
{
    public function viewCollectionAction()
    {
        $collectionUrl = $this->url()->fromRoute('iiif-presentation/item/collection', [], ['force_canonical' => true], true);
        return $this->redirect()->toRoute('iiif-viewer', [], ['query' => ['url' => $collectionUrl]]);
    }

    public function collectionAction()
    {
        $itemIds = explode(',', $this->params('item-ids'));
        $collection = [
            '@context' => 'http://iiif.io/api/presentation/3/context.json',
            'id' => $this->url()->fromRoute(null, [], ['force_canonical' => true], true),
            'type' => 'Collection',
            'label' => [
                'none' => [$this->translate('Items collection')],
            ],
        ];
        foreach ($itemIds as $itemId) {
            $item = $this->api()->read('items', $itemId)->getContent();
            $collection['items'][] = [
                'id' => $this->url()->fromRoute('iiif-presentation/item/manifest', ['item-id' => $item->id()], ['force_canonical' => true], true),
                'type' => 'Manifest',
                'label' => [
                    'none' => [$item->displayTitle()],
                ],
            ];
        }
        return $this->getResponse()->setContent(json_encode($collection, JSON_PRETTY_PRINT));
    }

    public function viewManifestAction()
    {
        $manifestUrl = $this->url()->fromRoute('iiif-presentation/item/manifest', [], ['force_canonical' => true], true);
        return $this->redirect()->toRoute('iiif-viewer', [], ['query' => ['url' => $manifestUrl]]);
    }

    public function manifestAction()
    {
        $item = $this->api()->read('items', $this->params('item-id'))->getContent();
        $manifest = [
            '@context' => 'http://iiif.io/api/presentation/3/context.json',
            'id' => $this->url()->fromRoute(null, [], ['force_canonical' => true], true),
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
                    'id' => $this->url()->fromRoute('top', [], ['force_canonical' => true]),
                    'type' => 'Agent',
                    'label' => ['none' => [$this->settings()->get('installation_title')]],
                ],
            ],
            'seeAlso' => [
                [
                    'id' => $this->url()->fromRoute('api/default', ['resource' => 'items', 'id' => $item->id()], ['force_canonical' => true, 'query' => ['pretty_print' => true]]),
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
                'id' => $this->url()->fromRoute('iiif-presentation/item/canvas', ['media-id' => $media->id()], ['force_canonical' => true], true),
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
                        'id' => $this->url()->fromRoute('iiif-presentation/item/annotation-page', ['media-id' => $media->id()], ['force_canonical' => true], true),
                        'type' => 'AnnotationPage',
                        'items' => [
                            [
                                'id' => $this->url()->fromRoute('iiif-presentation/item/annotation', ['media-id' => $media->id()], ['force_canonical' => true], true),
                                'type' => 'Annotation',
                                'motivation' => 'painting',
                                'body' => [
                                    'id' => $media->originalUrl(),
                                    'type' => 'Image',
                                    'format' => $media->mediaType(),
                                    'width' => $width,
                                    'height' => $height,
                                ],
                                'target' => $this->url()->fromRoute('iiif-presentation/item/canvas', ['media-id' => $media->id()], ['force_canonical' => true], true),
                            ],
                        ],
                    ],
                ],
            ];
        }
        return $this->getResponse()->setContent(json_encode($manifest, JSON_PRETTY_PRINT));
    }

    public function getMetadata($resource)
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

    public function getResponse()
    {
        $response = parent::getResponse();
        $response->getHeaders()->addHeaders([
            'Content-Type' => 'application/ld+json;profile="http://iiif.io/api/presentation/3/context.json"',
        ]);
        return $response;
    }
}
