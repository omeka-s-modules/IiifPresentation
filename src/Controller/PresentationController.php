<?php
namespace IiifPresentation\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\JsonModel;

class PresentationController extends AbstractActionController
{
    public function manifestAction()
    {
        $item = $this->api()->read('items', $this->params('item-id'))->getContent();
        $manifest = [
            '@context' => 'http://iiif.io/api/presentation/3/context.json',
            'id' => $this->url()->fromRoute(null, [], ['force_canonical' => true], true),
            'type' => 'Manifest',
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
        // Manifest metadata.
        $metadata = [];
        foreach ($item->values() as $term => $propertyValues) {
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
                $metadata[$label][$lang][] =  $value;
            }
        }
        foreach ($metadata as $label => $values) {
            $manifest['metadata'][] = [
                'label' => ['none' => [$label]],
                'value' => $values,
            ];
        }

        foreach ($item->media() as $media) {
            $mediaType = $media->mediaType();
            if ('image' !== strtok($mediaType, '/')) {
                continue;
            }
            [$width, $height] = getimagesize($media->originalUrl());
            $manifest['items'][] = [
                'id' => $this->url()->fromRoute('iiif-presentation/canvas', ['media-id' => $media->id()], ['force_canonical' => true], true),
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
                'items' => [
                    [
                        'id' => $this->url()->fromRoute('iiif-presentation/annotation-page', ['media-id' => $media->id()], ['force_canonical' => true], true),
                        'type' => 'AnnotationPage',
                        'items' => [
                            [
                                'id' => $this->url()->fromRoute('iiif-presentation/annotation', ['media-id' => $media->id()], ['force_canonical' => true], true),
                                'type' => 'Annotation',
                                'motivation' => 'painting',
                                'body' => [
                                    'id' => $media->originalUrl(),
                                    'type' => 'Image',
                                    'format' => $media->mediaType(),
                                    'width' => $width,
                                    'height' => $height,
                                ],
                                'target' => $this->url()->fromRoute('iiif-presentation/canvas', ['media-id' => $media->id()], ['force_canonical' => true], true),
                            ],
                        ],
                    ],
                ],
            ];
        }
        return $this->getResponse()->setContent(json_encode($manifest, JSON_PRETTY_PRINT));
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
