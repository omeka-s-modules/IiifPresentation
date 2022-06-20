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
            'IiifPresentation\Controller\Index' => Controller\IndexController::class,
            'IiifPresentation\Controller\Item' => Controller\ItemController::class,
        ],
    ],
    'router' => [
        'routes' => [
            'iiif' => [
                'type' =>  Http\Literal::class,
                'options' => [
                    'route' => '/iiif',
                    'defaults' => [
                        '__NAMESPACE__' => 'IiifPresentation\Controller',
                        'controller' => 'index',
                        'action' => 'index',
                    ],
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'items' => [
                        'type' => Http\Literal::class,
                        'options' => [
                            'route' => '/items',
                            'defaults' => [
                                'controller' => 'item',
                                'action' => 'index',
                            ],
                        ],
                        'may_terminate' => true,
                        'child_routes' => [
                            'render' => [
                                'type' => Http\Segment::class,
                                'options' => [
                                    'route' => '/:item-id',
                                    'defaults' => [
                                        'action' => 'render',
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
