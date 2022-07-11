<?php
namespace IiifPresentation;

use Laminas\Router\Http;

return [
    'iiif_presentation_v2_canvas_types' => [
        'invokables' => [
            'file' => v2\CanvasType\File::class,
            'iiif' => v2\CanvasType\IiifImage::class,
        ],
    ],
    'iiif_presentation_v3_canvas_types' => [
        'invokables' => [
            'iiif' => v3\CanvasType\IiifImage::class,
        ],
        'factories' => [
            'file' => v3\Service\CanvasType\FileFactory::class,
        ],
    ],
    'iiif_presentation_v3_file_canvas_types' => [
        'invokables' => [
            'image' => v3\FileCanvasType\Image::class,
            'video' => v3\FileCanvasType\Video::class,
            'audio' => v3\FileCanvasType\Audio::class,
        ],
        'aliases' => [
            'application/ogg' => 'video',
            'mp3' => 'audio',
        ],
    ],
    'translator' => [
        'translation_file_patterns' => [
            [
                'type' => 'gettext',
                'base_dir' => sprintf('%s/../language', __DIR__),
                'pattern' => '%s.mo',
            ],
        ],
    ],
    'view_manager' => [
        'template_path_stack' => [
            sprintf('%s/../view', __DIR__),
        ],
        'strategies' => [
            'ViewJsonStrategy',
        ],
    ],
    'service_manager' => [
        'factories' => [
            'IiifPresentation\v2\CanvasTypeManager' => v2\Service\CanvasType\ManagerFactory::class,
            'IiifPresentation\v3\CanvasTypeManager' => v3\Service\CanvasType\ManagerFactory::class,
            'IiifPresentation\v3\FileCanvasTypeManager' => v3\Service\FileCanvasType\ManagerFactory::class,
        ],
    ],
    'controllers' => [
        'invokables' => [
            'IiifPresentation\v2\Controller\Index' => v2\Controller\IndexController::class,
            'IiifPresentation\v2\Controller\Item' => v2\Controller\ItemController::class,
            'IiifPresentation\v2\Controller\ItemSet' => v2\Controller\ItemSetController::class,
            'IiifPresentation\v3\Controller\Index' => v3\Controller\IndexController::class,
            'IiifPresentation\v3\Controller\Item' => v3\Controller\ItemController::class,
            'IiifPresentation\v3\Controller\ItemSet' => v3\Controller\ItemSetController::class,
        ],
    ],
    'controller_plugins' => [
        'factories' => [
            'iiifPresentation2' => v2\Service\ControllerPlugin\IiifPresentationFactory::class,
            'iiifPresentation3' => v3\Service\ControllerPlugin\IiifPresentationFactory::class,
        ],
    ],
    'router' => [
        'routes' => [
            'iiif-presentation-2' => [
                'type' => Http\Literal::class,
                'options' => [
                    'route' => '/iiif-presentation/2',
                    'defaults' => [
                        '__NAMESPACE__' => 'IiifPresentation\v2\Controller',
                        'controller' => 'index',
                        'action' => 'index',
                    ],
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'item-set' => [
                        'type' => Http\Literal::class,
                        'options' => [
                            'route' => '/item-set',
                            'defaults' => [
                                'controller' => 'item-set',
                                'action' => 'index',
                            ],
                        ],
                        'may_terminate' => true,
                        'child_routes' => [
                            'view-collection' => [
                                'type' => Http\Segment::class,
                                'options' => [
                                    'route' => '/:item-set-id',
                                    'defaults' => [
                                        'action' => 'view-collection',
                                    ],
                                    'constraints' => [
                                        'item-set-id' => '\d+',
                                    ],
                                ],
                            ],
                            'collection' => [
                                'type' => Http\Segment::class,
                                'options' => [
                                    'route' => '/:item-set-id/collection',
                                    'defaults' => [
                                        'action' => 'collection',
                                    ],
                                    'constraints' => [
                                        'item-set-id' => '\d+',
                                    ],
                                ],
                            ],
                            'view-collections' => [
                                'type' => Http\Segment::class,
                                'options' => [
                                    'route' => '/:item-set-ids',
                                    'defaults' => [
                                        'action' => 'view-collections',
                                    ],
                                    'constraints' => [
                                        'item-set-ids' => '(\d+,)+(\d+)',
                                    ],
                                ],
                            ],
                            'collections' => [
                                'type' => Http\Segment::class,
                                'options' => [
                                    'route' => '/:item-set-ids/collection',
                                    'defaults' => [
                                        'action' => 'collections',
                                    ],
                                    'constraints' => [
                                        'item-set-ids' => '(\d+,)+(\d+)',
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'item' => [
                        'type' => Http\Literal::class,
                        'options' => [
                            'route' => '/item',
                            'defaults' => [
                                'controller' => 'item',
                                'action' => 'index',
                            ],
                        ],
                        'may_terminate' => true,
                        'child_routes' => [
                            'view-collection' => [
                                'type' => Http\Segment::class,
                                'options' => [
                                    'route' => '/:item-ids',
                                    'defaults' => [
                                        'action' => 'view-collection',
                                    ],
                                    'constraints' => [
                                        'item-ids' => '[\d+,]+',
                                    ],
                                ],
                            ],
                            'collection' => [
                                'type' => Http\Segment::class,
                                'options' => [
                                    'route' => '/:item-ids/collection',
                                    'defaults' => [
                                        'action' => 'collection',
                                    ],
                                    'constraints' => [
                                        'item-ids' => '[\d+,]+',
                                    ],
                                ],
                            ],
                            'view-manifest' => [
                                'type' => Http\Segment::class,
                                'options' => [
                                    'route' => '/:item-id',
                                    'defaults' => [
                                        'action' => 'view-manifest',
                                    ],
                                    'constraints' => [
                                        'item-id' => '\d+',
                                    ],
                                ],
                            ],
                            'manifest' => [
                                'type' => Http\Segment::class,
                                'options' => [
                                    'route' => '/:item-id/manifest',
                                    'defaults' => [
                                        'action' => 'manifest',
                                    ],
                                    'constraints' => [
                                        'item-id' => '\d+',
                                    ],
                                ],
                            ],
                            'sequence' => [
                                'type' => Http\Segment::class,
                                'options' => [
                                    'route' => '/:item-id/sequence',
                                    'defaults' => [
                                        'action' => 'sequence',
                                    ],
                                    'constraints' => [
                                        'item-id' => '\d+',
                                    ],
                                ],
                            ],
                            'canvas' => [
                                'type' => Http\Segment::class,
                                'options' => [
                                    'route' => '/:item-id/canvas/:media-id',
                                    'defaults' => [
                                        'action' => 'canvas',
                                    ],
                                    'constraints' => [
                                        'item-id' => '\d+',
                                        'media-id' => '\d+',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'iiif-presentation-3' => [
                'type' => Http\Literal::class,
                'options' => [
                    'route' => '/iiif-presentation/3',
                    'defaults' => [
                        '__NAMESPACE__' => 'IiifPresentation\v3\Controller',
                        'controller' => 'index',
                        'action' => 'index',
                    ],
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'item-set' => [
                        'type' => Http\Literal::class,
                        'options' => [
                            'route' => '/item-set',
                            'defaults' => [
                                'controller' => 'item-set',
                                'action' => 'index',
                            ],
                        ],
                        'may_terminate' => true,
                        'child_routes' => [
                            'view-collection' => [
                                'type' => Http\Segment::class,
                                'options' => [
                                    'route' => '/:item-set-id',
                                    'defaults' => [
                                        'action' => 'view-collection',
                                    ],
                                    'constraints' => [
                                        'item-set-id' => '\d+',
                                    ],
                                ],
                            ],
                            'collection' => [
                                'type' => Http\Segment::class,
                                'options' => [
                                    'route' => '/:item-set-id/collection',
                                    'defaults' => [
                                        'action' => 'collection',
                                    ],
                                    'constraints' => [
                                        'item-set-id' => '\d+',
                                    ],
                                ],
                            ],
                            'view-collections' => [
                                'type' => Http\Segment::class,
                                'options' => [
                                    'route' => '/:item-set-ids',
                                    'defaults' => [
                                        'action' => 'view-collections',
                                    ],
                                    'constraints' => [
                                        'item-set-ids' => '(\d+,)+(\d+)',
                                    ],
                                ],
                            ],
                            'collections' => [
                                'type' => Http\Segment::class,
                                'options' => [
                                    'route' => '/:item-set-ids/collection',
                                    'defaults' => [
                                        'action' => 'collections',
                                    ],
                                    'constraints' => [
                                        'item-set-ids' => '(\d+,)+(\d+)',
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'item' => [
                        'type' => Http\Literal::class,
                        'options' => [
                            'route' => '/item',
                            'defaults' => [
                                'controller' => 'item',
                                'action' => 'index',
                            ],
                        ],
                        'may_terminate' => true,
                        'child_routes' => [
                            'view-collection' => [
                                'type' => Http\Segment::class,
                                'options' => [
                                    'route' => '/:item-ids',
                                    'defaults' => [
                                        'action' => 'view-collection',
                                    ],
                                    'constraints' => [
                                        'item-ids' => '(\d+,)+(\d+)',
                                    ],
                                ],
                            ],
                            'collection' => [
                                'type' => Http\Segment::class,
                                'options' => [
                                    'route' => '/:item-ids/collection',
                                    'defaults' => [
                                        'action' => 'collection',
                                    ],
                                    'constraints' => [
                                        'item-ids' => '(\d+,)+(\d+)',
                                    ],
                                ],
                            ],
                            'view-manifest' => [
                                'type' => Http\Segment::class,
                                'options' => [
                                    'route' => '/:item-id',
                                    'defaults' => [
                                        'action' => 'view-manifest',
                                    ],
                                    'constraints' => [
                                        'item-id' => '\d+',
                                    ],
                                ],
                            ],
                            'manifest' => [
                                'type' => Http\Segment::class,
                                'options' => [
                                    'route' => '/:item-id/manifest',
                                    'defaults' => [
                                        'action' => 'manifest',
                                    ],
                                    'constraints' => [
                                        'item-id' => '\d+',
                                    ],
                                ],
                            ],
                            'canvas' => [
                                'type' => Http\Segment::class,
                                'options' => [
                                    'route' => '/:item-id/canvas/:media-id',
                                    'defaults' => [
                                        'action' => 'canvas',
                                    ],
                                    'constraints' => [
                                        'item-id' => '\d+',
                                        'media-id' => '\d+',
                                    ],
                                ],
                            ],
                            'annotation-page' => [
                                'type' => Http\Segment::class,
                                'options' => [
                                    'route' => '/:item-id/annotation-page/:media-id',
                                    'defaults' => [
                                        'action' => 'annotation-page',
                                    ],
                                    'constraints' => [
                                        'item-id' => '\d+',
                                        'media-id' => '\d+',
                                    ],
                                ],
                            ],
                            'annotation' => [
                                'type' => Http\Segment::class,
                                'options' => [
                                    'route' => '/:item-id/annotation/:media-id',
                                    'defaults' => [
                                        'controller' => 'item',
                                        'action' => 'annotation',
                                    ],
                                    'constraints' => [
                                        'item-id' => '\d+',
                                        'media-id' => '\d+',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
];
