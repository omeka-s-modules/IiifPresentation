<?php
namespace IiifPresentation;

use Laminas\Router\Http;

return [
    'iiif_presentation_canvas_types_v3' => [
        'invokables' => [
            'file' => CanvasType\v3\File::class,
            'iiif' => CanvasType\v3\IiifImage::class,
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
            'IiifPresentation\CanvasType\v3\Manager' => Service\CanvasType\v3\ManagerFactory::class,
        ],
    ],
    'controllers' => [
        'invokables' => [
            'IiifPresentation\Controller\v2\Index' => Controller\v2\IndexController::class,
            'IiifPresentation\Controller\v2\Item' => Controller\v2\ItemController::class,
            'IiifPresentation\Controller\v2\ItemSet' => Controller\v2\ItemSetController::class,
            'IiifPresentation\Controller\v3\Index' => Controller\v3\IndexController::class,
            'IiifPresentation\Controller\v3\Item' => Controller\v3\ItemController::class,
            'IiifPresentation\Controller\v3\ItemSet' => Controller\v3\ItemSetController::class,
        ],
    ],
    'controller_plugins' => [
        'factories' => [
            'iiifPresentation2' => Service\ControllerPlugin\IiifPresentation2Factory::class,
            'iiifPresentation3' => Service\ControllerPlugin\IiifPresentation3Factory::class,
        ],
    ],
    'router' => [
        'routes' => [
            'iiif-presentation-2' => [
                'type' => Http\Literal::class,
                'options' => [
                    'route' => '/iiif-presentation/2',
                    'defaults' => [
                        '__NAMESPACE__' => 'IiifPresentation\Controller\v2',
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
                        '__NAMESPACE__' => 'IiifPresentation\Controller\v3',
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
