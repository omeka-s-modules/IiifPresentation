<?php
namespace IiifPresentation;

use Laminas\Router\Http;

return [
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
    'controllers' => [
        'invokables' => [
            'IiifPresentation\Controller\v3\Index' => Controller\v3\IndexController::class,
            'IiifPresentation\Controller\v3\Item' => Controller\v3\ItemController::class,
        ],
    ],
    'controller_plugins' => [
        'invokables' => [
            'iiifPresentation3' => ControllerPlugin\IiifPresentation3::class,
        ],
    ],
    'router' => [
        'routes' => [
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
