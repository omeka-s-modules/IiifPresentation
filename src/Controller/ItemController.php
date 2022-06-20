<?php
namespace IiifPresentation\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;

class ItemController extends AbstractActionController
{
    public function renderAction()
    {
        $item = $this->api()->read('items', $this->params('item-id'))->getContent();
        $manifestUrl = $this->url()->fromRoute('iiif/items/manifest', [], ['force_canonical' => true], true);

        $view = new ViewModel;
        $view->setTerminal(true);
        $view->setVariable('manifestUrl', $manifestUrl);
        return $view;
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
                'id' => $this->url()->fromRoute('iiif/items/canvas', ['media-id' => $media->id()], ['force_canonical' => true], true),
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
                        'id' => $this->url()->fromRoute('iiif/items/annotation-page', ['media-id' => $media->id()], ['force_canonical' => true], true),
                        'type' => 'AnnotationPage',
                        'items' => [
                            [
                                'id' => $this->url()->fromRoute('iiif/items/annotation', ['media-id' => $media->id()], ['force_canonical' => true], true),
                                'type' => 'Annotation',
                                'motivation' => 'painting',
                                'body' => [
                                    'id' => $media->originalUrl(),
                                    'type' => 'Image',
                                    'format' => $media->mediaType(),
                                    'width' => $width,
                                    'height' => $height,
                                ],
                                'target' => $this->url()->fromRoute('iiif/items/canvas', ['media-id' => $media->id()], ['force_canonical' => true], true),
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
